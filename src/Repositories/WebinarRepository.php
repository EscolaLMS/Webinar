<?php

namespace EscolaLms\Webinar\Repositories;

use EscolaLms\Core\Repositories\BaseRepository;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Repositories\Contracts\WebinarRepositoryContract;
use Illuminate\Database\Eloquent\Builder;

class WebinarRepository extends BaseRepository implements WebinarRepositoryContract
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Webinar::class;
    }

    public function allQueryBuilder(array $search = [], array $criteria = []): Builder
    {
        $query = $this->allQuery($search);
        if (!empty($criteria)) {
            $query = $this->applyCriteria($query, $criteria);
        }
        return $query;
    }

    public function updateModel(Webinar $webinar, array $data): Webinar
    {
        $webinar->fill($data);
        $webinar->save();
        return $webinar;
    }
}
