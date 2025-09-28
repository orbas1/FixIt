<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Resources\StateResource;
use App\Models\State;
use Exception;
use Illuminate\Support\Facades\Validator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class StateRepository extends BaseRepository
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
        return State::class;
    }

    public function store($request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'unique:states,name'],
                'country_id' => ['required',  'exists:countries,id'],
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $state = $this->model->where('name', $request->name)
                ?->where('country_id', $request->country_id)
                ?->first();

            if (! $state) {
                $state = $this->model->create([
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                ]);
            }

            return [
                'data' => $state,
                'success' => true,
            ];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {

            $item =  $this->model->findOrFail($id);
            return response()->json(['success' => true, 'data' => new StateResource($item)]);


        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
