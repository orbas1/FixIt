<?php

namespace App\Repositories\API;

use App\Enums\CategoryType;
use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\CommissionTrait;
use App\Models\Booking;
use App\Models\Category;
use App\Models\CommissionHistory;
use App\Models\Home;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class HomeRepository extends BaseRepository
{
    use CommissionTrait;

    public function model()
    {
        return Home::class;
    }

    public function index($request)
    {
        try {

            $roleName = Helpers::getCurrentRoleName();

            return response()->json(['success' => true, 'data' => [
                'total_revenue' => $this->getTotalRevenue($roleName),
                'total_Bookings' => $this->getTotalBookings($roleName),
                'total_users' => $this->getTotalUsers(),
                'total_services' => $this->getTotalService($roleName),
                'total_providers' => $this->getTotalProviders(),
                'total_categories' => $this->getTotalCategories($roleName),
                'total_servicemen' => $this->getTotalServicemen($roleName),
            ]]);
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

    public static function getMonthlyCompletedOrder($month, $year, $roleName)
    {
        $bookings = Booking::whereMonth('created_at', $month)->whereYear('created_at', $year)->whereNull('deleted_at');
        if ($roleName == RoleEnum::PROVIDER) {
            return $bookings->where('provider_id', Helpers::getCurrentProviderId())->get();
        }

        return $bookings;
    }

    public function getMonthlyRevenues($roleName)
    {
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        $perMonthRevenues = [];
        foreach ($months as $key => $month) {
            $perMonthRevenues[$month] = (float) $this->getCompleteBooking($roleName)
                ->whereMonth('created_at', $key + 1)
                ->whereYear('created_at', Carbon::now()->year)->sum('total');
        }

        return $perMonthRevenues;
    }

    public function chart($request)
    {
        try {
            $roleName = Helpers::getCurrentRoleName();
            $data['monthlyRevenues'] = $this->getMonthlyRevenues($roleName);
            $data['yearlyRevenues'] = $this->getYearlyRevenues($roleName);
            $data['weekdayRevenues'] = $this->getWeekdayRevenues($roleName);

            return response()->json($data);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getYearlyRevenues($roleName)
    {
        $years = range(Carbon::now()->year - 6, Carbon::now()->year);
        $perYearRevenues = [];

        foreach ($years as $year) {
            $perYearRevenues[$year] = $this->getCompleteBooking($roleName)
                ->whereYear('created_at', $year)->sum('total');
        }

        return $perYearRevenues;
    }

    public function getYearlyCommissions($roleName)
    {
        $years = range(Carbon::now()->year - 6, Carbon::now()->year);
        $perYearCommissions = [];

        foreach ($years as $year) {
            $commissionHistory = CommissionHistory::whereYear('created_at', $year)->whereNull('deleted_at');

            if ($roleName == RoleEnum::PROVIDER) {
                $perYearCommissions[$year] = $this->getYearlyProviderCommissions($commissionHistory);
            } else {
                $perYearCommissions[$year] = $this->getYearlyAdminCommissions($commissionHistory);
            }
        }

        return response()->json(['success' => true, 'data' => $perYearCommissions]);
    }

    public function getYearlyProviderCommissions($yearlyCommissions)
    {
        return $yearlyCommissions->where('provider_id', Helpers::getCurrentProviderId())->sum('provider_commission');
    }

    public function getYearlyAdminCommissions($yearlyCommissions)
    {
        return $yearlyCommissions->sum('admin_commission');
    }

    public function getWeekdayRevenues($roleName)
    {
        $weekdays = [
            'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
        ];

        $startDate = now()->subDays(7)->startOfDay();
        $endDate = now()->endOfDay();

        $perWeekdayRevenues = [];

        foreach ($weekdays as $key => $weekday) {
            $perWeekdayRevenues[$weekday] = (float) $this->getCompleteBooking($roleName)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereRaw('DAYOFWEEK(created_at) = ?', [$key + 1])
                ->sum('total');
        }

        return $perWeekdayRevenues;
    }

    public function getWeekdayCommissions($roleName)
    {
        $weekdays = range(0, 6);
        $perWeekdayCommissions = [];

        foreach ($weekdays as $weekday) {
            $commissionHistory = CommissionHistory::whereRaw('DAYOFWEEK(created_at) = ? and deleted_at is null', [$weekday + 1]);

            if ($roleName == RoleEnum::PROVIDER) {
                $perWeekdayCommissions[$weekday] = $this->getWeekdayProviderCommissions($commissionHistory);
            } else {
                $perWeekdayCommissions[$weekday] = $this->getWeekdayAdminCommissions($commissionHistory);
            }
        }

        return $perWeekdayCommissions;
    }

    public function getWeekdayProviderCommissions($weekdayCommissions)
    {
        return $weekdayCommissions->where('provider_id', Helpers::getCurrentProviderId())->sum('provider_commission');
    }

    public function getWeekdayAdminCommissions($weekdayCommissions)
    {
        return $weekdayCommissions->sum('admin_commission');
    }

    public function getTotalService($roleName)
    {
        $services = Service::whereNull('deleted_at')->get();
        if ($roleName == RoleEnum::PROVIDER) {
            return $services->where('user_id', Helpers::getCurrentProviderId())->count();
        }

        return $services->count();
    }

    public function getTotalServicemen($roleName)
    {
        if ($roleName == RoleEnum::PROVIDER) {
            $provider = Helpers::getCurrentUser();
            $servicemenCount = $provider->servicemans->count();

            return $servicemenCount;
        }

        return 0;
    }

    public function getTotalProviders()
    {
        return User::role(RoleEnum::PROVIDER)->whereNull('deleted_at')->count();
    }

    public function getTotalUsers()
    {
        $rolesToExclude = [RoleEnum::ADMIN, RoleEnum::PROVIDER];

        return User::whereHas('roles', function ($query) use ($rolesToExclude) {
            $query->whereNotIn('name', $rolesToExclude);
        })->whereNull('deleted_at')->count();
    }

    public function getTotalCategories($roleName)
    {
        $categories = Category::where([
            'parent_id' => null,
            'status' => true,
            'category_type' => CategoryType::SERVICE,
        ]);
        if ($roleName = RoleEnum::PROVIDER) {
            $provider = Helpers::getCurrentUser();
            $services = $provider->services;
            $serviceIds = $services->pluck('id')->toArray();
            $categories->whereExists(function ($query) use ($serviceIds) {
                $query->select(DB::raw(1))
                    ->from('service_categories')
                    ->join('services', 'services.id', '=', 'service_categories.service_id')
                    ->whereColumn('categories.id', 'service_categories.category_id')
                    ->whereIn('services.id', $serviceIds)
                    ->whereNull('services.deleted_at');
            });
        }

        return $categories->count();
    }

    public function getTotalBookings($roleName)
    {
        return $this->getCompleteBooking($roleName)->count();
    }

    public function getTotalRevenue($roleName)
    {
        if ($roleName == RoleEnum::PROVIDER) {
            $totalRevenue =  CommissionHistory::where('provider_id', Helpers::getCurrentProviderId())->sum('provider_commission');
        } else {
            $totalRevenue = CommissionHistory::sum('admin_commission');
        }

        return $totalRevenue;
    }

    public function getCompleteBooking($roleName)
    {
        $bookings = Booking::whereNull('deleted_at')->where('payment_status', PaymentStatus::COMPLETED);
        if ($roleName == RoleEnum::PROVIDER) {
            return $bookings->whereNotNull('parent_id')->where('provider_id', Helpers::getCurrentProviderId());
        }

        return $bookings;
    }

    public function getTopCategoryEarnings()
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'total_earnings' => 0,
                'category_earnings' => [],
            ]);
        }

        if ($user->hasRole('provider')) {
            $categoryEarnings = CommissionHistory::where('provider_id', $user->id)
                                ->select('category_id', DB::raw('SUM(provider_commission) as total_earnings'))
                                ->groupBy('category_id')
                                ->with('category:id,title')
                                ->take(5)
                                ->get();
        } elseif ($user->hasRole('serviceman')) {
            $categoryEarnings = CommissionHistory::select(
                    'commission_histories.category_id',
                    DB::raw('SUM(serviceman_commissions.commission) as total_earnings')
                )
                ->join('serviceman_commissions', 'serviceman_commissions.commission_history_id', '=', 'commission_histories.id')
                ->where('serviceman_commissions.serviceman_id', $user->id)
                ->groupBy('commission_histories.category_id')
                ->with('category:id,title')
                ->take(5)
                ->get();
        } else {
            return response()->json([
                'total_earnings' => 0,
                'category_earnings' => [],
            ]);
        }

        $totalEarnings = $categoryEarnings->sum('total_earnings');
        $categoryPercentages = $categoryEarnings->map(function ($earning) use ($totalEarnings) {
        $percentage = $totalEarnings > 0 ? ($earning->total_earnings / $totalEarnings) * 100 : 0;

            return [
                'category_name' => $earning?->category?->title ?? 'N/A',
                'percentage' => round($percentage, 2),
            ];
        });

        return response()->json([
            'total_earnings' => round($totalEarnings, 2),
            'category_earnings' => $categoryPercentages,
        ]);

        // $providerId = Helpers::getCurrentProviderId();
        // $categoryEarnings = CommissionHistory::where('provider_id', $providerId)
        //                     ->select('category_id', DB::raw('SUM(provider_commission) as total_earnings'))
        //                     ->groupBy('category_id')
        //                     ->with('category:id,title')
        //                     ->take(5)
        //                     ->get();

        // $totalEarnings = $categoryEarnings->sum('total_earnings');
        // $categoryPercentages = $categoryEarnings->map(function ($earning) use ($totalEarnings) {
        // $percentage = ($earning->total_earnings / $totalEarnings) * 100;

        //     return [
        //         'category_name' => $earning?->category?->title,
        //         'percentage' => round($percentage, 2),
        //     ];
        // });

        // return response()->json([
        //     'total_earnings' => $totalEarnings,
        //     'category_earnings' => $categoryPercentages,
        // ]);
    }
}
