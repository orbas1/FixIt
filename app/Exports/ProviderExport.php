<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProviderExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $providers = User::role(RoleEnum::PROVIDER)->where('status', true)->whereNull('deleted_at');
        return $this->filter($providers, request()->all());
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'name',
            'email',
            'password',
            'phone',
            'code',
            'system_reserve',
            'status',
            'is_featured',
            'provider_id',
            'created_by',
            'current_password',
            'new_password',
            'confirm_password',
            'type',
            'slug',
            'experience_interval',
            'is_verified',
            'experience_duration',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'description',
            'served',
            'fcm_token',
            'company_id',
            'location_cordinates'                                           
        ];
    }

    public function map($provider): array
    {
        return [
            $provider->name ?? 'N/A',
            $provider->email ?? 'N/A',
            $provider->type ?? 'N/A',
            $provider->getReviewRatingsAttribute() ?? '0.0',
            $provider->total_provider_commission ?? '0.0',
            Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::PENDING,$provider->id) ?? '0',
            Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::COMPLETED,$provider->id) ?? '0',
            Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::CANCEL,$provider->id) ?? '0',
            Helpers::getTotalProviderBookings($provider->id) ?? '0',
            $provider->servicemans->count() ?? '0',
            $provider->services->count() ?? '0',
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
            'Provider Name',
            'Provider Email',
            'Provider Type',
            'Provider Ratings',
            'Earnings',
            'Pending Bookings',
            'Completed Bookings', 
            'Cancelled Bookings', 
            'Total Bookings	', 
            'Total Servicemen', 
            'Total Services', 
        ];
    }

    public function filter($providers, $request)
    {
        if(isset($request['provider']) && !in_array('all', $request['provider'])) {
            $providers = $providers->whereIn('id',$request['provider']);
        }
        
        if(isset($request['zone']) && !in_array('all', $request['zone'])) {
            $requestedZones = $request['zone'];
            $filteredProviders = $providers->get()->filter(function ($provider) use ($requestedZones) {
                $locationCoordinates = json_decode($provider->location_cordinates);
    
                if (isset($locationCoordinates->lat, $locationCoordinates->lng)) {
                    $zoneIds = Helpers::getZoneByPoint($locationCoordinates->lat, $locationCoordinates->lng)->pluck('id')->toArray();
                    return !empty(array_intersect($zoneIds, $requestedZones));
                }
                return false;
            });
    
            // Convert filtered collection back to an array of IDs and reapply to the query builder
            $providerIds = $filteredProviders->pluck('id')->toArray();
            $providers = $providers->whereIn('id', $providerIds);
        }

        if(isset($request['type']) && !in_array('all', $request['type'])) {
            $providers = $providers->whereIn('type', $request['type']);
        }

        if(isset($request['start_end_date']))
        {
            [$start_date, $end_date] = explode(' to ', $request['start_end_date']);
            $providers =  $providers->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $providers->get();
    }
}