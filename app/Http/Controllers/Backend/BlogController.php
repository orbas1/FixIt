<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BlogDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateBlogRequest;
use App\Http\Requests\Backend\UpdateBlogRequest;
use App\Models\Blog;
use App\Repositories\Backend\BlogRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(BlogRepository $repository)
    {
        $this->authorizeResource(Blog::class, 'blog');
        $this->repository = $repository;
    }

    public function index(BlogDataTable $dataTable)
    {
        return $dataTable->render('backend.blog.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBlogRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        return $this->repository->edit($blog?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        return $this->repository->update($request, $blog?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        return $this->repository->destroy($blog?->id);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->repository->updateStatus($id, $request->status);
    }

    public function updateIsFeatured(Request $request, $id)
    {
        return $this->repository->updateIsFeatured($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $blog = Blog::find($request->id[$row]);
                $blog->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
