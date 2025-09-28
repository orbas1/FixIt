<?php

namespace App\Repositories\Backend;

use Exception;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Exports\BookingExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class BookingReportRepository extends BaseRepository
{
    /**
     * Display a listing of the resource.
     */
    function model()
    {
        return Booking::class;
    }
 
    public function index()
    {
        return view('backend.reports.booking');
    }

    public function filter($request)
    {
        $bookings = $this->model->whereNotNull('parent_id');
        if($request->provider && !in_array('all', $request->provider)) {
            $bookings = $bookings->whereIn('provider_id',$request->provider);
        }

        if($request->user && !in_array('all', $request->user)) {
            $bookings = $bookings->whereIn('consumer_id',$request->user);
        }

        if($request->booking_status && !in_array('all', $request->booking_status)) {
            $bookings = $bookings->whereIn('booking_status_id',$request->booking_status);
        }

        if($request->payment_status && !in_array('all', $request->payment_status)) {
            $bookings = $bookings->whereIn('payment_status',$request->payment_status);
        }

        if($request->service && !in_array('all', $request->service)) {
            $bookings = $bookings->whereIn('service_id',$request->service);
        }

        if($request->zone && !in_array('all', $request->zone)) {
            $bookings = $bookings->whereIn('service_id',$request->service);
        }

        if($request->start_end_date)
        {
            [$start_date, $end_date] = explode(' to ', $request->start_end_date);
            $bookings =  $bookings->whereBetween('created_at', [$start_date, $end_date]);
        }

        $bookings = $bookings->paginate(5);
        $bookingReportTable = $this->getbookingReportTable($bookings);

        return response()->json([
            'bookingReportTable' => $bookingReportTable,
            'pagination' => $bookings->links('pagination::bootstrap-4')->render()
        ]);
    }

    public function getbookingReportTable($bookings)
    {
        $bookingReportTable = "";

        if($bookings->isNotEmpty()){
            foreach ($bookings as $booking) {
                $bookingReportTable .= "
                    <tr>
                        <td>
                            <div class='badge badge-primary'>" . $booking?->booking_number . "</div>
                        </td>
                        <td>" . $booking?->provider?->name . "</td>
                        <td>" . $booking?->consumer?->name . "</td>
                        <td>
                            <div class='badge badge-" . $booking?->booking_status?->name . "'>
                                " . ucfirst($booking?->booking_status?->name) . "</div>
                        </td>
                        <td>" . ucfirst($booking?->payment_method) . "</td>
                        <td>
                            <div class='badge badge-" . $booking?->payment_status . "'>
                                " . ucfirst($booking?->payment_status) . "</div>
                        </td>
                        <td>" . $booking?->service?->title . "</td>
                        <td>" . Helpers::getDefaultCurrency()->symbol . " " . $booking?->total . "</td>
                    </tr>";
            }
        }
        else {
            $bookingReportTable .= "
            <tr>
                <td colspan='10' class='text-center'>
                    <div class='no-data'>
                        <span>" . __('static.no_records_found') . "</span>
                    </div>
                </td>
            </tr>";
        }


        return $bookingReportTable;
    }

    public function export($request)
    {
        try {

            $format = $request->get('format', 'csv');    
            switch ($format) {
                case 'excel':
                    return $this->exportExcel();
                case 'csv':
                default:
                    return $this->exportCsv();
            }

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public  function exportExcel()
    {
        return Excel::download(new BookingExport, 'bookings.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new BookingExport, 'bookings.csv');
    }
}
