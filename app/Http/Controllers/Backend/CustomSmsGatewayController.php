<?php

namespace App\Http\Controllers\Backend;

use App\Models\CustomSmsGateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\CustomSmsGatewayRepository;

class CustomSmsGatewayController extends Controller
{
    public $repository;

    public function __construct(CustomSmsGatewayRepository $repository)
    {
        // $this->authorizeResource(Setting::class, 'setting');
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    public function show() {
        //
    }

    public function update(Request $request, CustomSmsGateway $customSmsGateway)
    {
        return $this->repository->update($request->all(), $customSmsGateway?->id);
    }

    public function test(Request $request)
    {
       
        return $this->repository->test($request);
    }
}
