<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateBlogCategoriesRequest;
use App\Http\Requests\Backend\UpdateBlogCategoriesRequest;
use App\Models\Category;
use App\Repositories\Backend\BlogCategoryRepository;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(BlogCategoryRepository $repository)
    {
        $this->authorizeResource(Category::class, 'blog_category');
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBlogCategoriesRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $blog_category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $blog_category)
    {
        return $this->repository->edit($blog_category, $blog_category?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogCategoriesRequest $request, Category $blog_category)
    {
        return $this->repository->update($request, $blog_category?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $blog_category)
    {
        return $this->repository->destroy($blog_category?->id);
    }

    public function changeIsFeatured(Request $request)
    {
        return $this->repository->changeIsFeatured($request->statusVal, $request->subject_id);
    }

    public function changeStatus(Request $request)
    {
        return $this->repository->changeStatus($request->statusVal, $request->subject_id);
    }
}
