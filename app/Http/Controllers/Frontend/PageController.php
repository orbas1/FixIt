<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function privacy()
    {
        return view('frontend.page.privacy');
    }

    public function terms()
    {
        return view('frontend.page.terms');
    }

    public function details($slug)
    {
        $page = Page::where('slug', $slug)->whereNull('deleted_at')?->first();

        if(!$page) {
            abort(404);
        }
        return view('frontend.page.details', ['page' => $page]);
    }
}
