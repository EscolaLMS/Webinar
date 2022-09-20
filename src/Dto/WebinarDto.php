<?php

namespace EscolaLms\Webinar\Dto;

use Carbon\Carbon;
use EscolaLms\Webinar\Dto\Contracts\ModelDtoContract;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Http\UploadedFile;

class WebinarDto extends BaseDto implements ModelDtoContract
{
    protected string $name;
    protected string $status;
    protected string $description;
    protected string $shortDesc;
    protected string $agenda;
    protected ?string $activeTo;
    protected ?string $activeFrom;
    protected ?string $duration;
    protected ?int $basePrice;
    protected $imagePath = false;
    protected $logotypePath = false;

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

    public function getLogotypePath()
    {
        if ($this->logotypePath !== false) {
            return $this->logotypePath === null ? '' : $this->logotypePath;
        }
        return false;
    }

    protected function setImage($file): void
    {
        $this->files['image_path'] = $file;
    }

    protected function setLogotype($logotype): void
    {
        $this->files['logotype_path'] = $logotype;
    }

    protected function setTrainers(array $trainers): void
    {
        $this->relations['trainers'] = $trainers;
    }

    protected function setTags(array $tags): void
    {
        $this->relations['tags'] = $tags;
    }

    protected function setActiveTo(?string $activeTo): void
    {
        $this->activeTo = Carbon::make($activeTo);
    }

    protected function setActiveFrom(?string $activeFrom): void
    {
        $this->activeFrom = Carbon::make($activeFrom);
    }
}
