<?php

namespace App\Repositories\API;

use Exception;
use App\Models\Blog;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Service;
use App\Models\Banner;
use App\Models\Address;
use App\Models\Category;
use App\Enums\CategoryType;
use App\Models\Advertisement;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Modules\Coupon\Entities\Coupon;
use App\Exceptions\ExceptionHandler;
use App\Enums\AdvertisementStatusEnum;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Http\Resources\UserDashboardBlogResource;
use App\Http\Resources\UserDashboardBannerResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Http\Resources\UserDashboardCouponResource;
use App\Http\Resources\UserDashboardCategoryResource;
use App\Http\Resources\UserDashboardHomeBannerResource;
use App\Http\Resources\UserDashboardHomeServiceResource;
use App\Http\Resources\UserDashboardServicePackageResource;
use App\Http\Resources\UserDashboardFeaturedServiceResource;
use App\Http\Resources\UserDashboardHighestRatedProviderResource;

class UserRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    protected $role;
    protected $blog;
    protected $banner;
    protected $coupon;
    protected $address;
    protected $service;
    protected $provider;
    protected $category;
    protected $servicePackage;
    protected $advertisement;

    public function model()
    {
        $this->blog = new Blog();
        $this->role = new Role();
        $this->provider = new User();
        $this->banner = new Banner();
        $this->coupon = new Coupon();
        $this->service = new Service();
        $this->address = new Address();
        $this->category = new Category();
        $this->servicePackage = new ServicePackage();
        $this->advertisement = new Advertisement();
        return User::class;
    }

    public function getAllUsers()
    {
        DB::beginTransaction();
        try {
            return $this->model->role('user')->with('addresses');
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            $user = $this->model->findOrFail($id);
            if ($user->hasRole(RoleEnum::ADMIN)) {
                throw new Exception(__('static.users.reserved_user_not_deleted'), 400);
            }

            return $user->destroy($id);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getDashboardData($request)
    {
        try {
            $locale = $request->header('Accept-Lang') ?? app()->getLocale();
            $zone_ids = $request->zone_ids ? [(int) $request->zone_ids] : [];
            $zoneKey = !empty($zone_ids) ? implode('_', $zone_ids) : 'null';
            $cacheKey = "dashboard_banners_{$zoneKey}_{$locale}";
    
            // Banners
            $banners = cache()->remember($cacheKey, now()->addMinute(), function () use ($zone_ids) {
                if (empty($zone_ids)) {
                    return collect();
                }
                return $this->banner
                        ->where('is_offer', false)
                        ->select('id', 'title', 'type', 'related_id')
                        ->whereHas('zones', function (Builder $zones) use ($zone_ids) {
                            $zones->whereIn('zones.id', $zone_ids);
                        })
                        ->latest()
                        ->get();
            });

            // Coupons
            $coupons = cache()->remember('dashboard_coupons_' . implode('_', $zone_ids ?: ['null']), now()->addMinute(), function () use ($zone_ids) {
                $query = $this->coupon->select('id', 'code', 'min_spend', 'type', 'amount', 'title');
                if (!empty($zone_ids)) {
                    $query->whereHas('zones', function ($zoneQuery) use ($zone_ids) {
                        $zoneQuery->whereIn('zones.id', $zone_ids);
                    });
                } else {
                    return collect(); 
                }
                
                return $query->get();
            });

            // Categories with Media
            $categories = cache()->remember('dashboard_categories_' . implode('_', $zone_ids ?: ['null']), now()->addMinute(), function () use ($zone_ids) {
                $query = $this->category->where([
                                            'category_type' => CategoryType::SERVICE, 
                                            'status' => true
                                        ])
                                      ->whereNull('parent_id')
                                      ->orderBy('title')
                                      ->take(8)
                                      ->with('media','hasSubCategories');
                
                if (!empty($zone_ids)) {
                    $query->whereHas('zones', function ($q) use ($zone_ids) {
                        $q->whereIn('zones.id', $zone_ids);
                    });
                } else {
                    return collect();
                }
                return $query->get();
            });

            // Service Packages with Media
            $servicePackages = cache()->remember('dashboard_service_packages', now()->addMinute(), function () {
                return $this->servicePackage->where('status', true)->select('id', 'hexa_code', 'title', 'price', 'provider_id', 'status')
                        ->withCount('services')->having('services_count', '>=', 2)
					    ->with('media') 
                        ->latest()
                        ->take(4)
                        ->get();
            });

            // Featured Services with Media
            $featuredServices = cache()->remember('dashboard_services_' . implode('_', $zone_ids ?: ['null']), now()->addMinute(), function () use ($zone_ids) {
                if (empty($zone_ids)) {
                    return collect();
                }
                return $this->service->where('is_featured', true)->whereNull('deleted_at')->where('status', true)
                                ->whereHas('categories.zones', function ($query) use ($zone_ids) {
                                    $query->whereIn('zones.id', $zone_ids);
                                })
                                 ->select('id', 'title', 'price', 'type', 'duration', 'duration_unit', 'discount', 'required_servicemen', 'service_rate', 'description', 'user_id', 'status')
                                 ->latest()
                                 ->take(2)
                                 ->with('media', 'user:id,name')
                                 ->get();
            });

            return response()->json([
                'banners' => UserDashboardBannerResource::collection($banners),
                'coupons' => UserDashboardCouponResource::collection($coupons),
                'categories' => UserDashboardCategoryResource::collection($categories),
                'servicePackages' => UserDashboardServicePackageResource::collection($servicePackages),
                'featuredServices' => UserDashboardFeaturedServiceResource::collection($featuredServices),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDashboardData2($request)
    {
        try {
            $zone_ids = $request->zone_ids ? [(int) $request->zone_ids] : [];
            if (empty($zone_ids)) {
                return response()->json([
                    'highestRatedProviders' => [],
                    'blogs' => [],
                    'home_banner_advertisements' => [],
                    'home_services_advertisements' => [],
                ]);
            }
            // Providers with Highest Ratings
            $highestRatedProviders = cache()->remember('dashboard_highest_rated_providers', now()->addMinute(), function () {
                return $this->provider->role('provider')
                                      ->with(['media', 'reviews'])
                                      ->withAvg('reviews', 'rating')
                                      ->whereHas('reviews')
                                      ->having('reviews_avg_rating', '>', 0)
                                      ->orderByDesc('reviews_avg_rating')
                                      ->take(2)
                                      ->get();
            });

            // Blogs with Media and Tags
            $blogs = cache()->remember('dashboard_blogs', now()->addMinute(), function () {
                return $this->blog->select('id', 'title', 'description','slug','content','meta_title','meta_description','is_featured','status','created_by_id', 'created_at')
                                  ->with(['media', 'tags', 'created_by','categories'])->take(5)
                                  ->get();
            });

            $currentDate = now();

            $home_banner_advertisements = cache()->remember('dashboard_home_banner_advertisements' . implode('_', $zone_ids ?: ['null']), now()->addMinute(), function() use ($currentDate , $request) {
               return $this->advertisement
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate)
                    ->where('type', 'banner')
                    ->where('screen', 'home')
                    ->whereIn('status', [AdvertisementStatusEnum::APPROVED , AdvertisementStatusEnum::RUNNING])
                    ->where('zone', $request?->zone_ids)
                    ->select('id', 'banner_type', 'video_link' , 'type' , 'screen' ,'provider_id')->with('media')
                    ->get();
            });

            $home_services_advertisements = cache()->remember('dashboard_home_services_advertisements'. implode('_', $zone_ids ?: ['null']), now()->addMinute(), function() use ($currentDate , $request) {
                return $this->advertisement
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate)
                    ->where('type', 'service')
                    ->where('screen', 'home')
                    ->where('zone', $request?->zone_ids)
                    ->whereIn('status', [AdvertisementStatusEnum::APPROVED , AdvertisementStatusEnum::RUNNING])
                    ->select('id', 'banner_type', 'video_link' , 'type' , 'screen' , 'provider_id')->with('services')
                    ->get();
            });

            return response()->json([
                'highestRatedProviders' => UserDashboardHighestRatedProviderResource::collection($highestRatedProviders),
                'blogs' => UserDashboardBlogResource::collection($blogs),
                'home_banner_advertisements' => UserDashboardHomeBannerResource::collection($home_banner_advertisements),
                'home_services_advertisements' => UserDashboardHomeServiceResource::collection($home_services_advertisements),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard data.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
