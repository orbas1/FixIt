<?php

namespace App\Services\Messaging;

use App\Events\ThreadMessageCreated;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\ThreadParticipant;
use App\Models\User;
use App\Notifications\ThreadMessageNotification;
use App\Services\Security\ContentGuardService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ThreadService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly ContentGuardService $guard,
    )
    {
    }

    public function createThread(array $payload, User $creator): Thread
    {
        return $this->db->transaction(function () use ($payload, $creator) {
            $participantIds = collect($payload['participants'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique();

            if ($participantIds->isEmpty()) {
                throw new \InvalidArgumentException('A thread requires at least one other participant.');
            }

            $thread = Thread::query()->create([
                'public_id' => Arr::get($payload, 'public_id') ?? (string) Str::ulid(),
                'service_request_id' => Arr::get($payload, 'service_request_id'),
                'booking_id' => Arr::get($payload, 'booking_id'),
                'type' => Arr::get($payload, 'type', 'buyer_provider'),
                'status' => Arr::get($payload, 'status', 'open'),
                'subject' => Arr::get($payload, 'subject'),
                'opened_by_id' => $creator->id,
            ]);

            $participants = $participantIds
                ->push($creator->id)
                ->unique()
                ->map(function (int $id) use ($thread, $creator, $payload): ThreadParticipant {
                    $role = Arr::get($payload, 'roles.' . $id);

                    if (! $role) {
                        $role = $id === $creator->id ? 'consumer' : 'provider';
                    }

                    return ThreadParticipant::query()->create([
                        'thread_id' => $thread->id,
                        'user_id' => $id,
                        'role' => $role,
                        'is_active' => true,
                        'last_read_at' => $id === $creator->id ? now() : null,
                        'notification_preferences' => Arr::get($payload, 'notification_preferences.' . $id),
                    ]);
                });

            $thread->setRelation('participants', new Collection($participants));

            return $thread;
        });
    }

    public function sendMessage(Thread $thread, User $author, array $payload): ThreadMessage
    {
        return $this->db->transaction(function () use ($thread, $author, $payload) {
            $attachments = $this->prepareAttachments($payload['attachments'] ?? []);

            if (! ($payload['body'] ?? null) && empty($attachments)) {
                throw new \InvalidArgumentException('A message requires text or attachments.');
            }

            $body = $payload['body'] ?? null;
            $moderationResult = null;

            if ($body !== null) {
                $moderationResult = $this->guard->inspect($body, [
                    'locale' => $author->preferred_locale ?? app()->getLocale(),
                ]);

                if ($moderationResult->isBlocked()) {
                    throw new \DomainException('Message rejected by safety filters.');
                }

                $body = $moderationResult->sanitizedText();
            }

            $message = ThreadMessage::query()->create([
                'thread_id' => $thread->id,
                'author_id' => $author->id,
                'body' => $body,
                'attachments' => $attachments ?: null,
                'meta' => Arr::except($payload, ['body', 'attachments']),
                'is_system' => (bool) ($payload['is_system'] ?? false),
                'delivered_at' => now(),
            ]);

            $thread->forceFill([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ])->save();

            /** @var ThreadParticipant|null $participant */
            $participant = $thread->participants
                ->firstWhere('user_id', $author->id);

            if ($participant) {
                $participant->markRead();
            }

            $this->dispatchNotifications($thread, $message);

            if ($moderationResult && $moderationResult->shouldEscalate()) {
                $this->guard->flag($message, $moderationResult, [
                    'category' => 'thread_message',
                    'actor_id' => $author->id,
                    'thread_id' => $thread->id,
                ]);
            }

            ThreadMessageCreated::dispatch($message->fresh(['author', 'thread']));

            return $message->load(['author']);
        });
    }

    public function markRead(Thread $thread, User $user): void
    {
        /** @var ThreadParticipant|null $participant */
        $participant = $thread->participants()->where('user_id', $user->id)->first();

        if ($participant) {
            $participant->markRead();
        }
    }

    private function prepareAttachments(array $attachmentPayload): array
    {
        if (empty($attachmentPayload)) {
            return [];
        }

        $mediaIds = collect($attachmentPayload)
            ->pluck('media_id')
            ->merge(collect($attachmentPayload)->filter(fn ($item) => is_numeric($item))->values())
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($mediaIds->isEmpty()) {
            return [];
        }

        /** @var Collection<int, Media> $media */
        $media = Media::query()->whereIn('id', $mediaIds)->get();

        return $media->map(function (Media $item) {
            $scanStatus = $item->getCustomProperty('scan_status');
            if ($scanStatus && $scanStatus !== 'clean') {
                throw new \RuntimeException('Attachment failed security scan.');
            }

            return [
                'media_id' => $item->id,
                'name' => $item->file_name,
                'size' => $item->size,
                'mime_type' => $item->mime_type,
                'url' => $item->getFullUrl(),
                'thumbnail_url' => $item->hasGeneratedConversion('thumb') ? $item->getFullUrl('thumb') : null,
            ];
        })->values()->all();
    }

    private function dispatchNotifications(Thread $thread, ThreadMessage $message): void
    {
        $message->loadMissing('thread', 'author');

        $participants = $thread->participants()
            ->with('user')
            ->where('user_id', '!=', $message->author_id)
            ->get();

        $notifiables = $participants
            ->reject(fn (ThreadParticipant $participant) => $participant->isMuted())
            ->map(fn (ThreadParticipant $participant) => $participant->user)
            ->filter();

        if ($notifiables->isEmpty()) {
            return;
        }

        try {
            Notification::send($notifiables, new ThreadMessageNotification($message));
        } catch (\Throwable $exception) {
            Log::warning('Thread notification failure', [
                'thread_id' => $thread->id,
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
