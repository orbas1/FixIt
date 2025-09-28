<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProvidersExport implements FromCollection,WithMapping,WithHeadings
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
            'description',
            'image',
            'system_reserve',
            'experience_interval',
            'experience_duration',
            'type',
            'status',
            'is_featured',
            'slug',
            'is_verified',
            'location_cordinates',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'company_description',
            // 'company_logo',
            'role_id',
            'zones',
        ];
    }

    public function map($provider): array
    {

        return [
            $provider->name ?? 'N/A',
            $provider->email ?? 'N/A',
            $provider->password ?? 'N/A',
            $provider->phone ?? 'N/A',
            $provider->code ?? 'N/A',
            $provider->description ?? 'N/A',
            $provider->media->first()->original_url ?? 'N/A',
            $provider->system_reserve ?? 'N/A',
            $provider->experience_interval ?? 'N/A',
            $provider->experience_duration ?? 'N/A',
            $provider->type ?? 'N/A',
            $provider->status ?? 'N/A',
            $provider->is_featured ?? 'N/A',
            $provider->slug ?? 'N/A',
            $provider->is_verified ?? 'N/A',
            $provider->location_cordinates ?? 'N/A',
            $provider?->company->name ?? 'N/A',
            $provider?->company->email ?? 'N/A',
            $provider?->company->phone ?? 'N/A',
            $provider?->company->code ?? 'N/A',
            $provider?->company->description ?? 'N/A',
            // $provider?->company->media?->first()?->original_url,
            $provider?->role?->id,
            $provider?->zones->pluck('id')->toArray() ?? []

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
            'name',
            'email',
            'password',
            'phone',
            'code',
            'description',
            'image',
            'system_reserve',
            'experience_interval',
            'experience_duration',
            'type',
            'status',
            'is_featured',
            'slug',
            'is_verified',
            'location_cordinates',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'company_description',
            // 'company_logo',
            'role_id',
            'zones',
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
