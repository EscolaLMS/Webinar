<?php

namespace EscolaLms\Webinar\Enum;

use BenSampo\Enum\Enum;

class WebinarTermReminderStatusEnum extends Enum
{
    public const REMINDED_DAY_BEFORE = 'reminded_day_before';
    public const REMINDED_HOUR_BEFORE = 'reminded_hour_before';
}
