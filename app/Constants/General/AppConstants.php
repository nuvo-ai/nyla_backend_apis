<?php

namespace App\Constants\General;

class AppConstants
{
    const ROLE_SUDO = 'Sudo';

    const ROLE_CUSTOMER = 'Customer';

    const ROLE_ADMIN = 'Admin';

    const TEAM_ZENOVATE = 'Zenovate';

    const TEAM_SKYCARE = 'Skycare';

    const ACIVITY_SUBMITTED = 'Submitted';

    const ACIVITY_REVIEWED = 'Reviewed';

    const ACIVITY_SIGNED = 'Signed';

    const ACIVITY_CONFIRMED = 'Confirmed';

    const ACIVITY_RECREATE = 'Recreate';

    const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CUSTOMER,
    ];

    const ADMIN_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SUDO,
    ];

    const PILL_CLASSES = [
        StatusConstants::COMPLETED => 'success',
        StatusConstants::SUCCESSFUL => 'success',
        StatusConstants::PENDING => 'warning',
        StatusConstants::PROCESSING => 'info',
        StatusConstants::ACTIVE => 'success',
        StatusConstants::INACTIVE => 'warning',
        StatusConstants::DECLINED => 'danger',
        StatusConstants::CANCELLED => 'danger',
        StatusConstants::FAILED => 'danger',
    ];

    const ADMIN_PAGINATION_SIZE = 50;

    const MALE = 'Male';

    const FEMALE = 'Female';

    const RATHER_NOT_SAY = 'Rather not say';

    const OTHERS = 'Others';

    const GENDERS = [
        self::MALE => self::MALE,
        self::FEMALE => self::FEMALE,
        self::RATHER_NOT_SAY => self::RATHER_NOT_SAY,
        self::OTHERS => self::OTHERS,
    ];
}
