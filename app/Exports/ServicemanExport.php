<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServicemanExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $serviceman = User::role(RoleEnum::SERVICEMAN)->where('status', true)->whereNull('deleted_at');
        return $serviceman->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'provider_id',
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
            'status',
            // 'is_featured',
            'role'
        ];
    }

    public function map($serviceman): array
    {
        return [
            $serviceman->provider_id,
            $serviceman->name ?? 'N/A',
            $serviceman->email ?? 'N/A',
            $serviceman->password ?? 'N/A',
            $serviceman->phone ?? 'N/A',
            $serviceman->code ?? 'N/A',
            $serviceman->description ?? 'N/A',
            $serviceman->media->first()->original_url ,
            $serviceman->system_reserve ?? 'N/A',
            $serviceman->experience_interval ?? 'N/A',
            $serviceman->experience_duration ?? 'N/A',
            $serviceman->status ?? 'N/A',
            // $serviceman->is_featured ?? 'N/A',
            $serviceman?->role?->id,
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
            'provider_id',
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
            'status',
            // 'is_featured',
            'role'
        ];
    }

   
}
