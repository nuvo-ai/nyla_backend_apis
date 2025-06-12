<?php

namespace App\Constants\General;

class ApiConstants
{
    const SERVER_ERR_CODE = 500;

    const BAD_REQ_ERR_CODE = 400;

    const AUTH_ERR_CODE = 401;

    const FORBIDDEN_ERR_CODE = 403;

    const NOT_FOUND_ERR_CODE = 404;

    const VALIDATION_ERR_CODE = 422;

    const GOOD_REQ_CODE = 200;

    const AUTH_TOKEN_EXP = 60;

    const OTP_DEFAULT_LENGTH = 7;

    const MAX_PROFILE_PIC_SIZE = 2048;

    const MALE = 'Male';

    const FEMALE = 'Female';

    const OTHERS = 'Others';

    const PAYMENT_REFERENCE_PREFIX = 'WEALTH-PAY-';

    const GENDERS = [
        self::MALE,
        self::FEMALE,
        self::OTHERS,
    ];

    const PAYMENT_TYPES = [
        'one time',
        'installment',
    ];

    const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'successful' => 'Successful',
        'failed' => 'Failed',
    ];

    const GG_PROVIDER = 'google';

    const FB_PROVIDER = 'facebook';

    const PAGINATION_SIZE_WEB = 50;

    const PAGINATION_SIZE_API = 20;
}
