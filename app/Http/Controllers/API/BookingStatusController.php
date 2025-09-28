<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateBookingStatusRequest;
use App\Http\Requests\API\UpdateBookingStatusRequest;
use App\Http\Resources\BookingStatusResource;
use App\Models\BookingStatus;
use App\Repositories\API\BookingStatusRepository;
use Exception;
use Illuminate\Http\Request;

class BookingStatusController extends Controller
{
    protected $repository;

    public function __construct(BookingStatusRepository $repository)
    {
        $this->repository = $repository;
        $this->authorizeResource(BookingStatus::class, 'orderStatus', [
            'except' => 'index', 'show',
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $bookingStatus = $this->repository;
            $bookingStatus = $this->filter($bookingStatus, $request);

            $items = $bookingStatus->oldest('sequence')->paginate($request->paginate ?? $bookingStatus->count());
            return response()->json([
                'success' => true,
                'data' => BookingStatusResource::collection($items)
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBookingStatusRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(BookingStatus $bookingStatus)
    {
        return $this->repository->show($bookingStatus->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookingStatusRequest $request, BookingStatus $bookingStatus)
    {
        return $this->repository->update($request->all(), $bookingStatus->getId($request));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, BookingStatus $bookingStatus)
    {
        return $this->repository->destroy($bookingStatus->getId($request));
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $status
     * @return \Illuminate\Http\Response
     */
    public function status($id, $status)
    {
        return $this->repository->status($id, $status);
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function filter($bookingStatus, $request)
    {
        if ($request->field && $request->sort) {
            $bookingStatus = $bookingStatus->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $bookingStatus = $bookingStatus->where('status', $request->status);
        }

        return $bookingStatus;
    }
}
