<?php

namespace App\Repositories\API;

use App\Enums\CategoryType;
use App\Exceptions\ExceptionHandler;
use App\Models\Category;
use Exception;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CategoryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
        'hasSubCategories.title' => 'like',
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
        return Category::class;
    }

    public function show($id)
    {
        try {
            return $this->model->where('id', $id)->with('hasSubCategories')->get();
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCategoryCommission($request)
    {
        try {
            $categories = $this->model->select('id', 'title', 'commission', 'created_at')
                ->where(['category_type' => CategoryType::SERVICE, 'status' => true])
                ->whereNull('parent_id');

            if ($request->search) {
                $categories->where('title', 'like', '%'.$request->search.'%');
            }

            return $categories->latest('created_at')->paginate($request->paginate ?? $categories->count());
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
