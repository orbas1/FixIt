<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PageDataTable;
use App\DataTables\TestimonialDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateTestimonialRequest;
use App\Http\Requests\Backend\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Repositories\Backend\TestimonialRepository;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(TestimonialRepository $repository)
    {
        $this->authorizeResource(Testimonial::class, 'testimonial');
        $this->repository = $repository;
    }

    public function index(TestimonialDataTable $dataTable)
    {
        return $dataTable->render('backend.testimonial.index');
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
    public function store(CreateTestimonialRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        return $this->repository->edit($testimonial?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        return $this->repository->update($request, $testimonial?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        return $this->repository->destroy($testimonial?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $testimonial = Testimonial::find($request->id[$row]);
                $testimonial->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
