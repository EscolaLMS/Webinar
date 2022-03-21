<?php

namespace EscolaLms\Webinar\Services;

use Carbon\Carbon;
use EscolaLms\Jitsi\Services\Contracts\JitsiServiceContract;
use EscolaLms\Webinar\Dto\FilterListDto;
use EscolaLms\Webinar\Dto\WebinarDto;
use EscolaLms\Webinar\Helpers\StrategyHelper;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Repositories\Contracts\WebinarRepositoryContract;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use EscolaLms\Youtube\Dto\Contracts\YTLiveDtoContract;
use EscolaLms\Youtube\Dto\YTBroadcastDto;
use EscolaLms\Youtube\Enum\YTStatusesEnum;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebinarService implements WebinarServiceContract
{
    private WebinarRepositoryContract $webinarRepositoryContract;
    private JitsiServiceContract $jitsiServiceContract;
    private YoutubeServiceContract $youtubeServiceContract;

    public function __construct(
        WebinarRepositoryContract $webinarRepositoryContract,
        JitsiServiceContract $jitsiServiceContract,
        YoutubeServiceContract $youtubeServiceContract
    ) {
        $this->webinarRepositoryContract = $webinarRepositoryContract;
        $this->jitsiServiceContract = $jitsiServiceContract;
        $this->youtubeServiceContract = $youtubeServiceContract;
    }

    public function getWebinarsList(array $search = [], bool $onlyActive = false): Builder
    {
        if ($onlyActive) {
            $now = now()->format('Y-m-d');
            $search['active_to'] = $search['active_to'] ?? $now;
            $search['active_from'] = $search['active_from'] ?? $now;
        }
        $criteria = FilterListDto::prepareFilters($search);
        return $this->webinarRepositoryContract->allQueryBuilder(
            $search,
            $criteria
        );
    }

    public function store(WebinarDto $webinarDto): Webinar
    {
        return DB::transaction(function () use($webinarDto) {
            $webinar = $this->webinarRepositoryContract->create($webinarDto->toArray());
            $this->setRelations($webinar, $webinarDto->getRelations());
            $this->setFiles($webinar, $webinarDto->getFiles());
            $this->setYtStream($webinar);
            $webinar->save();
            return $webinar;
        });
    }

    public function update(int $id, WebinarDto $webinarDto): Webinar
    {
        $webinar = $this->show($id);
        return DB::transaction(function () use($webinar, $webinarDto) {
            $this->setFiles($webinar, $webinarDto->getFiles());
            $webinar = $this->webinarRepositoryContract->updateModel($webinar, $webinarDto->toArray());
            $this->setRelations($webinar, $webinarDto->getRelations());
            $this->updateYtStream($webinar);
            return $webinar;
        });
    }

    public function show(int $id): Webinar
    {
        $webinar = $this->webinarRepositoryContract->find($id);
        if (!$webinar) {
            throw new NotFoundHttpException(__('Webinar not found'));
        }
        return $webinar;
    }

    public function delete(int $id): ?bool
    {
        return DB::transaction(function () use($id) {
            $webinar = $this->webinarRepositoryContract->find($id);
            if (!$webinar) {
                throw new NotFoundHttpException(__('Webinar not found'));
            }
            $ytId = $webinar->yt_id;
            $deleteModel = $this->webinarRepositoryContract->deleteModel($webinar);
            if ($deleteModel) {
                $this->youtubeServiceContract->removeYTStream($ytId);
            }
            return $deleteModel;
        });
    }

    public function setRelations(Webinar $webinar, array $relations = []): void
    {
        foreach ($relations as $key => $value) {
            $className = 'WebinarWith' . ucfirst($key) . 'Strategy';
            StrategyHelper::useStrategyPattern(
                $className,
                'RelationsStrategy',
                'setRelation',
                $webinar,
                $relations
            );
        }
    }

    public function setFiles(Webinar $webinar, array $files = []): void
    {
        foreach ($files as $key => $file) {
            $webinar->$key = $file->storePublicly("webinar/{$webinar->getKey()}/images");
        }
    }

    public function generateJitsi(int $webinarId): array
    {
        $webinar = $this->webinarRepositoryContract->find($webinarId);
        if (!$this->canGenerateJitsi($webinar)) {
            throw new NotFoundHttpException(__('Webinar is not available'));
        }

        return array_merge($this->jitsiServiceContract->getChannelData(
            auth()->user(),
            Str::studly($webinar->name)
        ), [
            'yt_url' => $webinar->yt_url,
            'yt_stream_url' => $webinar->yt_stream_url,
            'yt_stream_key' => $webinar->yt_stream_key,
        ]);
    }

    public function setYtStream(Webinar $webinar): void
    {
        $this->setYtStreamToWebinar(
            $this->youtubeServiceContract->generateYTStream($this->prepareYTDtoBroadcast($webinar)),
            $webinar
        );
    }

    public function updateYTStream(Webinar $webinar): void
    {
        $this->setYtStreamToWebinar(
            $this->youtubeServiceContract->updateYTStream($this->prepareYTDtoBroadcast($webinar)),
            $webinar
        );
    }

    public function getWebinarsListForCurrentUser(array $search = []): Builder
    {
        $now = now()->format('Y-m-d');
        $search['active_to'] = $search['active_to'] ?? $now;
        $search['active_from'] = $search['active_from'] ?? $now;
        $criteria = FilterListDto::prepareFilters($search);
        return $this->webinarRepositoryContract->forCurrentUser(
            $search,
            $criteria
        );
    }
  
    public function setYtStream(Webinar $webinar): void
    {
        $this->setYtStreamToWebinar(
            $this->youtubeServiceContract->generateYTStream($this->prepareYTDtoBroadcast($webinar)),
            $webinar
        );
    }

    public function updateYTStream(Webinar $webinar): void
    {
        $this->setYtStreamToWebinar(
            $this->youtubeServiceContract->updateYTStream($this->prepareYTDtoBroadcast($webinar)),
            $webinar
        );
    }

    public function getWebinarsListForCurrentUser(array $search = []): Builder
    {
        $now = now()->format('Y-m-d');
        $search['active_to'] = $search['active_to'] ?? $now;
        $search['active_from'] = $search['active_from'] ?? $now;
        $criteria = FilterListDto::prepareFilters($search);
        return $this->webinarRepositoryContract->forCurrentUser(
            $search,
            $criteria
        );
    }
  
    private function setYtStreamToWebinar(YTLiveDtoContract $ytLiveDto, Webinar $webinar): void
    {
        if ($ytLiveDto) {
            $webinar->yt_id = $ytLiveDto->getId();
            $webinar->yt_url = $ytLiveDto->getYtUrl();
            $ytStreamDto = $ytLiveDto->getYTStreamDto();
            if ($ytStreamDto) {
                $webinar->yt_stream_url = $ytStreamDto->getYTCdnDto()->getStreamUrl();
                $webinar->yt_stream_key = $ytStreamDto->getYTCdnDto()->getStreamName();
            }
        }
    }

    private function prepareYTDtoBroadcast(Webinar $webinar): YTBroadcastDto
    {
        $endDate = $this->getWebinarEndDate($webinar);
        $data = [
            "title" => $webinar->name,
            "description" => $webinar->description,
            "event_start_date_time" => Carbon::make($webinar->active_to)->format('Y-m-d H:i:s'),
            "event_end_date_time" => $endDate ? $endDate->format('Y-m-d H:i:s') : '',
            "time_zone" => config('timezone', 'UTC'),
            'privacy_status' => YTStatusesEnum::UNLISTED,				// default: "public" OR "private"
            "id" => $webinar->yt_id ?? null,
        ];
        return new YTBroadcastDto($data);
    }

    private function canGenerateJitsi(Webinar $webinar): bool
    {
        $now = now();
        return $webinar->isPublished() &&
            $now <= $this->getWebinarEndDate($webinar) &&
            $webinar->hasYT();
    }

    /**
     * @param Webinar $webinar
     * @return Carbon|false|null
     */
    private function getWebinarEndDate(Webinar $webinar): ?Carbon
    {
        $modifyTimeStrings = [
            'seconds', 'minutes', 'hours', 'weeks', 'years'
        ];
        if (!$webinar->duration) {
            return null;
        }
        $explode = explode(' ', $webinar->duration);
        $count = $explode[0];
        $string = in_array($explode[1], $modifyTimeStrings) ? $explode[1] : 'hours';
        return Carbon::make($webinar->active_to)->modify('+' . $count . ' ' . $string);
    }
}
