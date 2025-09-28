<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Frontend\CommentRepository;
use App\Http\Requests\Frontend\CreateCommentRequest;
class CommentController extends Controller
{
    public $repository;

    public function __construct(CommentRepository $repository)
    {
        $this->repository = $repository;
    }
    public function store(CreateCommentRequest $request, $blogId)
    {
        return $this->repository->store($request, $blogId);
    }

    public function show($blogId)
    {
        $blog = $this->repository->show($blogId);
        return view('frontend.blog.details', compact('blog'));
    }
}
