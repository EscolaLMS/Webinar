<?php

namespace EscolaLms\Webinar\Dto;

class GenerateSignedScreenUrlsDto extends BaseDto
{
    protected int $webinarId;
    protected string $executedAt;
    protected array $files;
    protected int $userId;

    public function getWebinarId(): int
    {
        return $this->webinarId;
    }

    public function setWebinarId(int $webinarId): void
    {
        $this->webinarId = $webinarId;
    }

    public function getExecutedAt(): string
    {
        return $this->executedAt;
    }

    public function setExecutedAt(string $executedAt): void
    {
        $this->executedAt = $executedAt;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
