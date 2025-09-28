<?php

namespace App\Repositories\API;

use App\Http\Resources\AdvertisementResource;
use App\Models\Service;
use Exception;
use DateTime;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class AdvertisementRepository extends BaseRepository
{

    public function model()
    {
        return Advertisement::class;
    }

    public function show($id)
    {
        try {
            $item = $this->model->with('media')->findOrFail($id);

            return response()->json(['success' => true, 'data' => new AdvertisementResource($item)]);
        } catch (Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
          $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');

          $advertisement = $this->model?->create([
            'provider_id' => Helpers::getCurrentUserId(),
            'type' => $request?->type,
            'screen' => $request?->screen,
            'status' => 'pending',
            'start_date' => $request?->start_date,
            'end_date' => $request?->end_date,
            'zone' => $request?->zone_id,
            'video_link' => $request?->video_link,
            'banner_type' => $request?->banner_type,
            'price' => $request->price
          ]);

          if ($request->images) {
            $images = $request->file('images');

            foreach ($images as $image) {
                $advertisement->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }
            $advertisement->media;
          }

          if (isset($request->service_ids)) {
            $advertisement->services()->attach($request->service_ids);
            $advertisement->services;
            if($request?->screen === 'category') {
                Service::whereIn('id', (array) $request->service_id)->update(['is_advertised' => true]);
            }
          }

          DB::commit();
          return response()->json([
              'message' => __('static.advertisement.created_successfully'),
              'advertisement' => new AdvertisementResource($advertisement)
          ]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }


    public function update($request, $id)
    {

        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $advertisement = $this->model->findOrFail($id);

            if($advertisement?->status === 'pending') {

                if($request?->type == 'service' ) {
                    $request->video_link = null;
                    $advertisement->banner_type = null;
                    $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                        return $media->getCustomProperty('language') === $locale;
                    });

                    foreach ($existingMedia as $media) {
                        $media->delete();
                    }
                }

                if($request?->type == 'banner') {
                    if($request?->banner_type === 'video') {
                        $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                            return $media->getCustomProperty('language') === $locale;
                        });
                        foreach ($existingMedia as $media) {
                            $media->delete();
                        }
                    }

                    if($request?->banner_type === 'image') {
                        $request->video_link = null;
                    }
                    $existingServices = $advertisement->services;

                    foreach ($existingServices as $service) {
                        $service->delete();
                    }
                }

                $advertisement->update([
                    'provider_id' => Helpers::getCurrentUserId(),
                    'type' => $request?->type,
                    'screen' => $request?->screen,
                    'start_date' => $request?->start_date,
                    'end_date' => $request?->end_date,
                    'zone' => $request?->zone_id,
                    'video_link' => $request?->video_link,
                    'banner_type' => $request?->banner_type,
                ]);

                if ($request->hasFile('images')) {
                    $newImages = $request->file('images');
                    $newImages = is_array($newImages) ? $newImages : [$newImages];
                    $existingMedia = $advertisement->getMedia('image')->filter(function ($media) use ($locale) {
                        return $media->getCustomProperty('language') === $locale;
                    });

                    foreach ($existingMedia as $media) {
                        $media->delete();
                    }

                    foreach ($newImages as $image) {
                        $advertisement->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                }


                if (isset($request->service_id)){
                    $advertisement->services()->sync($request->service_id);
                    $advertisement->services;
                    if($request?->screen === 'category') {
                        Service::whereIn('id', (array) $request->service_id)->update(['is_advertised' => true]);
                    }

                }

                DB::commit();
                return response()->json([
                    'message' => __('static.advertisement.updated_successfully'),
                    'success' => true
                ]);
            }

            throw new Exception(__('static.advertisement.only_update_while_pending'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

}
