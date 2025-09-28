<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\SystemLang;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class SystemLangRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (\Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        return SystemLang::class;
    }

    public function getTranslate($request)
    {
        if ($request->lang) {
            $locale = $this->model->where('locale', $request?->lang)?->value('locale');
        } else {
            $settings = Helpers::getSettings();
            $locale = $settings['general']['default_language']['locale'];
        }

        $filePath = resource_path("lang/{$locale}/app.php");
        if (file_exists($filePath)) {
            $file = include $filePath;

            return response()->json($file);
        }

        return response()->json([
            'success' => false,
            'message' => __('static.language.file_not_found'),
        ], 404);
    }

    public function getProviderTranslate($request)
    {
        if ($request->lang) {
            $locale = $this->model->where('locale', $request?->lang)?->value('locale');
        } else {
            $settings = Helpers::getSettings();
            $locale = $settings['general']['default_language']['locale'];
        }

        $filePath = resource_path("lang/{$locale}/provider.php");
        if (file_exists($filePath)) {
            $file = include $filePath;

            return response()->json($file);
        }

        return response()->json([
            'success' => false,
            'message' => __('static.language.file_not_found'),
        ], 404);
    }
}
