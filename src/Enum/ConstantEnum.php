<?php

namespace EscolaLms\Webinar\Enum;

use BenSampo\Enum\Enum;

class ConstantEnum extends Enum
{
    public const PER_PAGE = 15;
    public const DIRECTORY = 'webinar';
    public const REDIS_IMAGES_KEY = 'signed_urls_index';
    public const REDIS_IMAGES_TTL = 20;
}
