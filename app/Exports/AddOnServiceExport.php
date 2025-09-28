<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AddOnServiceExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $services = Service::whereNotNull('parent_id')->get();
        return $services;
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'title',
            'price',
            'parent_id',
            'user_id',
            'image'
        ];
    }

    public function map($service): array
    {

        return [
            $service->title,
            $service->price,
            $service->parent_id,
            $service?->user_id,
            $service?->media?->first()->original_url
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
            'title',
            'price',
            'parent_id',
            'user_id',
            'image'
        ];
    }


}
