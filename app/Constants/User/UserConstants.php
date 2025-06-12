<?php

namespace App\Constants\User;

use App\Constants\General\StatusConstants;

class UserConstants
{
    const SUDO = "Sudo";
    const DRIVER = "Driver";
    const USER = "User";
    const ADMIN = "Admin";
    const SUPER_ADMIN = "Super Admin";

    const ROLES = [
        self::DRIVER,
        self::ADMIN,
        self::USER,
    ];

    const STATUSES = [
        StatusConstants::PENDING,
        StatusConstants::ACTIVE,
        StatusConstants::INACTIVE,
    ];
}
