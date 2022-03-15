<?php

namespace EscolaLms\Webinar\Strategies\Relations;

use EscolaLms\Core\Models\User;
use EscolaLms\Webinar\Events\WebinarAuthorAssigned;
use EscolaLms\Webinar\Events\WebinarAuthorUnassigned;
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
        $changes = $this->webinar->authors()->sync($this->data['authors']);
        $this->dispatchEventForAuthorsAttachedToWebinar($changes['attached']);
        $this->dispatchEventForAuthorsDetachedFromWebinar($changes['detached']);
    }

    private function dispatchEventForAuthorsAttachedToWebinar(array $users = []): void
    {
        foreach ($users as $attached) {
            $user = is_int($attached) ? User::find($attached) : $attached;
            if ($user) {
                event(new WebinarAuthorAssigned($user, $this->webinar));
            }
        }
    }

    private function dispatchEventForAuthorsDetachedFromWebinar(array $users = []): void
    {
        foreach ($users as $detached) {
            $user = is_int($detached) ? User::find($detached) : $detached;
            if ($user) {
                event(new WebinarAuthorUnassigned($user, $this->webinar));
            }
        }
    }
}
