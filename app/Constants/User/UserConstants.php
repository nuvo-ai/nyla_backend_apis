<?php

namespace App\Constants\User;

use App\Constants\General\StatusConstants;

class UserConstants
{
    const SUDO = "Sudo";
    const DOCTOR = "Doctor";
    const LAB = "Lab";
    const FRONT_DESK = "Front Desk";
    const USER = "User";
    const ADMIN = "Admin";
    const SUPER_ADMIN = "Super Admin";
    const NURSE = 'Nurse';

    const ROLES = [
        self::ADMIN,
        self::USER,
        self::SUDO,
        self::DOCTOR,
        self::LAB,
        self::FRONT_DESK,
        self::SUPER_ADMIN,
        self::NURSE,
    ];

    const STATUSES = [
        StatusConstants::PENDING,
        StatusConstants::ACTIVE,
        StatusConstants::INACTIVE,
    ];
}
