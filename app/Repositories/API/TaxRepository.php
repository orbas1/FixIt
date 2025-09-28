<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Models\Tax;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class TaxRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function model()
    {
        return Tax::class;
    }

    public function show($banner)
    {
        try {
            $item = $this->model->with('media')->findOrFail($banner->id);

            return response()->json(['success' => true, 'data' => $item]);
        } catch (Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $banner = $this->model->create([
                'type' => $request->type,
                'related_id' => $request->related_id,
                'status' => $request->status,
            ]);

            if ($request->images) {
                $images = $request->images;
                foreach ($images as $image) {
                    $banner->addMedia($image)->toMediaCollection('image');
                }
            }
            DB::commit();

            return response()->json([
                'message' => __('static.tax.created'),
                'banner' => $banner,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
