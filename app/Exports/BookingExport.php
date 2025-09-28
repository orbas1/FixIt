<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BookingExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $bookings = Booking::whereNotNull('parent_id');
        return $this->filter($bookings, request()->all());
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'id',
            'booking_number',
            'consumer_id',
            'provider_id',
            'service_id',
            'service_package_id',
            'service_price',
            'type',
            'tax',
            'per_serviceman_charge',
            'required_servicemen',
            'total_extra_servicemen',
            'total_servicemen',
            'coupon_total_discount',
            'platform_fees',
            'total_extra_servicemen_charge',
            'subtotal',
            'total',
            'date_time',
            'parent_id',
            'booking_status_id',
            'payment_method',
            'payment_status',
            'description',
            'created_by_id',
            'platform_fees_type',                                            
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id ?? 'N/A',
            $booking->booking_number ?? 'N/A',
            $booking->consumer ? ($booking->consumer->name ?? 'N/A') : 'N/A',
            $booking->provider ? ($booking->provider->name ?? 'N/A') : 'N/A',
            $booking->service ? ($booking->service->title ?? 'N/A') : 'N/A',
            $booking->service_package_id ?? 'N/A',
            $booking->service_price ?? 'N/A',
            $booking->type ?? 'N/A',
            $booking->tax ?? 'N/A',
            $booking->per_serviceman_charge ?? 'N/A',
            $booking->required_servicemen ?? 'N/A',
            $booking->total_extra_servicemen ?? 'N/A',
            $booking->total_extra_servicemen_charge ?? 'N/A',
            $booking->total_servicemen ?? 'N/A',
            $booking->coupon_total_discount ?? 0,
            $booking->platform_fees_type ?? 'N/A',
            $booking->platform_fees ?? 'N/A',
            $booking->subtotal ?? 'N/A',
            $booking->total ?? 'N/A',
            $booking->date_time ?? 'N/A',
            $booking->booking_status->name ? ($booking->booking_status->name ?? 'N/A') : 'N/A',
            $booking->payment_method ?? 'N/A',
            $booking->payment_status ?? 'N/A',
            $booking->description ?? 'N/A',
            $booking->created_by->name ?? 'N/A',
        ];
    }

     /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Booking ID',
            'Booking Number',
            'Consumer Name',
            'Provider Name',
            'Service Title',
            'Service Package',
            'Service Price', 
            'Type', 
            'Tax', 
            'Per Serviceman Charge', 
            'Required Servicemen', 
            'Total Extra Servicemen', 
            'Total Extra Servicemen Charge', 
            'Total Servicemen', 
            'Coupon Total Discount', 
            'Platform Fees Type', 
            'Platform Fees', 
            'Subtotal', 
            'Total', 
            'Booking Date', 
            'Booking Status', 
            'Payment Method', 
            'Payment Status', 
            'Description', 
            'Created By', 
        ];
    }

    public function filter($bookings, $request)
    {
        if(isset($request['provider']) && !in_array('all', $request['provider'])) {
            $bookings = $bookings->whereIn('provider_id', $request['provider']);
        }

        if(isset($request['user']) && !in_array('all', $request['user'])) {
            $bookings = $bookings->whereIn('consumer_id', $request['user']);
        }

        if(isset($request['booking_status']) && !in_array('all', $request['booking_status'])) {
            $bookings = $bookings->whereIn('booking_status_id',$request['booking_status']);
        }

        if(isset($request['payment_status']) && !in_array('all', $request['payment_status'])) {
            $requestStatuses = array_map('strtoupper', $request['payment_status']);
            $bookings = $bookings->whereIn('payment_status', $requestStatuses);
        }

        if(isset($request['service']) && !in_array('all', $request['service'])) {
            $bookings = $bookings->whereIn('service_id', $request['service']);
        }
        
        if(isset($request['start_end_date']))
        {
            [$start_date, $end_date] = explode(' to ', $request['start_end_date']);
            $bookings =  $bookings->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $bookings->get();

    }
}