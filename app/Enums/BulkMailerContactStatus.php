<?php

namespace App\Enums;

enum BulkMailerContactStatus: string
{
    case Active = 'active';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
    case Suppressed = 'suppressed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Unsubscribed => 'Unsubscribed',
            self::Bounced => 'Bounced',
            self::Suppressed => 'Suppressed',
        };
    }
}