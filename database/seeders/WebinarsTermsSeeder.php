<?php

namespace EscolaLms\Webinar\Database\Seeders;

use EscolaLms\Core\Models\User;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Database\Seeder;

class WebinarsTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::limit(10)->get();
        $trainers = User::limit(5)->get();

        Webinar::factory(5, [
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_to' => now(),
            'duration' => '1 hours'
        ])
            ->create()
            ->each(function (Webinar $webinar) use($users, $trainers) {
                $webinar->users()->sync($users->pluck('id')->toArray());
                $webinar->trainers()->sync($trainers->pluck('id')->toArray());
            });
    }
}
