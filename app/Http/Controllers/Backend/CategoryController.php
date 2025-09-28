<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateCategoriesRequest;
use App\Http\Requests\Backend\UpdateCategoriesRequest;
use App\Models\Category;
use App\Models\Zone;
use App\Repositories\Backend\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->authorizeResource(Category::class, 'category');
        $this->repository = $repository;
    }

    public function index()
    {
        $categories = $this->repository->withOutParent()->where('category_type', CategoryType::SERVICE);
        if (!empty(request()->search)) {
            $categories = $categories->where('title', 'LIKE', '%' . request()->search . '%');
        }
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        $childs = Category::getAllChildCategories();
        $dropdownOptions = Category::where([['category_type', '=', 'service']])->whereNull(['parent_id', 'deleted_at'])->pluck('title', 'id');
        
        return view('backend.category.index',[
                'categories' => $categories->get(),
                'allparent' => $dropdownOptions,
                'zones' => $this->getZones(),
                'childs' => $childs
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoriesRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $cat = $category;
        $categories = $this->repository->withOutParent()->where('category_type', CategoryType::SERVICE)->get();

        $childs = Category::getAllChildCategories();

        $dropdownOptions = Category::getCategoryDropdownOptions();
        return view('backend.category.edit', [
            'cat' => $cat,
            'categories' => $this->repository->withOutParent()->where('category_type', CategoryType::SERVICE)->get(),
            'zones' => $this->getZones(),
            'default_zones' => $this->getDefaultZones($cat),
            'allparent' => $dropdownOptions,
            'childs' => $childs
        ]);
    }

    public function getDefaultZones($cat)
    {
        $zones = [];
        foreach ($cat->zones as $zone) {
            $zones[] = $zone->id;
        }
        $zones = array_map('strval', $zones);

        return $zones;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoriesRequest $request, Category $category)
    {
        return $this->repository->update($request, $category->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        return $this->repository->destroy($category?->id);
    }

    public function changeIsFeatured(Request $request)
    {
        return $this->repository->changeIsFeatured($request->statusVal, $request->subject_id);
    }

    public function changeStatus(Request $request)
    {
        return $this->repository->changeStatus($request->statusVal, $request->subject_id);
    }

    private function getCategories()
    {
        return $this->repository->Wherenull('parent_id')->pluck('title', 'id');
    }

    public function getZones()
    {
        return Zone::where('status', true)->pluck('name', 'id');
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }

    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
}
