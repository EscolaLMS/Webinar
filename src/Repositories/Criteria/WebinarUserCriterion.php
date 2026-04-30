<?php

namespace EscolaLms\Webinar\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;

class WebinarUserCriterion extends Criterion
{
    public function __construct(string $value)
    {
        parent::__construct(null, $value);
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->whereHas(
                'webinars',
                fn (Builder $query) => $query
                    ->where('id', $this->value)
            );
    }
}
