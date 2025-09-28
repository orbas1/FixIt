<?php

namespace App\Repositories\API;

use Exception;
use Carbon\Carbon;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Helpers\Helpers;
use Illuminate\Support\Arr;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Http\Resources\ServicePackageDetailResource;

class ServicePackageRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        return ServicePackage::class;
    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $this->model->where('provider_id', Auth::user()?->id)?->whereNUll('deleted_at')?->count() ?? 0;
                if (Helpers::isModuleEnable('Subscription')) {
                    if (function_exists('isPlanAllowed')) {
                        $isAllowed = isPlanAllowed('allowed_max_service_packages', $maxItems, $provider?->id);
                    }
                }

                if (! $isAllowed) {
                    $settings = Helpers::getSettings();
                    $max_service_packages = $settings['default_creation_limits']['allowed_max_service_packages'];
                    if ($max_service_packages > $maxItems) {
                        $isAllowed = true;
                    }
                }
            }

            return $isAllowed;
        }
    }

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
                        throw new Exception(__('static.provider.please_fill_company_details_package'), 422);
                    }
                    
                    $hasDocuments = $provider->UserDocuments()->exists();
                    if (!$hasDocuments) {
                        throw new Exception(__('static.provider.please_upload_documents_first_package'), 422);
                    }
                }
            }
            if ($this->isProviderCanCreate()) {
                if (!is_array($request->service_id) || count($request->service_id) < 2) {
                    throw new ExceptionHandler(__('validation.at_least_two_services_required'), 422);
                }

                $service_package = $this->model->create([
                    'title' => $request->title,
                    'hexa_code' => $request->hexa_code,
                    'price' => $request->price,
                    'discount' => $request->discount,
                    'description' => $request->description,
                    'disclaimer' => $request->disclaimer,
                    'is_featured' => $request->is_featured,
                    'provider_id' => $request->provider_id,
                    'status' => $request->status,
                    'started_at' => Carbon::createFromFormat('j-M-Y', $request->started_at)->format('Y-m-d'),
                    'ended_at' => Carbon::createFromFormat('j-M-Y', $request->ended_at)->format('Y-m-d'),
                ]);

                if (isset($request->service_id)) {
                    $service_package->services()->attach($request->service_id);
                    $service_package->services;
                }

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $service_package->addMediaFromRequest('image')->withCustomProperties(['language' => $locale])->toMediaCollection('service_package_image');
                }

                $service_package->setTranslation('title', $locale, $request['title']);
                $service_package->setTranslation('description', $locale, $request['description']);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => __('static.service_package.store'),
                ]);
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {

            DB::rollback();
            throw $e;
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
            $service_package = $this->model->findOrFail($id);
            if (!$service_package) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.service_package.service_package_not_found'),
                ]);
            }

            if (isset($request->service_id) && count($request->service_id) < 2) {
                throw new ExceptionHandler(__('validation.at_least_two_services_required'), 422);
            }

            $startedAt = Carbon::createFromFormat('j-M-Y', $request->started_at)->format('Y-m-d');
            $endedAt = Carbon::createFromFormat('j-M-Y', $request->ended_at)->format('Y-m-d');

            $request->merge([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
            ]);

            $service_package->setTranslation('title', $locale, $request['title']);
            $service_package->setTranslation('description', $locale, $request['description']);
            $service_package->setTranslation('disclaimer', $locale, $request['disclaimer']);
            $request = Arr::except($request, ['title', 'description', 'disclaimer']);

            $service_package->update($request->all());
            if ($request->service_id) {
                $service_package->services()->sync($request->service_id);
                $service_package->services;
            }
            if ($request->hasFile('image')) {
                $existingThumbnail = $service_package->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingThumbnail as $media) {
                    $media->delete();
                }
                $service_package->addMedia($request->image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('static.service_package.updated'),
            ]);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $service_package = $this->model->findOrFail($id);
            if (!$service_package) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.service_package.service_package_not_found'),
                ]);
            }
            $service_package->services()->detach();
            $service_package->clearMediaCollection('image');
            $service_package->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('static.service_package.destroy'),
            ]);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $servicePackage = ServicePackage::query()->with(['user' , 'user.media', 'services'])->where('id' , $id)->first();

            return response()->json([
                'success' => true,
                'data' =>  new ServicePackageDetailResource($servicePackage),
            ]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updateStatus($status, $id)
    {
        DB::beginTransaction();
        try {
            $role = Helpers::getCurrentRoleName();
            if ($role !== RoleEnum::PROVIDER) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.service_package.permission_denied'),
                ], 403);
            }

            $servicePackage = $this->model->findOrFail($id);
            
            if ($servicePackage->provider_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => __('static.service_package.not_owner'),
                ], 403);
            }
            $servicePackage->update(['status' => $status]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('static.service_package.status_updated'),
            ]);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
