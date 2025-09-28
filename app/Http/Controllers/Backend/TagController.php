<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\TagDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateTagRequest;
use App\Http\Requests\Backend\UpdateTagRequest;
use App\Models\Tag;
use App\Repositories\Backend\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(TagRepository $repository)
    {
        $this->authorizeResource(Tag::class, 'tag');
        $this->repository = $repository;
    }

    public function index(TagDataTable $dataTable)
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return $dataTable->render('backend.tag.index');
    }


    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTagRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag , TagDataTable $dataTable)
    {
        return $this->repository->edit($tag?->id , $dataTable);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        return $this->repository->update($request->all(), $tag?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        return $this->repository->destroy($tag?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $plan = Tag::find($request->id[$row]);
                $plan->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
