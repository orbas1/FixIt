<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class AboutUsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('frontend.about-us.index');
    }
}
