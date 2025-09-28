<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Resources\CountriesResource;
use App\Models\Country;
use Exception;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CountryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
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
        return Country::class;
    }

    public function show($id)
    {
        try {

            $item =  $this->model->with('state')->findOrFail($id);
            return response()->json(['success' => true, 'data' => new CountriesResource($item)]);


        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
