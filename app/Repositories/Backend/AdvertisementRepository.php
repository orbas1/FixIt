<?php

namespace App\Repositories\Backend;

use App\Enums\AdvertisementStatusEnum;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use App\Models\ProviderWallet;
use DateTime;
use Exception;
use App\Models\Service;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;
use App\Enums\AdvertisementTypeEnum;
use Illuminate\Support\Facades\Request;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AdvertisementRepository extends BaseRepository
{
    public function model()
    {
        return Advertisement::class;
    }

    public function create($attributes = [])
    {
        $advertisementType = AdvertisementTypeEnum::ADVERTISEMENTTYPE;
        $advertisementScreen = AdvertisementTypeEnum::ADVERTISEMENTSCREEN;
        $advertisementBannerType = AdvertisementTypeEnum::ADVERTISEMENTBANNERTYPE;
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.advertisement.create' , ['services' => $this->getServices(),
        'advertisementType' => $advertisementType ,
        'advertisementScreen' => $advertisementScreen,
        'advertisementBannerType' => $advertisementBannerType]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $provider = Helpers::getCurrentRoleName() == RoleEnum::PROVIDER ? auth()->user()->id :  $request?->provider_id;
            if ($request->price) {
                $isDebited = $this->debitWallet($provider, $request?->price, WalletPointsDetail::WALLET_ADVERTISEMENT, $request);

                if ($isDebited !== true) {
                    return $isDebited;
                }
            }

            $locale = $request->locale ?? app()->getLocale();

            if($request->has('start_end_date')){
                [$start_date, $end_date] = explode(' to ', $request->start_end_date);
                $start_date = DateTime::createFromFormat('d-m-Y', $start_date)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $end_date)->format('Y-m-d');
            }

            if (Carbon::parse($start_date)->lte(Carbon::today())) {
                return redirect()->back()->with('error', 'Start date must be Date after today.');
            }

            $advertisement = $this->model?->create([
                'provider_id' => Helpers::getCurrentRoleName() == RoleEnum::PROVIDER ? auth()->user()->id :  $request?->provider_id,
                'type' => $request?->type,
                'screen' => $request?->screen,
                'status' => Helpers::getCurrentRoleName() == RoleEnum::PROVIDER  ? AdvertisementStatusEnum::PENDING : AdvertisementStatusEnum::APPROVED,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'zone' => $request?->zone,
                'video_link' => $request?->video_link,
                'banner_type' => $request?->banner_type,
                'price' => $request?->price
            ]);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $advertisement->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
            }

            if ($request->service_id) {
                $advertisement->services()->attach($request->service_id);
                $advertisement->services;
                if($request?->screen === 'category') {
                    Service::whereIn('id', (array) $request->service_id)->update(['is_advertised' => true]);
                }
            }

            $advertisement->setTranslation('video_link', $locale, $request['video_link']);
            $advertisement->save();
            DB::commit();

            return redirect()->route('backend.advertisement.index')->with('message', 'Advertisement Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {
            $advertisement = $this->model->findOrFail($id);

            $validTransitions = [
                AdvertisementStatusEnum::PENDING => [
                    'allowed' => [AdvertisementStatusEnum::APPROVED, AdvertisementStatusEnum::REJECTED],
                    'error' => 'Cannot move from Pending to this status.'
                ],
                'approved' => [
                    'allowed' => [AdvertisementStatusEnum::RUNNING, AdvertisementStatusEnum::PAUSED, AdvertisementStatusEnum::EXPIRED],
                    'error' => 'Once approved, the ad cannot be rejected or set to pending.'
                ],
                'rejected' => [
                    'allowed' => [],
                    'error' => 'A rejected advertisement cannot be updated further.'
                ],
                'running' => [
                    'allowed' => [AdvertisementStatusEnum::PAUSED, AdvertisementStatusEnum::EXPIRED],
                    'error' => 'A running ad can only be paused or expired.'
                ],
                'paused' => [
                    'allowed' => [AdvertisementStatusEnum::RUNNING, AdvertisementStatusEnum::EXPIRED],
                    'error' => 'A paused ad can only be resumed (running) or expired.'
                ],
                'expired' => [
                    'allowed' => [],
                    'error' => 'An expired advertisement cannot be changed.'
                ],
            ];

            $currentStatus = $advertisement->status;


            if (!in_array($status, $validTransitions[$currentStatus]['allowed'])) {
                return back()->with('error', $validTransitions[$currentStatus]['error']);
            }

            if ($currentStatus === AdvertisementStatusEnum::PENDING && $status === AdvertisementStatusEnum::APPROVED) {
                if ($advertisement->price > 0) {
                    $isDebited = $this->debitWallet(
                        $advertisement->provider_id,
                        $advertisement->price,
                        WalletPointsDetail::WALLET_ADVERTISEMENT,
                        request()
                    );

                    if ($isDebited !== true) {
                        return back()->with('error', 'Wallet deduction failed: ' . $isDebited);
                    }
                }
            }

            $advertisement->update(['status' => $status]);

            if (in_array($status, [AdvertisementStatusEnum::EXPIRED, AdvertisementStatusEnum::PAUSED])) {

                $advertisement?->services()?->update(['is_advertised' => 0]);
            } elseif (in_array($status, [AdvertisementStatusEnum::APPROVED, AdvertisementStatusEnum::RUNNING])) {
                $advertisement?->services()?->update(['is_advertised' => 1]);
            }

            return redirect()->route('backend.advertisement.index')->with('message', 'Status updated successfully.');

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $advertisement = $this->model->findOrFail($id);
        $advertisementType = AdvertisementTypeEnum::ADVERTISEMENTTYPE;
        $advertisementScreen = AdvertisementTypeEnum::ADVERTISEMENTSCREEN;
        $advertisementBannerType = AdvertisementTypeEnum::ADVERTISEMENTBANNERTYPE;

        return view('backend.advertisement.edit', [
            'advertisementType' => $advertisementType ,
            'advertisementScreen' => $advertisementScreen ,
            'advertisement' => $advertisement,
            'advertisementBannerType' => $advertisementBannerType,
            'services' => $this->getServices(),
        ]);
    }

    private function getServices()
    {
        $services = Service::whereNull('parent_id');
        if(Helpers::getCurrentRoleName() == RoleEnum::PROVIDER){
            $services->where('user_id', auth()->user()->id);
        }
        $serviceList = $services->get();

        return $serviceList;
    }


    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $advertisement = $this->model->findOrFail($id);
            if($request?->price > $advertisement?->price ) {
                $remaining_price = $request?->price - $advertisement?->price;

                $isDebited = $this->debitWallet($request?->provider_id, $remaining_price, WalletPointsDetail::WALLET_ADVERTISEMENT, $request);

                if ($isDebited !== true) {
                    return $isDebited;
                }
            } 
            if($request?->type == 'service' ) {
                $request->video_link = null;
                $advertisement->banner_type = null;
                $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingMedia as $media) {
                    $media->delete();
                }
            }


            if($request?->type == 'banner') {
                if($request?->banner_type === 'video') {
                    $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                        return $media->getCustomProperty('language') === $locale;
                    });
                    foreach ($existingMedia as $media) {
                        $media->delete();
                    }
                }

                if($request?->banner_type === 'image') {
                    $request->video_link = null;
                }
                $existingServices = $advertisement->services;

                foreach ($existingServices as $service) {
                    $service->delete();
                }
            }

            if($request->has('start_end_date')){
                [$start_date, $end_date] = explode(' to ', $request->start_end_date);
                $start_date = DateTime::createFromFormat('d-m-Y', $start_date)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $end_date)->format('Y-m-d');
            }

            $advertisement->update([
                'provider_id' => $request?->provider_id,
                'type' => $request?->type,
                'screen' => $request?->screen,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'zone' => $request?->zone,
                'video_link' => $request?->video_link,
                'banner_type' => $request?->banner_type,
                'price' => $request?->price
            ]);

            if ($request->hasFile('images')) {
                $newImages = $request->file('images');
                $newImages = is_array($newImages) ? $newImages : [$newImages];
                $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingMedia as $media) {
                    $media->delete();
                }

                foreach ($newImages as $image) {
                    $advertisement->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
            }

            if (isset($request->service_id)){
                $advertisement->services()->sync($request->service_id);
                $advertisement->services;
                if($request?->screen === 'category') {
                    Service::whereIn('id', (array) $request->service_id)->update(['is_advertised' => true]);
                }
            }

            DB::commit();
            return redirect()->route('backend.advertisement.index')->with('message', 'Advertisement Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $advertisement = $this->model->findOrFail($id);
            $advertisement->destroy($id);

            return redirect()->route('backend.advertisement.index')->with('message', 'Advertisement Deleted Successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function debitWallet($consumer_id, $balance, $detail, $request)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {

            if ($wallet->balance >= $balance) {
                $wallet->decrement('balance', $balance);
                $this->debitTransaction($wallet, $balance, $detail);
                $wallet->setRelation('transactions', $wallet->transactions()
                    ->paginate($request->paginate ?? $wallet->transactions()->count()));
                return true;
            }

            return redirect()->back()->with('error', 'Balance is not sufficient for this withdrawal.');
        }
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::PROVIDER) {
            return ProviderWallet::firstOrCreate(['provider_id' => $consumer_id]);
        }

        return back()->with('error', 'user must be Provider');
    }

    public function debitTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $order_id = null)
    {

        $transaction =  $model->transactions()->create([
            'amount' => $amount,
            'order_id' => $order_id,
            'detail' => $detail,
            'type' => $type,
            'from' => auth()->user()->id,
        ]);

    }


}
