<?php

namespace App\Enums;

enum BulkMailerSmtpHealthStatus: string
{
    case Unknown = 'unknown';
    case Healthy = 'healthy';
    case Warning = 'warning';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Unknown',
            self::Healthy => 'Healthy',
            self::Warning => 'Warning',
            self::Failed => 'Failed',
        };
    }
}