<?php

namespace EscolaLms\Webinar\Tests\Mocks;

use EscolaLms\Youtube\Dto\Contracts\YTCdnDtoContract;

class YTCdnDtoMock extends MockTest implements YTCdnDtoContract
{
    private string $streamUrl;
    private string $streamName;

    public function __construct()
    {
        parent::__construct();
        $this->streamUrl = 'rtmps://a.rtmps.youtube.com/test';
        $this->streamName = '2tqj-usxv-eaxc-77g9-123';
    }

    public function getStreamUrl(): ?string
    {
        return $this->streamUrl ?? '';
    }

    public function getStreamName(): ?string
    {
        return $this->streamName ?? '';
    }
}
