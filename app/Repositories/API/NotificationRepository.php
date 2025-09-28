<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class NotificationRepository extends BaseRepository
{
    protected $notification;

    public function model()
    {
        return User::class;
    }

    public function markAsRead($request)
    {
        DB::beginTransaction();
        try {

            $user_id = Helpers::getCurrentUserId();
            $user = $this->model->findOrFail($user_id);
            $user->unreadNotifications->markAsRead();
            DB::commit();

            return $user->notifications()->paginate($request->paginate);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function markAsReadNotification($request)
    {
        DB::beginTransaction();
        try {

            $user_id = Helpers::getCurrentUserId();
            $user = $this->model->findOrFail($user_id);
            $notification = $user->unreadNotifications()->find($request->id);
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => __('validation.notification_not_found')
                ], 404);
            }

            $notification->markAsRead();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('validation.notification_mark_as_read')
            ]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        auth()->user()->notify(new \App\Notifications\StoreMessageNotification($request));

        return response()->json([
            'message' => __('static.notification.store'),
            'success' => true,
        ]);
    }

    public function destroy($id)
    {
        try {

            $user_id = Helpers::getCurrentUserId();
            $user = $this->model->findOrFail($user_id)->first();

            return $user->notifications()->where('id', $id)->first()->destroy($id);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function clearNotifications()
    {
        try {
            $user_id = Helpers::getCurrentUserId();
            $notifications = $this->model->findOrFail($user_id)->notifications();

            if ($notifications) {
                $notifications->delete();

                return response()->json([
                    'message' => __('static.notification.destroy'),
                    'success' => true,
                ]);
            } else {
                throw new ExceptionHandler(__('static.notification.notification_not_found'), 404);
            }

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
