<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Frontend\ServiceRequestRepository;

class ServiceRequestController extends Controller
{
    public $repository;

    public function __construct(ServiceRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        return $this->repository->store($request);
    }

    public function delete($id)
    {
        return $this->repository->delete( $id);
    }

    public function updateBid(Request $request)
    {
        return $this->repository->updateBid($request);
    }
}
