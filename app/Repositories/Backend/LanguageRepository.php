<?php

namespace App\Repositories\Backend;

use App\Helpers\Helpers;
use App\Models\SystemLang;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prettus\Repository\Eloquent\BaseRepository;

class LanguageRepository extends BaseRepository
{
    protected $settings;
    public function model()
    {
        $this->settings = Helpers::getSettings();
        return SystemLang::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $this->model->create([
                'name' => $request->name,
                'locale' => $request->locale,
                'flag' => $request->flag,
                'app_locale' => $request->app_locale,
                'is_rtl' => $request->is_rtl,
                'status' => $request->status,
            ]);

            DB::commit();

            return to_route('backend.systemLang.index')->with('message', __('static.language.create_successfully'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $systemLang = $this->model->findOrFail($id);
            $systemLang->update($request);

            DB::commit();

            return redirect()->route('backend.systemLang.index')->with('success', __('static.language.update_successfully'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $systemLang = $this->model->findOrFail($id);
            $defaultLanguageId = $this->settings['general']['default_language_id']; 
            if ($defaultLanguageId == $id) {
                return back()->with('error', __('static.language.default_language_cannot_be_deleted'));
            }
            $activeLanguagesCount = $this->model
            ->whereNull('deleted_at')
            ->count();
            if ($activeLanguagesCount == 1) {
                return back()->with('error', __('static.language.cannot_delete_only_language'));
            }
            $systemLang->delete($id);

            return redirect()->route('backend.systemLang.index')->with('success', __('static.language.delete_successfully'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {
            $systemLang = $this->model->findOrFail($id);
            $defaultLanguageId = $this->settings['general']['default_language_id'];
            if ($defaultLanguageId == $id && $status == false) {
                return response()->json(['error' => __('static.language.default_language_cannot_be_disabled'),], 400);
            }

            $LanguagesCount = $this->model->whereNull('deleted_at')->where('id', '!=', $id)->count();
            if ($LanguagesCount == 0 && $status == false) {
                return response()->json(['error' => __('static.language.cannot_disable_only_language')], 400);
            }

            $systemLang->update(['status' => $status]);
            $systemLang->fresh();

            return response()->json(['success' => true, 'status' => $systemLang->status]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function rtl($id, $status)
    {
        try {

            $systemLang = $this->model->findOrFail($id);
            $systemLang->update(['is_rtl' => $status]);

            return json_encode(['resp' => $systemLang]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        try {

            return $this->model->whereIn('id', $ids)->delete();
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function createPaginate($translations, $request)
    {
        $perPage = config('app.paginate') ?? 15;
        $currentPage = $request->input('page', 1);
        $items = array_slice($translations, ($currentPage - 1) * $perPage, $perPage);
        $translations = new LengthAwarePaginator($items, count($translations), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return $translations;
    }

    public function translate($request)
    {
        try {

            $locale = $request->locale;
            $file = $request->file;
            $dir = resource_path("lang/{$locale}");
            $allFiles = [];
            if (File::isDirectory($dir)) {
                foreach (File::allFiles($dir) as $dirFile) {
                    $filename = pathinfo($dirFile, PATHINFO_FILENAME);
                    $allFiles[] = $filename;
                }

                if (! $file) {
                    $file = head($allFiles);
                }

                $languageFilePath = "{$dir}/{$file}.php";
                if (file_exists($languageFilePath)) {
                    $translations = include $languageFilePath;
                    $translations = $this->createPaginate($translations, $request);

                    return view('backend.language.translate', [
                        'translations' => $translations,
                        'allFiles' => $allFiles,
                        'file' => $file,
                    ]);
                }
            }

            return redirect()->back()->with('error', __('static.language.file_not_found'));

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function translate_update($request, $locale)
    {
        $translations = $request->except('_token');
        $file = $request->file;
        $filePath = resource_path("lang/{$locale}/{$file}.php");
        if (file_exists($filePath)) {
            $existingTranslations = include $filePath;
            foreach ($translations as $key => $value) {
                $this->updateTranslation($existingTranslations, $key, $value);
            }

            $content = "<?php\n\nreturn ".var_export($existingTranslations, true).";\n";
            File::put($filePath, $content);
            Artisan::call('cache:clear');

            return to_route('backend.systemLang.index')->with('message', __('static.language.translate_file_update_successfully'));
        }

        return redirect()->back()->with('error', __('static.language.file_not_found'));
    }

    public function updateTranslation(&$translations, $key, $value)
    {
        $keys = explode('__', $key);
        $current = &$translations;
        foreach ($keys as $nestedKey) {
            if (! isset($current[$nestedKey])) {
                $current[$nestedKey] = [];
            }
            $current = &$current[$nestedKey];
        }
        $current = $value;
    }
}
