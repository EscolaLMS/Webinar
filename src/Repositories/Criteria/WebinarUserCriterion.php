<?php

namespace EscolaLms\Webinar\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WebinarUserCriterion extends Criterion
{
    public function __construct(string $value)
    {
        parent::__construct(null, $value);
    }

    public function apply(Builder $query): Builder
    {
        $userTable = $query->getModel()->getTable();

        return $query->whereExists(function ($subQuery) use ($userTable) {
            $subQuery->select(DB::raw(1))
                ->from('webinar_user')
                ->whereRaw("webinar_user.user_id = {$userTable}.id")
                ->where('webinar_user.webinar_id', $this->value);
        });
    }
}
