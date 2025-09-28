<?php

namespace App\Repositories\Backend;

use App\Enums\BannerTypeEnum;
use App\Models\Banner;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class BannerRepository extends BaseRepository
{
    public function model()
    {
        return Banner::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            $banner = $this->model->create([
                'title' => $request->title,
                'type' => $request->type,
                'related_id' => $request->related_id,
                'status' => $request->status,
                'is_offer' => $request->is_offer,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $banner->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
            }

            if ($request->zones) {
                $banner->zones()->attach($request->zones);
                $banner->zones;
            }

            $banner->setTranslation('title', $locale, $request['title']);
            $banner->save();
            DB::commit();

            return redirect()->route('backend.banner.index')->with('message', 'Banner Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $banner = $this->model->findOrFail($id);
            $banner->update(['status' => $status]);

            return json_encode(['resp' => $banner]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $banner = $this->model->findOrFail($id);
        return view('backend.banner.edit', ['bannerType' => BannerTypeEnum::BANNERTYPE, 'banner' => $banner]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $banner = $this->model->findOrFail($id);
            $banner->setTranslation('title', $locale, $request->title);

            $banner->update([
                'type' => $request->type,
                'related_id' => $request->related_id,
                'status' => $request->status,
                'is_offer' => $request->is_offer,
            ]);

            if ($request->hasFile('images')) {
                $newImages = $request->file('images');
                $newImages = is_array($newImages) ? $newImages : [$newImages];
                $existingMedia = $banner->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingMedia as $media) {
                    $media->delete();
                }

                foreach ($newImages as $image) {
                    $banner->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
            }

            if (isset($request->zones)){
                $banner->zones()->sync($request->zones);
                $banner->zones;
            }

            DB::commit();
            return redirect()->route('backend.banner.index')->with('message', 'Banner Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $banner = $this->model->findOrFail($id);
            $banner->destroy($id);

            return redirect()->route('backend.banner.index')->with('message', 'Banner Deleted Successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
