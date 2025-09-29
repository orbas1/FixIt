<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Thread\MarkThreadReadRequest;
use App\Models\Thread;
use App\Services\Messaging\ThreadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ThreadReadController extends Controller
{
    public function __construct(private readonly ThreadService $threadService)
    {
    }

    public function store(MarkThreadReadRequest $request, Thread $thread): JsonResponse
    {
        Gate::authorize('view', $thread);

        $this->threadService->markRead($thread->loadMissing('participants'), $request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'read_at' => $thread->participants
                    ->firstWhere('user_id', $request->user()->id)?->last_read_at?->toIso8601String(),
            ],
        ]);
    }
}
