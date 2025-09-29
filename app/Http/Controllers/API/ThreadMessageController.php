<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Thread\StoreThreadMessageRequest;
use App\Http\Resources\ThreadMessageResource;
use App\Models\Thread;
use App\Services\Messaging\ThreadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ThreadMessageController extends Controller
{
    public function __construct(private readonly ThreadService $threadService)
    {
    }

    public function store(StoreThreadMessageRequest $request, Thread $thread): JsonResponse
    {
        Gate::authorize('sendMessage', $thread);

        $message = $this->threadService->sendMessage(
            $thread->loadMissing('participants'),
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => ThreadMessageResource::make($message),
        ], 201);
    }
}
