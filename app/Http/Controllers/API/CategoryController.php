<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryListResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\API\CategoryRepository;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public $model;

    protected $service;

    public function __construct(CategoryRepository $repository)
    {
        $this->authorizeResource(Category::class, 'category', [
            'except' => ['index', 'show'],
        ]);

        $this->repository = $repository;
    }

    public function getAllCategories(Request $request)
    {
        try {

            $categories = $this->repository;
            if ($request->zone_ids) {
                $zone_ids = explode(',', $request->zone_ids);
                $categories = Helpers::getCategoriesByZoneIds($zone_ids);
            }

            $categories = $categories->where(['category_type' => CategoryType::SERVICE, 'status' => true])->with('hasSubCategories')->latest('created_at')->simplePaginate(
                $request->paginate ?? $categories->count()
            );

            return CategoryListResource::collection($categories);

            // return $categories = $categories->with(['hasSubCategories'])->latest('created_at')->paginate($request->paginate ?? $categories->count());

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $categories = $this->repository->where(['category_type' => CategoryType::SERVICE, 'status' => true])->whereNull('parent_id');
            if ($request->search) {
                $categories = $categories->where('title', 'like', '%'.$request->search.'%');
            }

            if ($request->providerId) {
                $providerId = $request->providerId;
                $categories = $categories->whereHas('services', function (Builder $services) use ($providerId) {
                    $services->whereHas('user', function (Builder $providers) use ($providerId) {
                        $providers->where('id', $providerId);
                    });
                });
            }

            if ($request->categoryId) {
                $categories = $categories->findOrFail($request->categoryId)->childs();
            }

            if ($request->zone_ids) {
                $zone_ids = explode(',', $request->zone_ids);
                $categories = $categories->whereRelation('zones', function ($zones) use ($zone_ids) {
                    $zones->WhereIn('zone_id', $zone_ids);
                });
            }
            $categories = $categories->with(['hasSubCategories.hasSubCategories'])->latest('created_at')->paginate($request->paginate ?? $categories->count());

            return CategoryResource::collection($categories);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function categoryServices(Request $request)
    {
        try {
            $RoleName = Helpers::getCurrentRoleName();
            $user = Helpers::getCurrentUser();

            if($RoleName === RoleEnum::PROVIDER){
                $services = $user->services()->whereNull('parent_id')->with('categories')->get();
                $providerCategories = $services->pluck('categories')->flatten()->unique('id')->values();

                if ($request->filled('category_id')) {
                    // Get only subcategories from the provider's available categories
                    $categories = Category::where('parent_id', $request->category_id)->where('category_type', CategoryType::SERVICE)->with('media')->orderBy('title')->get();
                } else {
                    $categories = $providerCategories->sortBy('title');
                }

                return response()->json([
                    'categories' => CategoryResource::collection($categories),
                    'services' => ServiceResource::collection($services),
                ]);
            }

            if (empty($request->zone_ids) && $RoleName === RoleEnum::PROVIDER) {
                return response()->json([
                    'categories' => [],
                    'services' => []
                ]);
            }

            $zone_ids = array_filter(explode(',', $request->zone_ids));

            if (empty($zone_ids) && $RoleName === RoleEnum::PROVIDER) {
                return response()->json([
                    'categories' => [],
                    'services' => []
                ]);
            }

            $categories = Category::where(function ($query) use ($request) {
                $query->where('id', $request->category_id)
                    ->orWhere('parent_id', $request->category_id);
            })
            ->where('category_type', 'service', 'service.user')
            ->whereHas('zones', function ($zones) use ($zone_ids) {
                $zones->whereIn('zone_id', $zone_ids);
            });

            $categoryIds = $categories->pluck('id')->toArray();

            $query = Service::query()
                ->whereNull('parent_id')
                ->with(['categories:id,title', 'user.media'])
                ->orderByDesc('is_advertised')
                ->latest('created_at');

            if (!empty($categoryIds)) {
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            } else {
                return response()->json([
                    'categories' => CategoryResource::collection([]),
                    'services' => []
                ]);
            }

            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('providerIds')) {
                $providerIds = explode(',', $request->providerIds);
                $query->whereIn('user_id', $providerIds);
            }

            if ($request->providerId) {
                $query->where('user_id', $request->providerId);
            }

            if ($request->filled('min') && $request->filled('max')) {
                $query->whereBetween('price', [$request->min, $request->max]);
            }

            $services = $query->paginate($request->paginate ?? $query->count());

            return response()->json([
                'categories' => CategoryResource::collection($categories->orderBy('title')->get()),
                'services' => ServiceResource::collection($services),
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->repository->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getCategoryCommission(Request $request)
    {
        try {
            return $this->repository->getCategoryCommission($request);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
