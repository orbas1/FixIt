<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\CommissionHistory;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CommissionHistoriesExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $Commissions = CommissionHistory::get();
        return $Commissions;
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
          'Booking Number',
          'Provider Name',
          'Admin Commission',
          'Provider Commission',
          'Created At'
        ];
    }

    public function map($Commission): array
    {

        return [
            $Commission->booking->booking_number ?? 'N/A',
            $Commission->provider->name,
            Helpers::getSettings()['general']['default_currency']->symbol.''.$Commission->admin_commission,
            Helpers::getSettings()['general']['default_currency']->symbol.''.$Commission->provider_commission,
            date('d-M-Y', strtotime($Commission->created_at))

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
            'Booking Number',
            'Provider Name',
            'Admin Commission',
            'Provider Commission',
              'Created At'
        ];
    }

}
