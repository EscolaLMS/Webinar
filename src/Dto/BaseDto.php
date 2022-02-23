<?php

namespace EscolaLms\Webinar\Dto;

use EscolaLms\Webinar\Dto\Traits\DtoHelper;

abstract class BaseDto
{
    use DtoHelper;

    public function __construct(array $data = [])
    {
        $this->setterByData($data);
    }
}
