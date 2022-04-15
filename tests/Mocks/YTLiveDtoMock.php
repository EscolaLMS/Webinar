<?php

namespace EscolaLms\Webinar\Tests\Mocks;

use EscolaLms\Youtube\Dto\Contracts\YTLiveDtoContract;
use EscolaLms\Youtube\Dto\Contracts\YTStreamDtoContract;

class YTLiveDtoMock extends MockTest implements YTLiveDtoContract
{
    private string $ytUrl;
    private bool $ytAutostartStatus;
    private string $id;

    public function __construct()
    {
        parent::__construct();
        $this->ytUrl = 'https://www.youtube.com/';
        $this->id = 'test1234aasd';
    }

    public function getId(): ?string
    {
        return $this->id ?? '';
    }

    public function getYtUrl(): ?string
    {
        return $this->ytUrl ?? '';
    }

    public function getYTStreamDto(): ?YTStreamDtoContract
    {
        return new YTStreamDtoMock();
    }

}
