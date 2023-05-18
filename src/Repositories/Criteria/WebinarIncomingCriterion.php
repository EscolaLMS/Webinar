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
                       ->whereRaw("(
                       CASE
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'seconds' or SUBSTRING_INDEX(duration, ' ', -1) = 'second' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) SECOND )
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'minutes' or SUBSTRING_INDEX(duration, ' ', -1) = 'minute' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) MINUTE )
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'days' or SUBSTRING_INDEX(duration, ' ', -1) = 'day' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) DAY )
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'week' or SUBSTRING_INDEX(duration, ' ', -1) = 'weeks' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) WEEK )
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'month' or SUBSTRING_INDEX(duration, ' ', -1) = 'months' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) MONTH )
                           WHEN SUBSTRING_INDEX(duration, ' ', -1) = 'year' or SUBSTRING_INDEX(duration, ' ', -1) = 'years' THEN DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) YEAR )
                           ELSE DATE_ADD(active_to, INTERVAL SUBSTRING_INDEX(duration, ' ', 1) HOUR )
                       END
                       ) > ?", [$this->value])
                   );
                });
        }
        return $query->where('webinars.active_to', '>', $this->value);
    }
}
