<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Blog;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $blogs = Blog::whereNull('deleted_at')->where('status', 1);
        $blogs = $blogs->paginate(Helpers::getThemeOptions()['pagination']['blog_per_page']);

        return view('frontend.blog.index', ['blogs' => $blogs]);
    }

    public function details($slug)
    {
        $blog = Blog::where('slug', $slug)->whereNull('deleted_at')?->first();
        $recentBlogs = Blog::whereNot('id', $blog?->id)?->whereNull('deleted_at')?->latest()?->paginate(3);
        if(!$blog) {
            abort(404);
        }
        return view('frontend.blog.details', ['blog' => $blog, 'recentBlogs' => $recentBlogs]);
    }
}