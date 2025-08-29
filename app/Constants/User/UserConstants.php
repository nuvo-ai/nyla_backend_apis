<?php

namespace App\Constants\User;

use App\Constants\General\StatusConstants;

class UserConstants
{
    const SUDO = "Sudo";
    const DOCTOR = "Doctor";
    const LAB = "Lab";
    const FRONT_DESK = "Frontdesk";
    const USER = "User";
    const ADMIN = "Admin";
    const SUPER_ADMIN = "Super Admin";
    const NURSE = 'Nurse';
    const PHARMACY_ADMIN = "Pharmacy";
    const SUPPORT = "Support";

    const ROLES = [
        self::ADMIN,
        self::USER,
        self::SUDO,
        self::DOCTOR,
        self::LAB,
        self::FRONT_DESK,
        self::SUPER_ADMIN,
        self::NURSE,
        self::SUPPORT,
    ];

    const STATUSES = [
        StatusConstants::PENDING,
        StatusConstants::ACTIVE,
        StatusConstants::INACTIVE,
    ];
}
