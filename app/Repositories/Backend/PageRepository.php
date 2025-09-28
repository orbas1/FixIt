<?php

namespace App\Repositories\Backend;

use App\Models\Page;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class PageRepository extends BaseRepository
{
    public function model()
    {
        return Page::class;
    }

    public function index()
    {
        return view('backend.page.index');
    }

    public function create($attribute = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.page.create');
    }

    public function show($id) {}

    public function store($request)
    {

        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $page = $this->model->create(
                [
                    'title' => $request->title,
                    'app_type' => $request->app_type,
                    'content' => $request->content,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    'created_by_id' => Auth::user()->id,
                    'status' => $request->status,
                ]
            );

            if ($request->hasFile('meta_image') && $request->file('meta_image')->isValid()) {
                $page->addMediaFromRequest('meta_image')->withCustomProperties(['language' => $locale])->toMediaCollection('meta_image');
            }

            if ($request->hasFile('app_icon') && $request->file('app_icon')->isValid()) {
                $page->addMediaFromRequest('app_icon')->withCustomProperties(['language' => $locale])->toMediaCollection('app_icon');
            }

            $page->setTranslation('title', $locale, $request['title']);
            $page->setTranslation('content', $locale, $request['content']);
            $page->setTranslation('meta_title', $locale, $request['meta_title']);
            $page->setTranslation('meta_description', $locale, $request['meta_description']);
            $page->save();

            DB::commit();
            return redirect()->route('backend.page.index')->with('message', 'Page Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $page = $this->model->find($id);

        return view('backend.page.edit', [
            'page' => $page,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $page = $this->model->findOrFail($id);
            $page->setTranslation('title', $locale, $request['title']);
            $page->setTranslation('content', $locale, $request['content']);
            $page->setTranslation('meta_title', $locale, $request['meta_title']);
            $page->setTranslation('meta_description', $locale, $request['meta_description']);
            $page->update([
                'app_type' => $request->app_type,
                'created_by_id' => Auth::user()->id,
                'status' => $request->status,
            ]);

            if ($request->hasFile('meta_image') && $request->file('meta_image')->isValid()) {
                $uploadedImages = $request->file('meta_image');
                $images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingImages = $page->getMedia('meta_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $uploadedImage) {
                    $page->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('meta_image');
                    $page->media;
                }
            }

            if ($request->hasFile('app_icon') && $request->file('app_icon')->isValid()) {
                $uploadedImages = $request->file('app_icon');
                $images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingImages = $page->getMedia('app_icon')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $uploadedImage) {
                    $page->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('app_icon');
                    $page->media;
                }
            }

            DB::commit();
            return redirect()->route('backend.page.index')->with('success', 'Page Updated Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $page = $this->model->findOrFail($id);
            $page->destroy($id);

            DB::commit();

            return redirect()->back()->with(['message' => 'Page deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();

            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function status($id, $status)
    {
        try {

            $page = $this->model->findOrFail($id);
            $page->update(['status' => $status]);

            return json_encode(['resp' => $page]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereNot('system_reserve', true)->whereIn('id', $ids)->delete();

            return back()->with('message', 'Roles Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }
}
