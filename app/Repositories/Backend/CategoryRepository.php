<?php

namespace App\Repositories\Backend;

use App\Exports\CategoryExport;
use App\Imports\CategoryImport;
use App\Models\Zone;
use Exception;
use Illuminate\Support\Arr;
use App\Http\Traits\HandlesMedia;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class CategoryRepository extends BaseRepository
{
    use HandlesMedia;
    public $zones;

    public function model()
    {
        return Category::class;
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
                    'commission' => $request->commission,
                    'status' => $request->status,
                    'category_type' => $request->category_type,
                    'is_featured' => $request->is_featured,
                    'created_by' => Auth::user()->id,
                ]
            );

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $category->addMediaFromRequest('image')->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }

            $category->setTranslation('title', $locale, $request['title']);
            $category->setTranslation('description', $locale, $request['description']);
            $category->save();

            if (isset($request->zones)) {
                if (in_array('all', $request->zones)) {
                    $allZones = Zone::pluck('id')->toArray();
                    $request->merge(['zones' => $allZones]);
                }
                $category->zones()->attach($request->zones);
                $category->zones;
            }

            DB::commit();
            return redirect()->route('backend.category.index')->with('message', __('static.blog_category.blog_category_store'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $category = $this->model->findOrFail($id);

            $category->setTranslation('title', $locale, $request['title']);
            $category->setTranslation('description', $locale, $request['description']);

            $data = Arr::except($request->all(), ['title', 'description', 'locale']);
            $category->update($data);

            if ($request->file('image') && $request->file('image')->isValid()) {
                $images = $request->file('image');
                $images = is_array($images) ? $images : [$images];
                $existingMedia = $category->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingMedia as $media) {
                    $media->delete();
                }

                foreach ($images as $image) {
                    if ($image->isValid()) {
                        $category->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                }
            }

            if (isset($request->zones)) {
                if (in_array('all', $request->zones)) {
                    $allZones = Zone::pluck('id')->toArray();
                    $request->merge(['zones' => $allZones]);
                }
                $category->zones()->sync($request->zones);
                $category->zones;
            }

            DB::commit();
            return redirect()->route('backend.category.edit', ['category' => $category->id, 'locale' => $locale])->with('message', __('static.categories.updated'));

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
            return redirect()->route('backend.category.index');
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

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new CategoryExport, 'categories.csv');
            }
            return Excel::download(new CategoryExport, 'categories.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function import($request)
    {
        try {
            $activeTab = $request->input('active_tab');

            $tempFile = null;

            if ($activeTab === 'direct-link') {

                $googleSheetUrl = $request->input('google_sheet_url');

                if (!$googleSheetUrl) {
                    throw new Exception(__('static.import.no_url_provided'));
                }

                if (!filter_var($googleSheetUrl, FILTER_VALIDATE_URL)) {
                    throw new Exception(__('static.import.invalid_url'));
                }

                $parsedUrl = parse_url($googleSheetUrl);
                preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $parsedUrl['path'], $matches);
                $sheetId = $matches[1] ?? null;
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                $gid = $queryParams['gid'] ?? 0;

                if (!$sheetId) {
                    throw new Exception(__('static.import.invalid_sheet_id'));
                }

                $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

                $response = Http::get($csvUrl);

                if (!$response->ok()) {
                    throw new Exception(__('static.import.failed_to_fetch_csv'));
                }

                $tempFile = tempnam(sys_get_temp_dir(), 'google_sheet_') . '.csv';

                file_put_contents($tempFile, $response->body());
            } elseif ($activeTab === 'local-file') {
                $file = $request->file('fileImport');

                if (!$file) {
                    throw new Exception(__('static.import.no_file_uploaded'));
                }

                if ($file->getClientOriginalExtension() != 'csv') {
                    throw new Exception(__('static.import.csv_file_allow'));
                }

                $tempFile = $file->getPathname();
            } else {
                throw new Exception(__('static.import.no_valid_input'));
            }

            Excel::import(new CategoryImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return redirect()->route('backend.category.index')->with('success', __('static.import.csv_file_import'));

        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
