<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Models\Language;
use Exception;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class LanguageRepository extends BaseRepository
{
    public function model()
    {
        return Language::class;
    }

    protected $fieldSearchable = [
        'key' => 'like',
        'value' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (\Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function index($request)
    {
        try {

            $languages = $this->model;

            return $languages = $languages->latest('created_at')->paginate($request->paginate ?? $languages->count());

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($banner)
    {
        //
    }

    public function store($request)
    {
        //
    }
}
