<?php

namespace App\Repositories\API;

use Exception;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Http\Resources\AdditionalServiceResource;
use Illuminate\Support\Facades\Auth;

class AdditionalServiceRepository extends BaseRepository
{
    public function model()
    {
        return Service::class;
    }

    public function index($request)
    {
        $query = Service::query()->whereNotNull('parent_id')->latest('created_at');
        $userId = Helpers::getCurrentUserId();
        $role = Helpers::getCurrentRoleName();
        if ($role === RoleEnum::PROVIDER) {
            $query->where('user_id', $userId);
        }
        $addons = $query->paginate($request->paginate ?? $query->count()); 
        return response()->json([
            'success' => true,
            'data' => AdditionalServiceResource::collection($addons),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($request)
    {
        DB::beginTransaction();
        try {

            $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
            if (Helpers::isUserLogin()) {
               $roleName = Helpers::getCurrentRoleName();
               if ($roleName == RoleEnum::PROVIDER) {
                   $provider = Auth::user();
                    if ($provider->type == UserTypeEnum::COMPANY && !$provider->company) {
                        throw new Exception(__('static.provider.please_fill_company_details_additional_service'), 422);
                    }    

                    $hasDocuments = $provider->UserDocuments()->exists();
                    if (!$hasDocuments) {
                        throw new Exception(__('static.provider.please_upload_documents_first_additional_service'), 422);
                    }
                }
            }

            $additionalService = $this->model->create([
                'title' => $request->title,
                'price' => $request->price,
                'parent_id' => $request->parent_id,
                'status' => $request->status,
                'user_id' => auth()->user()->hasRole(RoleEnum::PROVIDER) ? auth()->id()  : $this->model::find($request['parent_id'])->user_id,
            ]);

            if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                $additionalService->addMedia($request->file('thumbnail'))->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }

            $additionalService->setTranslation('title', $locale, $request['title']);
            $additionalService->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('static.service.add_on_created_successfully')
            ]);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
    
    public function show($id)
    {
        $additionalService = $this->model->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => new AdditionalServiceResource($additionalService)
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $service = $this->model->findOrFail($id);
            $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
            if(isset($request['title'])){
                $service->setTranslation('title', $locale, $request['title']);
            }
            $request = Arr::except($request, 'title');
            $service->update($request);

            if (isset($request['thumbnail'])) {
                $existingImages = $service->getMedia('thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $service->addMedia($request['thumbnail'])->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('static.service.add_on_updated_successfully'),
            ]);

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        $addon = $this->model->where('id', $id)->whereNotNull('parent_id')->first();

        if (!$addon) {
            return response()->json([
                'status' => false,
                'message' => __('static.service.addon_not_found')
            ], 404);
        }

        $addon->delete();
        return response()->json([
            'success' => true,
            'message' => __('static.service.add_on_deleted_successfully'),
        ]); 
    }

}
