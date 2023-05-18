<?php

namespace EscolaLms\Webinar\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WebinarIncomingCriterion extends Criterion
{
    private ?bool $withDuration;
    public function __construct(string $value, ?bool $withDuration)
    {
        parent::__construct(null, $value);
        $this->withDuration = $withDuration;
    }

    public function apply(Builder $query): Builder
    {
        if ($this->withDuration) {
            return $query
                ->where(function (Builder $query) {
                   $query = $query
                       ->where(fn (Builder $query) => $query->whereNull('duration')->where('webinars.active_to', '>', $this->value));
                   if (DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                       return $query->orWhere(fn (Builder $query) => $query
                           ->whereRaw("active_to + CASE WHEN (duration ~ '^\\d+$') THEN (duration || ' hours')::interval ELSE (duration)::interval END > ?", [$this->value])
                       );
                   }
                   return $query->orWhere(fn (Builder $query) => $query
                       ->whereRaw("DATE_ADD(active_to, INTERVAL CASE WHEN (duration REGEXP '^[0-9]+$') THEN duration HOURS ELSE duration END) > ?", [$this->value])
                   );
                });
        }
        return $query->where('webinars.active_to', '>', $this->value);
    }
}
