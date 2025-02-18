<?php

namespace App\Enums;

enum OtpType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';

    public static function values(): array
    {
        return [self::EMAIL->value, self::SMS->value];
    }
}
