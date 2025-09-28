<?php

namespace App\Exports;

use App\Models\Zone;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ZoneExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $zones = Zone::get();

        return $zones;
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
            'locations',
            'status',
        ];
    }

    public function map($zone): array
    {

        return [
            $zone->name,
            $zone->locations,
            $zone->status
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
            'locations',
            'status',
        ];
    }


}
