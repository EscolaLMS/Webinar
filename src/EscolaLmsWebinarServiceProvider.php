<?php

namespace EscolaLms\Webinar;

use EscolaLms\Webinar\Providers\EventServiceProvider;
use EscolaLms\Jitsi\EscolaLmsJitsiServiceProvider;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Webinar\Enum\WebinarTermReminderStatusEnum;
use EscolaLms\Webinar\Jobs\ReminderAboutWebinarJob;
use EscolaLms\Webinar\Repositories\Contracts\WebinarRepositoryContract;
use EscolaLms\Webinar\Repositories\WebinarRepository;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use EscolaLms\Webinar\Services\WebinarService;
use EscolaLms\Youtube\EscolaLmsYoutubeServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsWebinarServiceProvider extends ServiceProvider
{
    public const SERVICES = [
        WebinarServiceContract::class => WebinarService::class
    ];
    public const REPOSITORIES = [
        WebinarRepositoryContract::class => WebinarRepository::class,
    ];

    public $singletons = self::SERVICES + self::REPOSITORIES;

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'webinar');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('escolalms_webinar.php'),
        ], 'escolalms_webinar');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'escolalms_webinar');
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EscolaLmsJitsiServiceProvider::class);
        $this->app->register(EscolaLmsSettingsServiceProvider::class);
        $this->app->register(EscolaLmsYoutubeServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }
}
