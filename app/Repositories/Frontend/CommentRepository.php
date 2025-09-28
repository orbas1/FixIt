<?php

namespace App\Repositories\Frontend;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Prettus\Repository\Eloquent\BaseRepository;

class CommentRepository extends BaseRepository
{
    public function model()
    {
        return Comment::class;
    }

    public function store($request, $blogId)
    {
        $this->model::create([
            'message' => $request->message,
            'user_id' => Auth::id(),
            'blog_id' => $blogId,
           'parent_id' => $request->parent_id,
        ]);

        $message = $request->parent_id ? 'Reply Successfully!' : 'Comment Successfully!';
        return redirect()->back()->with('message', $message);
    }

    public function show($blogId)
    {
        $blog = Blog::with(['comments.user', 'comments.replies.user'])->findOrFail($blogId);
        return $blog;
    }
}