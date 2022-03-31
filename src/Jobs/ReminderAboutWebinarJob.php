<?php

namespace EscolaLms\Webinar\Jobs;

use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReminderAboutWebinarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private string $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $webinarServiceContract = app(WebinarServiceContract::class);
        $webinarServiceContract->reminderAboutWebinar($this->status);
    }
}
