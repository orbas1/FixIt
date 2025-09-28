<?php

namespace App\Repositories\Backend;

use Illuminate\Support\Arr;
use App\Enums\CategoryType;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class BlogRepository extends BaseRepository
{
    protected $category;

    protected $tag;

    public function model()
    {
        $this->category = new Category();
        $this->tag = new Tag();

        return Blog::class;
    }

    public function index()
    {
        return view('backend.blog.index');
    }

    public function create($attribute = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.blog.create', [
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
        ]);
    }

    private function getCategories()
    {

        return  $this->category->getCategoryDropdownOptions(CategoryType::BLOG);
    }

    private function getTags()
    {
        return $this->tag->where('type', 'blog')
            ->where('status', true)
            ->pluck('name', 'id');
    }

    public function show($id) {}

    public function store($request)
    {

        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            $blog = $this->model->create(
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'content' => $request->content,
                    'meta_title' => $request->meta_title,
                    'meta_description' => $request->meta_description,
                    // 'is_featured' => $request->is_featured,
                    'status' => $request->status,
                ]
            );

            if ($request->image) {
                $uploadedImages = $request->image;
                foreach ($uploadedImages as $uploadedImage) {
                    $blog->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    $blog->media;
                }
            }

            if ($request->web_image) {
                $uploadedImages = $request->web_image;
                foreach ($uploadedImages as $uploadedImage) {
                    $blog->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('web_image');
                    $blog->media;
                }
            }

            if ($request->hasFile('meta_image') && $request->file('meta_image')->isValid()) {
                $blog->addMedia($request->file('meta_image'))->withCustomProperties(['language' => $locale])->toMediaCollection('meta_image');
            }

            if (isset($request->categories)) {
                $blog->categories()->attach($request->categories);
                $blog->categories;
            }

            if (isset($request->tags)) {
                $blog->tags()->attach($request->tags);
                $blog->tags;
            }
            $blog->setTranslation('title', $locale, $request['title']);
            $blog->setTranslation('description', $locale, $request['description']);
            $blog->setTranslation('content', $locale, $request['content']);
            $blog->setTranslation('meta_title', $locale, $request['meta_title']);
            $blog->setTranslation('meta_description', $locale, $request['meta_description']);
            $blog->save();


            DB::commit();

            if ($request->has('save')) {
                return to_route('backend.blog.edit', $blog->id)->with('message', 'Blog Created Successfully.');
            }
            return to_route('backend.blog.index')->with('message', 'Blog Created Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $blog = $this->model->find($id);
        return view('backend.blog.edit', [
            'blog' => $blog,
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
            'default_categories' => $this->getDefaultCategories($blog),
            'default_tags' => $this->getDefaultTags($blog),
        ]);
    }

    public function getDefaultCategories($blog)
    {
        $categories = [];
        foreach ($blog->categories as $category) {
            $categories[] = $category->id;
        }
        $categories = array_map('strval', $categories);

        return $categories;
    }

    public function getDefaultTags($blog)
    {
        $tags = [];
        foreach ($blog->tags as $tag) {
            $tags[] = $tag->id;
        }
        $tags = array_map('strval', $tags);

        return $tags;
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $blog = $this->model->findOrFail($id);
            $blog->setTranslation('title', $locale, $request['title']);
            $blog->setTranslation('description', $locale, $request['description']);
            $blog->setTranslation('content', $locale, $request['content']);
            $blog->setTranslation('meta_title', $locale, $request['meta_title']);
            $blog->setTranslation('meta_description', $locale, $request['meta_description']);
            $data = Arr::except($request->all(), ['title', 'description', 'content', 'meta_title', 'meta_description']);
            $blog->update($data);

            if (isset($request->categories)) {
                $blog->categories()->sync($request->categories);
                $blog->categories;
            }

            if (isset($request->tags)) {
                $blog->tags()->sync($request->tags);
                $blog->tags;
            }

            if ($request['image']) {
                $uploadedImages = $request['image'];
                $images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingImages = $blog->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $uploadedImage) {
                    $blog->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    $blog->media;
                }
            }

            if ($request['web_image']) {
                $uploadedImages = $request['web_image'];
                $web_images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingWebImages = $blog->getMedia('web_image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingWebImages as $media) {
                    $media->delete();
                }
                foreach ($web_images as $uploadedImage) {
                    $blog->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('web_image');
                    $blog->media;
                }
            }
            if ($request['meta_image']) {
                $existingMetaImage = $blog->getMedia('web_thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingMetaImage as $media) {
                    $media->delete();
                }
                $blog->addMedia($request['meta_image'])->withCustomProperties(['language' => $locale])->toMediaCollection('meta_image');
            }

            DB::commit();

            if ($request->has('save')) {
                return to_route('backend.blog.edit', $blog->id)->with('message', 'Blog Edited Successfully.');
            }
            return to_route('backend.blog.index')->with('message', 'Blog Edited Successfully.');
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

            return redirect()->back()->with(['message' => 'Blog deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus($id, $status)
    {
        try {
            $blog = $this->model->findOrFail($id);
            $blog->update(['status' => $status]);

            return json_encode(['resp' => $blog]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function updateIsFeatured($id, $status)
    {
        try {
            $blog = $this->model->findOrFail($id);
            // $blog->update(['is_featured' => $status]);
            $blog->update();

            return json_encode(['resp' => $blog]);
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
