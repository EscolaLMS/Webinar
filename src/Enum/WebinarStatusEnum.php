<?php

namespace EscolaLms\Webinar\Enum;

use EscolaLms\Core\Enums\BasicEnum;

class WebinarStatusEnum extends BasicEnum
{
    public const DRAFT     = 'draft';
    public const PUBLISHED = 'published';
    public const ARCHIVED  = 'archived';
}
