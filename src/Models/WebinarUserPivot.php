<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Core\Models\User;
use EscolaLms\Webinar\Database\Factories\WebinarUserPivotFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WebinarUserPivot extends Pivot
{
    protected $table = 'webinar_user';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }

    protected static function newFactory(): WebinarUserPivotFactory
    {
        return WebinarUserPivotFactory::new();
    }
}
