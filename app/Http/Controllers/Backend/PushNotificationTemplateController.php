<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PushNotificationTemplate;
use App\Repositories\Backend\PushNotificationTemplateRepository;

class PushNotificationTemplateController extends Controller
{
    protected $repository;

    public function __construct(PushNotificationTemplateRepository $repository)
    {
        // $this->authorizeResource(PushNotification::class, 'pushNotification');
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }
    
    public function edit(Request $request , $slug)
    {
        return $this->repository->edit($request->all(),$slug);
    }

    public function update(Request $request , $slug)
    {
        return $this->repository->update($request->all(),$slug);
    }
}
