<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $model;

    public function __construct(Page $blog)
    {
        $this->model = $blog;
    }

    public function index(Request $request)
    {
        $pages = $this->model->where('status', true);
        $paginate = $request->input('paginate', $pages->count());

        if ($request->has('provider')) {
            $pages = $pages->where('app_type', 'provider');
        } else {
            $pages = $pages->where('app_type', '!=', 'provider')->orwhere('app_type', null);
        }

        $items = $pages->select('id' , 'title' , 'content')?->latest('created_at')->paginate($paginate);
        return response()->json([
            'success' => true,
            'data' => PageResource::collection($items)
        ]);
    }
}
