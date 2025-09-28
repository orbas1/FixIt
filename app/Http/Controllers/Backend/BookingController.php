<?php

namespace App\Http\Controllers\Backend;

use App\Enums\RoleEnum;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\DataTables\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\BookingRepository;

class BookingController extends Controller
{
    public $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->authorizeResource(Booking::class, 'booking', ['except' => 'show']);
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BookingDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->repository->show($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    public function getServicemen(Request $request)
    {
        return $this->repository->getServicemen($request->booking_id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        return $this->repository->update($request->all(), $booking->getId($request));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Booking $booking)
    {
        return $this->repository->destroy($booking->getId($request));
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

    public function calculateCommission()
    {
        return $this->repository->calculateCommission();
    }

    public function showChild($id)
    {
        return $this->repository->showChild($id);
    }

    public function reminder()
    {
        return $this->repository->reminder();
    }

    public function assignServicemen(Request $request)
    {
        return $this->repository->assignServicemen($request->all());
    }

    public function updateBookingStatus(Request $request, $booking_id)
    {
        return $this->repository->updateBookingStatus($request->all(), $booking_id);
    }

    public function updateDateTime(Request $request)
    {
        return $this->repository->updateDateTime($request->all());
    }

    public function updatePaymentStatus(Request $request)
    {
        return $this->repository->updatePaymentStatus($request->all());
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }

    public function deleteExtraCharge(Request $request, Booking $booking, $chargeId)
    {
        $user = auth()->user();

        if (!$user->hasRole([RoleEnum::ADMIN, RoleEnum::PROVIDER])) {
            abort(403, 'Unauthorized');
        }

        $extraCharge = $booking->extra_charges()->where('id', $chargeId)->first();

        if (!$extraCharge) {
            return redirect()->back()->with('error', __('static.booking.extra_charge_not_found'));
        }

        $extraCharge->delete();

        return redirect()->back()->with('success', __('static.booking.extra_charge_deleted'));
    }

}
