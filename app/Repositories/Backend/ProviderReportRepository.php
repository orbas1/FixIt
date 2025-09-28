<?php

namespace App\Repositories\Backend;

use App\Exports\ProviderExport;
use Exception;
use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class ProviderReportRepository extends BaseRepository
{
    /**
     * Display a listing of the resource.
     */
    function model()
    {
        return User::class;
    }
 
    public function index()
    {
        return view('backend.reports.provider');
    }

    public function filter($request)
    {
        $providers = $this->model->role(RoleEnum::PROVIDER)->where('status', true)->whereNull('deleted_at');
        if($request->provider && !in_array('all', $request->provider)) {
            $providers = $providers->whereIn('id',$request->provider);
        }
        
        if($request->zone && !in_array('all', $request->zone)) {
            $requestedZones = $request->zone;
            $providers = $providers->get()->filter(function ($provider) use ($requestedZones) {
                $locationCoordinates = json_decode($provider->location_cordinates);
                
                if (isset($locationCoordinates->lat, $locationCoordinates->lng)) {
                    $zoneIds = Helpers::getZoneByPoint($locationCoordinates->lat, $locationCoordinates->lng)->pluck('id')->toArray();

                    return !empty(array_intersect($zoneIds, $requestedZones));
                }
                
                return false;
            });
            $providerIds = $providers->pluck('id')->toArray();
            $providers = $this->model->whereIn('id', $providerIds);
        }
        
        if($request->type && !in_array('all', $request->type)) {
            $providers = $providers->whereIn('type',$request->type);
        }

        if($request->start_end_date)
        {
            [$start_date, $end_date] = explode(' to ', $request->start_end_date);
            $providers =  $providers->whereBetween('created_at', [$start_date, $end_date]);
        }
        $providers = $providers->paginate(7);
        $providerReportTable = $this->getProviderReportTable($providers);

        return response()->json([
            'providerReportTable' => $providerReportTable,
            'pagination' => $providers->links('pagination::bootstrap-5')->render()
        ]);
    }

    public function getProviderReportTable($providers)
    {
        $providerReportTable = "";
        foreach ($providers as $provider) {
            $providerReportTable .= "
                <tr>
                    <td>" . $provider->name . "</td>
                    <td>" . $provider->email . "</td>
                    <td>" . strtoupper($provider->type) . "</td>
                    <td> <i class='ri-star-fill'></i>(".$provider->getReviewRatingsAttribute().")</td>
                    <td>".Helpers::getDefaultCurrency()?->symbol . $provider?->total_provider_commission."</td>
                    <td>".Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::PENDING,$provider->id)."</td>
                    <td>".Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::COMPLETED,$provider->id)."</td>
                    <td>".Helpers::getTotalProviderBookingsByStatus(BookingStatusReq::CANCEL,$provider->id)."</td>
                    <td>".Helpers::getTotalProviderBookings($provider->id)."</td>
                    <td>".$provider->servicemans->count()."</td>
                    <td>".$provider->services->count()."</td>
                </tr>";
        }
        return $providerReportTable;
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
        return Excel::download(new ProviderExport, 'providers.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new ProviderExport, 'providers.csv');
    }
}
