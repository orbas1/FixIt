<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateReviewRequest;
use App\Http\Requests\API\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Repositories\API\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public $repository;

    public $model;

    public function __construct(ReviewRepository $repository,Review $model)
    {
        $this->repository = $repository;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reviewsQuery = $this->filter($this->repository->with(['service:id,title','service.media', 'consumer:id,name','consumer.media', 'serviceman:id,name','serviceman.media', 'provider:id,name','provider.media']), $request);
        $perPage = ($request->paginate ?? $reviewsQuery->count());
        if (!$request->field) {
            $reviewsQuery->latest('created_at');
        }
        $paginated = $reviewsQuery->simplePaginate($perPage);
        return ReviewResource::collection($paginated);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReviewRequest $request)
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
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request,$id)
    {
        return $this->repository->update($request->all(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Review $review)
    {
        return $this->repository->destroy($review->id);
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->id);
    }

    public function filter($reviews, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::PROVIDER) {
            $reviews = $reviews->where('provider_id', auth()->user()->id);
        }

        if ($roleName == RoleEnum::CONSUMER) {
            $reviews = $reviews->where('consumer_id', auth()->user()->id);
        }

        if ($roleName == RoleEnum::SERVICEMAN) {
            $reviews = $reviews->where('serviceman_id', auth()->user()->id);
        }

        if ($request->service_id) {
            $reviews = $reviews->where('service_id', $request->service_id);
        }

        if ($request->field && $request->sort) {
            $reviews = $reviews->orderBy($request->field, $request->sort);
        }

        return $reviews;
    }

    public function getProviderReviews(Request $request, $provider_id)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName != RoleEnum::CONSUMER) {
            return response()->json([
                'success' => false,
                'message' => __('errors.you_do_not_have_permission_to_access_this_resource')
            ], 403);
        }

        $reviews = Review::with(['consumer:id,name', 'service:id,title'])
            ->where('provider_id', $provider_id)
            ->orderBy('created_at', 'desc');
        $perPage = ($request->paginate ?? $reviews->count());
        $paginated = $reviews->simplePaginate($perPage);

        return ReviewResource::collection($paginated);
    }
}
