<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Thread\CreateThreadRequest;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Services\Messaging\ThreadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ThreadController extends Controller
{
    public function __construct(private readonly ThreadService $threadService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $threads = Thread::query()
            ->with([
                'participants.user:id,name,profile_photo_path,fcm_token',
                'latestMessage.author:id,name,profile_photo_path',
            ])
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => ThreadResource::collection($threads),
            'meta' => [
                'current_page' => $threads->currentPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    public function store(CreateThreadRequest $request): JsonResponse
    {
        $thread = $this->threadService->createThread($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'data' => ThreadResource::make($thread->load(['participants.user'])),
        ], 201);
    }

    public function show(Thread $thread, Request $request): JsonResponse
    {
        Gate::authorize('view', $thread);

        $thread->load([
            'participants.user:id,name,profile_photo_path,fcm_token',
            'messages' => function ($query) {
                $query->with('author:id,name,profile_photo_path')->orderBy('created_at');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => ThreadResource::make($thread),
        ]);
    }
}
