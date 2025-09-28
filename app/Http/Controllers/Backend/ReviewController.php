<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ServicemanReviewDataTable;
use App\DataTables\UserReviewDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateAddressRequest;
use App\Models\Address;
use App\Models\Review;
use App\Repositories\Backend\ReviewRepository;
use App\Http\Requests\Backend\UpdateAddressRequest;
use App\Http\Requests\Backend\UpdateReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public function __construct(ReviewRepository $repository)
    {
        $this->authorizeResource(Review::class, 'review');
        $this->repository = $repository;
    }

    public function index(UserReviewDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    public function servicemenReview(ServicemanReviewDataTable $dataTable)
    {
        return $this->repository->servicemenReview($dataTable);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAddressRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Review $review)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        return $this->repository->update($request->all(), $review->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        return $this->repository->destroy($review?->id);
    }

    public function deleteRows(Request $request)
    {
        return $this->repository->deleteRows($request);
    }
}
