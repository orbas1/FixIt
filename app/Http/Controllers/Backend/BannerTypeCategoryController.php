<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerTypeCategoryController extends Controller
{
    public function getBannerCategory(Request $request)
    {
        $bannerCategory['bannerCategory'] = Helpers::getBannerCategories($request->bannerType);

        return response()->json($bannerCategory);
    }
}
