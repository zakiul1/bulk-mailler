<?php

namespace App\Enums;

enum BulkMailerVerificationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Risky = 'risky';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Valid => 'Valid',
            self::Invalid => 'Invalid',
            self::Risky => 'Risky',
            self::Unknown => 'Unknown',
        };
    }
}