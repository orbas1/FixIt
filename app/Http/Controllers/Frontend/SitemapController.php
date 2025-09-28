<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use App\Models\Service;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
    public function generate()
    {
        $sitemap = Sitemap::create();


        Blog::all()->each(function (Blog $blog) use ($sitemap) {
            $sitemap->add(
                Url::create("/blog/{$blog->slug}")
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($blog->updated_at)
            );
        });

        Category::all()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create("/category")
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($category->updated_at)
            );
        });

        Service::all()->each(function (Service $service) use ($sitemap) {
            $sitemap->add(
                Url::create("/service/{$service->slug}")
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($service->updated_at)
            );
        });

        Page::all()->each(function (Page $page) use ($sitemap) {
            $sitemap->add(
                Url::create("/page/{$page->slug}")
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($page->updated_at)
            );
        });

        return response($sitemap->render())
            ->header('Content-Type', 'application/xml');
    }
}
