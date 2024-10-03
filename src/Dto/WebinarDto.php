<?php

namespace EscolaLms\Webinar\Dto;

use Carbon\Carbon;
use EscolaLms\Consultations\Enum\ConstantEnum;
use EscolaLms\Webinar\Dto\Contracts\ModelDtoContract;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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
        // @phpstan-ignore-next-line
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
            return $this->imagePath === null ? '' : Str::after($this->imagePath, env('AWS_ACCESS_KEY_ID') . '/');
        }
        return false;
    }

    public function getLogotypePath()
    {
        if ($this->logotypePath !== false) {
            if ($this->logotypePath) {
                $logotypePath = Str::after($this->logotypePath, env('AWS_ACCESS_KEY_ID') . '/');
                return Str::startsWith($logotypePath, ConstantEnum::DIRECTORY) ? $logotypePath : ConstantEnum::DIRECTORY . '/' .$logotypePath;
            }
            return '';
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
