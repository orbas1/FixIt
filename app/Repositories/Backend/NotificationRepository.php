<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\PushNotification;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class NotificationRepository extends BaseRepository
{
    protected $service;

    protected $user;

    public function model()
    {
        $this->service = new Service();
        $this->user = new User();
        $this->zone = new Zone();
        return PushNotification::class;
    }

    public function index($dataTable)
    {
        return $dataTable->render('backend.push-notification.index');
    }

    public function create($request)
    {
        $services = $this->service->get();
        $zones = $this->zone->pluck('name','id');

        return view('backend.push-notification.create', compact('services','zones'));
    }

    public function listNotification()
    {
        return view('backend.push-notification.list');
    }

    public function sendNotification($request)
    {

        $notification = $this->model->create([
            'send_to' => $request->notification_send_to,
            'service_id' => $request->service_id ?? null,
            'title' => $request->title,
            'message' => $request->description,
            'url' => $request->url,
            'notification_type' => $request->send_to,
            'user_id' => auth()->user()->id,
        ]);

        if ($request->file('image')) {
            $media = $notification->addMediaFromRequest('image')->toMediaCollection('notification_image');
            $fullImageUrl = $media->getFullUrl();
            $notification->image_url = $fullImageUrl;
            $notification->save();
        }



        if ($request->notification_send_to === 'user') {

            $users = User::role(RoleEnum::CONSUMER)->whereNotNull('fcm_token')?->get();

            foreach ($users as $user) {
                if($request->zone){
                    // $locationCordinates = json_decode($user?->location_cordinates);

                    $userZones = Helpers::getZoneByPoint($locationCordinates?->lat ?? null ,$locationCordinates?->lng ?? null)->pluck('id')->toArray();

                    if(in_array($request?->zone,$userZones)){
                        $notification = [
                            'message' => [
                                'data' => [
                                    'url' => $request->url ?? null,
                                    'service_id' => $request->service_id ?? null,
                                ],
                                'notification' => [
                                    'title' => $request->title,
                                    'body' => $request->discription,
                                    'image' => $notification->image_url ?? null,
                                ],
                                'token' => $user->token,
                            ],
                        ];
                    }
                }
                else{
                    $notification = [
                        'message' => [
                            'data' => [
                                'url' => $request->url ?? null,
                                'service_id' => $request->service_id ?? null,
                            ],
                            'notification' => [
                                'title' => $request->title,
                                'body' => $request->discription,
                                'image' => $notification->image_url ?? null,
                            ],
                            'token' => $user->token,
                        ],
                    ];

                }
                Helpers::pushNotification($notification);
            }

        } else {
            $users = $this->user->role(RoleEnum::PROVIDER)->whereNotNull('fcm_token')?->get();
            foreach ($users as $user) {
                if($request->zone){
                    $locationCordinates = is_string($user->location_cordinates) ? json_decode($user->location_cordinates) : $user->location_cordinates;
                    $userZones = Helpers::getZoneByPoint($locationCordinates->lat ?? null ,$locationCordinates->lng ?? null)->pluck('id')->toArray();
                    if(in_array($request?->zone,$userZones)){
                        $notification = [
                            'message' => [
                                'data' => [
                                    'url' => $request->url ?? null,
                                ],
                                'notification' => [
                                    'title' => $request->title,
                                    'body' => $request->discription,
                                    'image' => $notification->image_url ?? null,
                                ],
                                'token' => $user->token,
                            ],
                        ];
                }
                }else{
                    $notification = [
                        'message' => [
                            'data' => [
                                'url' => $request->url ?? null,
                            ],
                            'notification' => [
                                'title' => $request->title,
                                'body' => $request->discription,
                                'image' => $notification->image_url ?? null,
                            ],
                            'token' => $user->token,
                        ],
                    ];
                }
                Helpers::pushNotification($notification);
            }
        }

        return redirect()->route('backend.notifications')->with('message', 'Notification Sent Successfully');
    }

    public function markAsRead($request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success']);
    }

    public function store($request) {}

    public function edit($attributes, $id) {}

    public function update($request, $id) {}

    public function isPrimary($id) {}

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $notification = $this->model->findOrFail($id);
            $notification->destroy($id);
            DB::commit();

            return redirect()->back()->with(['message' => 'Notification Deleted Successfully']);
        } catch (Exception $e) {

            DB::rollback();

            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function deleteRows($request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $document = $this->model::find($request->id[$row]);
                $document->delete();
            }

            return redirect()->back()->with(['message' => 'Notifications Deleted Successfully']);
        } catch (Exception $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }
}
