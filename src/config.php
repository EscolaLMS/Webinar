<?php

use EscolaLms\Webinar\Enum\WebinarTermReminderStatusEnum;

return [
    'perPage' => 15,
    'modifier_date' => [
        WebinarTermReminderStatusEnum::REMINDED_HOUR_BEFORE => '+1 hour',
        WebinarTermReminderStatusEnum::REMINDED_DAY_BEFORE => '+1 day',
    ],
    'exclusion_reminder_status' => [
        WebinarTermReminderStatusEnum::REMINDED_DAY_BEFORE => [
            WebinarTermReminderStatusEnum::REMINDED_DAY_BEFORE,
            WebinarTermReminderStatusEnum::REMINDED_HOUR_BEFORE
        ],
        WebinarTermReminderStatusEnum::REMINDED_HOUR_BEFORE => [
            WebinarTermReminderStatusEnum::REMINDED_HOUR_BEFORE
        ]
    ]


];
