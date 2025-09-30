<?php

namespace App\Enums;

enum SecurityIncidentStatus: string
{
    case OPEN = 'open';
    case ACKNOWLEDGED = 'acknowledged';
    case CONTAINED = 'contained';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::ACKNOWLEDGED => 'Acknowledged',
            self::CONTAINED => 'Contained',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
