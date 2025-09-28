<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class TestimonialRepository extends BaseRepository
{
    public function model()
    {
        return Testimonial::class;
    }

    public function index()
    {
        return view('backend.testimonial.index');
    }

    public function create($attribute = [])
    {
        return view('backend.testimonial.create');
    }

    public function show($id) {}

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $testimonial = $this->model->create(
                [
                    'name' => $request->name,
                    'description' => $request->description,
                    'rating' => $request->rating,
                    'status' => $request->status,
                ]
            );

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $testimonial->addMediaFromRequest('image')->toMediaCollection('image');
            }
            DB::commit();
            return redirect()->route('backend.testimonial.index')->with('message', __('static.testimonials.added'));
        
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $testimonial = $this->model->find($id);
        return view('backend.testimonial.edit', [
            'testimonial' => $testimonial,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $testimonial = $this->model->findOrFail($id);
            $testimonial->update([
                'name' => $request->name,
                'rating' => $request->rating,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $testimonial->clearMediaCollection('image');
                $testimonial->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();

            return redirect()->route('backend.testimonial.index')->with('success', __('static.testimonial.updated'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $testimonial = $this->model->findOrFail($id);
            $testimonial->destroy($id);

            DB::commit();

            return redirect()->back()->with(['message' =>  __('static.testimonial.destroy')]);
        } catch (Exception $e) {

            DB::rollback();

            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        try {

            $testimonial = $this->model->findOrFail($id);
            $testimonial->update(['status' => $status]);

            return json_encode(['resp' => $testimonial]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereIn('id', $ids)->delete();

            return back()->with('message', __('static.testimonial.destroy'));
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }
}
