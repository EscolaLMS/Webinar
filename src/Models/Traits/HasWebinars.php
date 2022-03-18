<?php

namespace EscolaLms\Webinar\Models\Traits;

use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Models\WebinarUserPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasWebinars
{
    public function webinars(): BelongsToMany
    {
        /* @var $this \EscolaLms\Core\Models\User */
        return $this->belongsToMany(Webinar::class, 'webinar_user')->using(WebinarUserPivot::class);
    }
}
