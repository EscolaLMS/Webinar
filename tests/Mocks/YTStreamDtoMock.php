<?php

namespace EscolaLms\Webinar\Tests\Mocks;

use EscolaLms\Youtube\Dto\Contracts\YTCdnDtoContract;
use EscolaLms\Youtube\Dto\Contracts\YTStreamDtoContract;

class YTStreamDtoMock implements YTStreamDtoContract
{
    public function getYTCdnDto(): YTCdnDtoContract
    {
        return new YTCdnDtoMock();
    }

}
