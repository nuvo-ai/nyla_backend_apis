<?php

namespace App\Constants\Auth;

class OtpConstants
{
    const TYPE_EMAIL_VERIFICATION = 'email_verification';

    const TYPE_RESET_PASSWORD = 'reset_password';

    const TYPES = [
        self::TYPE_EMAIL_VERIFICATION,
        self::TYPE_RESET_PASSWORD,
    ];


    const TITLES = [
        self::TYPE_RESET_PASSWORD => "Reset Password",
        self::TYPE_EMAIL_VERIFICATION => "Verify Email",
    ];
}
