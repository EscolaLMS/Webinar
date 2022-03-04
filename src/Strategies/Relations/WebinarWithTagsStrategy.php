<?php

namespace EscolaLms\Webinar\Strategies\Relations;

use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Strategies\Contracts\RelationStrategyContract;
use EscolaLms\Webinar\Models\Webinar;

class WebinarWithTagsStrategy implements RelationStrategyContract
{
    private Webinar $webinar;
    private array $data;

    public function __construct(array $params) {
        $this->webinar = $params[0];
        $this->data = $params[1] ?? [];
    }

    public function setRelation(): void
    {
        foreach ($this->data['tags'] as $tag) {
            $this->webinar->tags()->save(new Tag(['title' => $tag]));
        }
    }
}
