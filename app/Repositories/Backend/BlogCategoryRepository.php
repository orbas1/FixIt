<?php

namespace App\Repositories\Backend;

use Exception;
use Illuminate\Support\Arr;
use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class BlogCategoryRepository extends BaseRepository
{
    public function model()
    {
        return Category::class;
    }

    public function index()
    {
        $categories = $this->model
            ->withOutParent()
            ->where('category_type', CategoryType::BLOG);

        if (!empty(request()->search)) {
            $categories->where('title','LIKE','%'.request()->search.'%');
        }

        $allParent = $this->model->whereNull('parent_id')
            ->where('category_type', CategoryType::BLOG)
            ->get()->pluck('title', 'id');

        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);

        return view('backend.blog-category.index', [
            'categories' => $categories->get(),
            'allparent' => $allParent,
        ]);
    }

    public function show($id)
    {
        try {

            return $this->model->with('permissions')->findOrFail($id);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function getHierarchy()
    {
        return collect($this->model->getHierarchy())->pluck('title', 'id');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            $category = $this->model->create(
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'parent_id' => $request->parent_id,
                    'status' => $request->status,
                    'category_type' => $request->category_type,
                    'created_by' => Auth::user()->id,
                ]
            );

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $category->addMediaFromRequest('image')->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }

            $category->setTranslation('title', $locale, $request['title']);
            $category->setTranslation('description', $locale, $request['description']);
            $category->save();
            DB::commit();
            return redirect()->route('backend.blog-category.index')->with('message', __('static.blog_category.blog_category_store'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($category, $id)
    {
        $cat = $this->model->find($id);

        return view('backend.blog-category.edit', [
            'cat' => $cat,
            'categories' => $this->model->withOutParent()->where('category_type', CategoryType::BLOG)->get(),
            'allparent' => $this->model->whereNull('parent_id')->where('category_type', CategoryType::BLOG)->get()->pluck('title', 'id'),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $blog_category = $this->model->findOrFail($id);
            $blog_category->setTranslation('title', $locale, $request['title']);
            $blog_category->setTranslation('description', $locale, $request['description']);

            $blog_category->update([
                'parent_id' => $request->parent_id,
                'status' => $request->status,
                'category_type' => $request->category_type,
                'created_by' => Auth::user()->id,
            ]);

            if ($request->file('image') && $request->file('image')->isValid()) {
                $images = $request->file('image');
                $images = is_array($images) ? $images : [$images];
                $existingMedia = $blog_category->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingMedia as $media) {
                    $media->delete();
                }

                foreach ($images as $image) {
                    if ($image->isValid()) {
                        $blog_category->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                }
            }
            DB::commit();
            return redirect()->route('backend.blog-category.index')->with('message', __('static.blog_category.blog_category_updated'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $category = $this->model->findOrFail($id);
            $category->destroy($id);

            DB::commit();

            return redirect()->route('backend.blog-category.index');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function changeIsFeatured($isFeatured, $subjectId)
    {
        DB::beginTransaction();
        try {
            $category = $this->model->findOrFail($subjectId);
            $category->is_featured = $isFeatured;
            $category->save();

            DB::commit();

            return redirect()->route('backend.category.index')->with('message', 'Is Featured Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function changeStatus($statusVal, $subjectId)
    {
        DB::beginTransaction();
        try {
            $category = $this->model->findOrFail($subjectId);
            $category->status = $statusVal;
            $category->save();

            DB::commit();

            return redirect()->route('backend.category.index')->with('message', 'Is Featured Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
}
