<?php

namespace App\Repositories\API;

use App\Http\Resources\BlogDetailResource;
use Exception;
use App\Models\Blog;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class BlogRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
    ];

    public function model()
    {
        return Blog::class;
    }

    // function index($request)
    // {
    //     try {
    //         $blog = $this->model->where('status', true);
    //         // $paginate = ;
    //         $blogPosts = $blog->latest('created_at')->paginate($request->input('paginate', $blog->count()));

    //         return response()->json(['success' => true, 'data' => $blogPosts]);
    //     } catch (Exception $e) {

    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function show($id)
    {
        try {
            $blog = Blog::query()
            ->with(['categories' , 'tags'])
            ->where('id' , $id)
            ->first();

            return response()->json([
                'success' => true,
                'data' =>  new BlogDetailResource($blog),
            ]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
