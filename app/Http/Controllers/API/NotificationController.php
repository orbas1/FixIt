<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Repositories\API\NotificationRepository;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $user = $this->repository->findOrFail(Helpers::getCurrentUserId());

        $items = $user->notifications()->latest('created_at')->paginate($request->paginate ?? $user->count());
      
        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($items)
        ]);
    }

    public function store(Request $request)
    {
        return $this->repository->store($request);
    }

    public function markAsRead(Request $request)
    {
        return $this->repository->markAsRead($request);
    }

    public function markAsReadNotification(Request $request)
    {
        return $this->repository->markAsReadNotification($request);
    }

    public function destroy(Request $request)
    {
        return $this->repository->destroy($request->id);
    }

    public function clearNotifications(Request $request)
    {
        return $this->repository->clearNotifications();
    }

    public function dummyNotification(Request $request)
    {
        $user = Helpers::getCurrentUser();
        $token = $request->topic;
        $image = asset('admin/images/Logo-Light.png');
        if ($token) {
            $notification = [
                'message' => [
                    'topic' => $token,
                    'notification' => [
                        'title' => 'dummy notification for test',
                        'body' =>  'dummy notification for test',
                        'image' => $image,
                    ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => $request->type,
                        $request->key => $request->value,
                    ],
                ],
            ];
            Helpers::pushNotification($notification);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }
}
