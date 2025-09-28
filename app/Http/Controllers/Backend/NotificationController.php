<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\PushNotificationDataTable;
use App\Repositories\Backend\NotificationRepository;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(PushNotificationDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    public function listNotification()
    {
        return $this->repository->listNotification();
    }

    public function sendNotification(Request $request)
    {
        return $this->repository->sendNotification($request);
    }

    public function markAsRead(Request $request)
    {
        return $this->repository->markAsRead($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function deleteRows(Request $request)
    {
        return $this->repository->deleteRows($request);
    }
}
