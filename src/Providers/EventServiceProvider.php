<?php

namespace EscolaLms\Webinar\Providers;

use EscolaLms\Webinar\Events\ReminderAboutTerm;
use EscolaLms\Webinar\Listeners\ReminderAboutTermListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ReminderAboutTerm::class => [
            ReminderAboutTermListener::class
        ]
    ];
}
