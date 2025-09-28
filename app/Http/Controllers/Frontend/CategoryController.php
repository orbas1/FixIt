<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\ServiceTypeEnum;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories = Category::where('category_type', 'service')?->with('services')?->whereNull('parent_id')->whereNull('deleted_at');
        $zoneIds = session('zoneIds', []);
        $categories =  $categories?->whereRelation('zones', function ($zones) use ($zoneIds) {
                $zones->WhereIn('zone_id', $zoneIds);
        });

        return view('frontend.category.index', ['categories' => $categories]);
    }
}
