<?php

namespace App\Repositories\Frontend;

use App\Enums\PaymentMethod;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletPointsTrait;
use App\Models\ServiceRequest;
use Exception;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Category;

class AccountRepository extends BaseRepository
{
    use WalletPointsTrait;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->service_request = new ServiceRequest();
        return User::class;
    }

    public function updateProfile($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->findOrFail(auth()->user()->id);
            $user->update([
                'name' => $request['name'],
                'email' => $request['email'],
                'phone' => (string) $request['phone'],
                'code' => $request['code'],
                'status' => true,
            ]);

            if (isset($request['image'])) {
                $user->clearMediaCollection('image');
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();
            return back()->with('message', 'Profile Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updatePassword($request)
    {
        DB::beginTransaction();
        try {
            $this->model->findOrFail(auth()->user()->id)
                ->update(['password' => Hash::make($request->new_password)]);

            DB::commit();
            return back()->with('message', 'Password Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function wallet($dataTable)
    {
        return $dataTable->render('frontend.account.wallet');
    }

    public function markAsRead($request)
    {
        $user = Auth::user();
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    }

    public function webMarkAsRead($request)
    {
        $user = Auth::user();
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return redirect()?->back();
    }

    public function walletTopUp($request)
    {
        $user_id = Helpers::getCurrentUserId();
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName === RoleEnum::CONSUMER) {
            $wallet = $this->getWallet($user_id);
            $payment = $this->createPayment($wallet, $request);
            if (isset($payment['is_redirect'])) {
                if ($payment['is_redirect']) {
                    return redirect()->away($payment['url']);
                }
            }
        }
        DB::beginTransaction();

        try {
            DB::commit();
            throw new Exception(__('static.wallet.permission_denied'), 403);
        } catch (Exception $e) {
            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCustomJobs($request)
    {

        $userId = auth()->id();

        $serviceRequests = $this->service_request->where('deleted_at' , null)->where('user_id', $userId)->get();
        $serviceCategories = Category::getDropdownOptions();

        return view('frontend.account.custom-job',['serviceRequests' => $serviceRequests ,  'serviceCategories' => $serviceCategories]);

    }

    public function createPayment($wallet, $request)
    {
        try {

            if ($wallet) {
                $module = Module::find($request->payment_method);
                $request->merge(['type' => PaymentMethod::WALLET]);
                $request->merge(['request_type' => 'web']);
                if (! is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\' . $moduleName . '\\Payment\\' . $moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        $wallet['total'] = $request->amount;

                        return $payment::getIntent($wallet, $request);
                    } else {
                        throw new Exception(__('static.wallet.payment_module_not_found'), 400);
                    }
                }
            }

            throw new Exception(__('static.wallet.invalid_payment_method'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
