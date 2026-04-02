<?php

namespace EscolaLms\Webinar\Broadcasting;

use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Contracts\Auth\Authenticatable;

class WebinarChannel
{
    public function join(Authenticatable $user, Webinar $webinar, string $term): bool
    {
        return $webinar->trainers()->where('users.id', '=', $user->getKey())->exists();
    }
}
