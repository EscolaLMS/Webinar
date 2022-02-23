<?php

namespace EscolaLms\Webinar\Strategies\Relations;

use EscolaLms\Webinar\Strategies\Contracts\RelationStrategyContract;
use EscolaLms\Webinar\Models\Webinar;

class WebinarWithAuthorsStrategy implements RelationStrategyContract
{
    private Webinar $webinar;
    private array $data;

    public function __construct(array $params) {
        $this->webinar = $params[0];
        $this->data = $params[1] ?? [];
    }

    public function setRelation(): void
    {
        $this->webinar->authors()->sync($this->data['authors']);
    }
}
