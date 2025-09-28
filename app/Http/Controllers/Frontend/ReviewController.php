<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

     protected $repository;


    public function __construct(ReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        return $this->repository->store($request);
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update( $request->all(), $id );
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }
}
