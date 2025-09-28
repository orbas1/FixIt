<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Frontend\ServicePackageRepository;

class ServicePackageController extends Controller
{
    public $repository;

    public function __construct(ServicePackageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $servicePackages = $this->filter($this->repository?->where('status', true));
        $servicePackages = $servicePackages->paginate(Helpers::getThemeOptions()['pagination']['service_package_per_page']);

        return view('frontend.service-package.index', ['servicePackages' => $servicePackages]);
    }

    public function details($slug)
    {
        return $this->repository->details($slug);
    }

    public function selectServicemen()
    {
        return $this->repository->selectServicemen();
    }

    public function filter($servicePackages)
    {
        $zoneIds = session('zoneIds', []);
        $servicePackages = $servicePackages->whereHas('services', function (Builder $services) use ($zoneIds) {
            $services->whereHas('categories', function (Builder $categories) use ($zoneIds) {
                $categories->whereHas('zones', function (Builder $zones) use ($zoneIds) {
                    $zones->WhereIn('zones.id', $zoneIds);
                });
            });
        });

        return  $servicePackages;
    }
}
