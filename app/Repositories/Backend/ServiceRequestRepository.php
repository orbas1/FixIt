<?php

namespace App\Repositories\Backend;

use App\Events\CreateServiceRequestEvent;
use Exception;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;
use App\Models\Category;
use Prettus\Repository\Eloquent\BaseRepository;

class ServiceRequestRepository extends BaseRepository
{
    protected $category;
    protected $fieldSearchable = [
        'title' => 'like',
        'initial_price' => 'like'
    ];

    function model()
    {
        $this->category = new Category();
        return ServiceRequest::class;
    }

    public function create($attributes = [])
    {
        $categories = $this->category->getDropdownOptions();
        return view('backend.service-request.create',['categories' => $categories]);
    }

    public function edit($id)
    {

        $serviceRequest = $this->model->findOrFail($id);
        $categories = $this->category->getDropdownOptions();

        return view('backend.service-request.edit',
            [
                'Request' => $serviceRequest,
                'categories' => $categories,
                'default_categories' => $serviceRequest->category_ids
            ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            if (in_array('all', $request->category_id)) {
                $allCategories = Category::pluck('id')->toArray();
                $request->merge(['category_id' => $allCategories]);
            }

            $serviceRequest = $this->model->create([
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'duration_unit' => $request->duration_unit,
                'required_servicemen' => $request->required_servicemen,
                'initial_price' => $request->price,
                'user_id' => Helpers::getCurrentUserId(),
                'booking_date' => $request->booking_date,
                'category_ids' => $request->category_id
            ]);

            if ($request->images) {
                $images = $request->file('images');
                foreach ($images as $image) {
                    $serviceRequest->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
                $serviceRequest->media;
            }

            DB::commit();
            event(new CreateServiceRequestEvent($serviceRequest));

            return redirect()->route('backend.service-requests.index')->with('message', __('static.service_request.store'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $serviceRequest = $this->model->findOrFail($id);
            $serviceRequest->destroy($id);

            DB::commit();

            return redirect()->back()->with('message',__('static.service_request.destroy'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }


}


