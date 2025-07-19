<?php

namespace App\Constants\General;

class StatusConstants
{
    const ACTIVE = 'Active';

    const AVAILABLE = 'Available';

    const INACTIVE = 'Inactive';

    const PENDING = 'Pending';

    const PROCESSING = 'Processing';

    const COMPLETED = 'Completed';

    const UPCOMING = 'Upcoming';

    const STOPPED = 'Stopped';

    const SUCCESSFUL = 'Successful';

    const APPROVED = 'Approved';

    const AWAITING_REVIEW = 'Awaiting_Review';

    const AWAITING_CONFIRMATION = 'Awaiting_Confirmation';

    const CANCELLED = 'Cancelled';

    const DECLINED = 'Declined';

    const REFUNDED = 'Refunded';

    const ROLLBACK = 'Rollback';

    const SUSPENDED = 'Suspended';

    const FAILED = 'Failed';

    const SOLD = 'Sold';

    const BANNED = 'Banned';
    
    const DISAPPROVED = "Disapproved";

    const YES = 'Yes';

    const NO = 'No';

    const SCHEDULED = 'Scheduled';

    const CONFIRMED = 'Confirmed';

    const DISCHARGE = 'Discharge';

    const ADMITTED  = 'Admitted';


    const NO_SHOW = 'No Show';

    const ACTIVE_STATUSES = [
        self::PENDING, self::PROCESSING, self::APPROVED, self::SCHEDULED
    ];

    const STATUSES = [
        self::ACTIVE => self::ACTIVE,
        self::INACTIVE => self::INACTIVE,
    ];

    const BOOL_OPTIONS = [
        self::YES => self::YES,
        self::NO => self::NO,
    ];

    const PAYMENT_STATUS_OPTIONS = [
        self::PENDING => self::PENDING,
        self::SUCCESSFUL => self::SUCCESSFUL,
        self::FAILED => self::FAILED,
        self::REFUNDED => self::REFUNDED,
    ];

    const SESSION_OPTIONS = [
        self::PENDING => self::PENDING,
        self::PROCESSING => self::PROCESSING,
        self::AWAITING_REVIEW => 'Awaiting Review',
        self::AWAITING_CONFIRMATION => 'Awaiting Confirmation',
        self::DECLINED => self::DECLINED,
        self::COMPLETED => self::COMPLETED,
    ];

     const SCHEDULE_STATUSES = [
        self::PENDING,
        self::CONFIRMED,
        self::CANCELLED,
        self::COMPLETED,
        self::SCHEDULED,
        self::NO_SHOW,
    ];
}
