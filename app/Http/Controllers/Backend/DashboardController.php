<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Repositories\Backend\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show Admin Dashboard
     */
    public function index(Request $request)
    {
        $dateRange = Helpers::getStartAndEndDate(request('sort'), request('start'), request('end'));
        $start_date = $dateRange['start'] ?? null;
        $end_date = $dateRange['end'] ?? null;

        $providerId = null;
        $servicemanId = null;
        $services = Service::whereNull('deleted_at');

        if ($start_date && $end_date) {
            $services->whereBetween('created_at', [$start_date, $end_date]);
        } else {
            $services->whereYear('created_at', date('Y'));
        }

        $services->having('bookings_count', '>', 0)
                 ->orderByDesc('bookings_count');


        $reviews = Review::with('service')->whereNotNull('service_id');

        if ($start_date && $end_date) {
            $reviews->whereBetween('created_at', [$start_date, $end_date]);
        } else {
            $reviews->whereYear('created_at', date('Y'));
        }

        if (auth()->check() && auth()?->user()?->hasRole('provider')) {
            $providerId = auth()?->user()?->id;
            $services = $services->where('user_id', $providerId);
            $reviews = $reviews->where('provider_id', $providerId);
        } else if (auth()->check() && auth()?->user()?->hasRole('serviceman')){
            $servicemanId = auth()?->user()?->id;
            $reviews = $reviews->where('serviceman_id', $servicemanId);

        }

        return view('backend.dashboard.index')->with([
            'data' => $this->chart($request),
            'Providers' => $this->fetchTopProviders()?->paginate(4),
            'topServicemen' => $this->getTopServicemen($providerId)?->paginate(5),
            'bookings' => Booking::getFilteredBookings($providerId,$servicemanId,$start_date , $end_date),
            'blogs' => Blog::whereNull('deleted_at')->paginate(2),
            'services' => $services->paginate(4),
            'reviews' => $reviews->paginate(4),
        ]);
    }

    public function chart($request)
    {
        return $this->repository->chart($request);
    }

    public function getTopServicemen($providerId)
    {
        return $this->repository->getTopServicemen($providerId);
    }

    public function fetchTopProviders()
    {
        return $this->repository->getTopProviders();
    }

    public function upload(Request $request)
    {
        return $this->repository->upload($request);
    }
}
