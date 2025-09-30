<?php

namespace App\Jobs\Security;

use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDataDeletionRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $userId, private readonly string $token)
    {
    }

    public function handle(): void
    {
        $user = User::withTrashed()->findOrFail($this->userId);

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'name' => 'Deleted User',
                'email' => sprintf('deleted+%s@example.com', $user->id),
                'phone' => null,
                'description' => null,
            ])->save();

            ThreadMessage::query()
                ->where('user_id', $user->id)
                ->update([
                    'body' => '[removed by privacy request]',
                ]);

            Log::notice('User data anonymized for privacy request', [
                'user_id' => $user->id,
                'token' => $this->token,
            ]);
        });
    }
}
