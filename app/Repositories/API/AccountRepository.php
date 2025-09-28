<?php

namespace App\Repositories\API;

use Exception;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\ExceptionHandler as ExceptionsExceptionHandler;
use App\Helpers\Helpers;
use App\Models\User;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserSelfResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\Response;

class AccountRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionsExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function self()
    {
        try {
            $user_id = Helpers::getCurrentUserId();
         
            // $settings = Helpers::getSettings();
            
            $user = $this->model->findOrFail($user_id);
            // $reminderMessage = $settings['subscription_plan']['reminder_message'] ?? '';

            // if ($user->activeSubscription && $user->role->name == RoleEnum::PROVIDER) {
            //     $endDate = Carbon::parse($user->activeSubscription->end_date);
            //     $daysBeforeEnd = now()->diffInDays($endDate, false);
            //     if ($daysBeforeEnd>=0 && $daysBeforeEnd <= $settings['subscription_plan']['days_before_reminder']) {
            //         $user->subscription_reminder_note = str_replace('{days_before_reminder}', (int)$daysBeforeEnd, $reminderMessage);
            //     }
            // }
            
            // $user;

            return response()->json(['success' => true, 'user' => new UserSelfResource($user)]);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
   
    
    public function updateUserZone($request){
        DB::beginTransaction();
        try {
            if (Helpers::isUserLogin()) {
                $user = Helpers::getCurrentUser();
                
                $user->update([
                    'location_cordinates' => $request->location,
                ]);
    
                DB::commit();
                $user = $user->fresh();
                return [
                    'success' => true
                ];
            } else {
                return response()->json(['success' => false, 'message' => __('errors.user_not_found')]);
            }

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
    
    public function updateProfile($request)
    {
        DB::beginTransaction();
        try {
            $request['phone'] = (string) $request['phone'];
            $user = $this->model->findOrFail(Helpers::getCurrentUserId());

            if ($request->hasFile('profile_image')) {
                $user->clearMediaCollection('image');
                $profileImage = $request->file('profile_image');
                $user->addMedia($profileImage)->toMediaCollection('image');
            }

            $user->update($request->all());
            DB::commit();

            return [
                'user' => new UserProfileResource($user),
                'success' => true,
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updatePassword($request)
    {
        DB::beginTransaction();
        try {

            $user_id = Helpers::getCurrentUserId();
            $user = $this->model->findOrFail($user_id);

            $user->update(['password' => Hash::make($request->password)]);
            DB::commit();

            return response()->json([
                'message' => __('auth.password_has_been_changed'),
            ]);
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAccount()
    {
        DB::beginTransaction();
        try {
            if(config('app.demo')){
                throw new ExceptionHandler(__('static.disabled_action'), Response::HTTP_BAD_REQUEST);    
            }
            $user = $this->model->findOrFail(auth('sanctum')->user()->id);
            $user->forceDelete(auth('sanctum')->user()->id);
            DB::commit();

            return [
                'message' => __('auth.user_deleted'),
            ];
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
