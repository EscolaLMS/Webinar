<?php

namespace EscolaLms\Webinar\Services;

use EscolaLms\Webinar\Dto\FilterListDto;
use EscolaLms\Webinar\Dto\WebinarDto;
use EscolaLms\Webinar\Helpers\StrategyHelper;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Repositories\Contracts\WebinarRepositoryContract;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebinarService implements WebinarServiceContract
{
    private WebinarRepositoryContract $webinarRepositoryContract;

    public function __construct(
        WebinarRepositoryContract $webinarRepositoryContract
    ) {
        $this->webinarRepositoryContract = $webinarRepositoryContract;
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
}
