<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $serviceCategories = Category::getDropdownOptions();

        return view('frontend.home.index', [
            'SEOData' => new SEOData(
                title: 'Awesome News - My Project',
                description: 'Lorem Ipsum',
            ),
            'serviceCategories' => $serviceCategories
        ]);
    }
}
