<?php

namespace App\Enums;

enum BulkMailerTagType: string
{
    case Smtp = 'smtp';
    case List = 'list';
    case Template = 'template';
    case Campaign = 'campaign';

    public function label(): string
    {
        return match ($this) {
            self::Smtp => 'SMTP',
            self::List => 'List',
            self::Template => 'Template',
            self::Campaign => 'Campaign',
        };
    }
}