<?php

namespace EscolaLms\Webinar\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface WebinarRepositoryContract extends BaseRepositoryContract
{
    public function allQueryBuilder(array $search = [], array $criteria = []): Builder;
    public function updateModel(Webinar $webinar, array $data): Webinar;
    public function deleteModel(Webinar $webinar): ?bool;
    public function forCurrentUser(array $search = [], array $criteria = []): Builder;
    public function getIncomingTerm(array $criteria = []): Collection;
}
