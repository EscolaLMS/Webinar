<?php

namespace EscolaLms\Webinar\Dto;

use EscolaLms\Webinar\Dto\Contracts\ModelDtoContract;
use EscolaLms\Webinar\Models\Webinar;

class WebinarDto extends BaseDto implements ModelDtoContract
{
    protected string $name;
    protected string $status;
    protected string $description;
    protected ?string $activeTo;
    protected ?string $activeFrom;
    protected ?string $duration;
    protected ?int $basePrice;
    protected $imagePath = false;

    public function model(): Webinar
    {
        return Webinar::newModelInstance();
    }

    public function toArray($filters = false): array
    {
        $result = $this->fillInArray($this->model()->getFillable());
        return $filters ? array_filter($result) : $result;
    }

    public function getImagePath()
    {
        if ($this->imagePath !== false) {
            return $this->imagePath === null ? '' : $this->imagePath;
        }
        return false;
    }

    protected function setAuthors(array $authors): void
    {
        $this->relations['authors'] = $authors;
    }

    protected function setTags(array $tags): void
    {
        $this->relations['tags'] = $tags;
    }
}
