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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebinarService implements WebinarServiceContract
{
    private WebinarRepositoryContract $webinarRepositoryContract;
    private JitsiServiceContract $jitsiServiceContract;

    public function __construct(
        WebinarRepositoryContract $webinarRepositoryContract,
        JitsiServiceContract $jitsiServiceContract
    ) {
        $this->webinarRepositoryContract = $webinarRepositoryContract;
        $this->jitsiServiceContract = $jitsiServiceContract;
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
            return $this->webinarRepositoryContract->delete($id);
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

        return $this->jitsiServiceContract->getChannelData(
            auth()->user(),
            Str::studly($webinar->name)
        );
    }

    private function canGenerateJitsi(Webinar $webinar): bool
    {
        $modifyTimeStrings = [
            'seconds', 'minutes', 'hours', 'weeks', 'years'
        ];
        $now = now();
        $explode = explode(' ', $webinar);
        $count = $explode[0];
        $string = in_array($explode[1], $modifyTimeStrings) ? $explode[1] : 'hours';
        $dateTo = Carbon::make($webinar->active_to)->modify('+' . $count . ' ' . $string);

        return $webinar->isPublished() &&
            $now <= $dateTo;
    }
}
