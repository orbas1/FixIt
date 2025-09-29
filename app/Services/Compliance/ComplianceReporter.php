<?php

namespace App\Services\Compliance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use function activity;

class ComplianceReporter
{
    public function report(string $action, Model $subject, ?Model $actor = null, array $properties = []): void
    {
        $properties = Arr::whereNotNull($properties);

        try {
            $logger = activity('compliance')
                ->event($action)
                ->performedOn($subject)
                ->withProperties($properties);

            if ($actor) {
                $logger->causedBy($actor);
            }

            $logger->log($action);
        } catch (\Throwable $exception) {
            Log::channel(config('escrow.logging_channel', 'stack'))->warning('Compliance log failed', [
                'action' => $action,
                'subject_type' => get_class($subject),
                'subject_id' => $subject->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
