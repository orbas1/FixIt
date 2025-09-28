<?php

namespace App\Repositories\Backend;

use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\Dashboard;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Prettus\Repository\Eloquent\BaseRepository;
use URL;

class DashboardRepository extends BaseRepository
{
    protected $user;

    public function model()
    {
        $this->user = new User();

        return Dashboard::class;
    }

    public function chart($request)
    {
        try {

            $roleName = Helpers::getCurrentRoleName();
            $revenues = $this->getMonthlyRevenues($roleName);
            $months = $this->getYearlyMonths();
            $formattedRevenues = array_map(function ($revenue) {
                return (float) $revenue;
            }, $revenues);

            $data = [
                'revenues' => $formattedRevenues,
                'months' => $months,
            ];

            return $data;
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getYearlyMonths()
    {
        $year = Carbon::now()->format('y');

        return collect(range(1, 12))->map(function ($month) use ($year) {
            return Carbon::createFromDate(null, $month, 1)->format('M \''.$year);
        })->toArray();
    }

    public function getMonthlyRevenues($roleName)
    {
        $months = range(1, 12);
        $perMonthRevenues = [];

        foreach ($months as $month) {
            $perMonthRevenues[] = (float) $this->getCompleteBooking($roleName)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', Carbon::now()->year)->sum('total');

        }

        return $perMonthRevenues;
    }

    public function getCompleteBooking($roleName)
    {
        $bookings = Booking::whereNull('deleted_at')->where('payment_status', PaymentStatus::COMPLETED);

        if ($roleName == RoleEnum::PROVIDER) {
            return $bookings->where('provider_id', auth()->user()->id);
        } elseif ($roleName == RoleEnum::SERVICEMAN) {
            return $bookings->whereHas('servicemen', function ($query) {
                $query->where('users.id', auth()->user()->id);
            });
        }


        return $bookings->whereNotNull('parent_id');
    }

    public function getTopProviders()
    {
        $dateRange = Helpers::getStartAndEndDate(request('sort'), request('start'), request('end'));
        $start_date = $dateRange['start'] ?? null;
        $end_date = $dateRange['end'] ?? null;

        $providers = $this->user->role(RoleEnum::PROVIDER)
            ->whereNull('deleted_at');

        if ($start_date && $end_date) {
            $providers->whereBetween('created_at', [$start_date, $end_date]);
        } else {
            $providers->whereYear('created_at', date('Y'));
        }

        return $providers->having('bookings_count', '>', 0)->orderByDesc('bookings_count');
    }


    public function getTopServicemen($providerId)
    {
        $dateRange = Helpers::getStartAndEndDate(request('sort'), request('start'), request('end'));
        $start_date = $dateRange['start'] ?? null;
        $end_date = $dateRange['end'] ?? null;

        $servicemen = $this->user->role(RoleEnum::SERVICEMAN)
            ->whereNull('deleted_at')
            ->where('system_reserve', 0)
            ->withCount(['servicemen_bookings as servicemen_bookings_count'])
            ->having('servicemen_bookings_count', '>', 0)
            ->orderByDesc('servicemen_bookings_count');

    if ($start_date && $end_date) {
        $servicemen->whereBetween('created_at', [$start_date, $end_date]);
    } else {
        $servicemen->whereYear('created_at', date('Y'));
    }
        if ($providerId) {
            return $servicemen->where('provider_id', $providerId);
        }

        return $servicemen;
    }

    public function upload($request)
    {
        $image = $request->file('file');
        $img_name = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/storage/upload/ckeditor');
        $imagePath = $destinationPath.'/'.$img_name;
        $image->move($destinationPath, $img_name);
        $url = URL::to('/storage/upload/ckeditor'.$img_name);

        return response()->json(['location' => $url]);
    }
}
