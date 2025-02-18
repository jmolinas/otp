<?php

namespace App\Exceptions;

use Exception;

class OtpException extends Exception
{
    public static function expiredOrInvalid()
    {
        return new self('Invalid, expired, or already used OTP.', 422);
    }
}
