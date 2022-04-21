<?php

namespace EscolaLms\Webinar\Database\Seeders;

use EscolaLms\Auth\Models\User;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Database\Seeder;

class WebinarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory(5)->create();
        Webinar::factory(10)
            ->create()
            ->each(fn (Webinar $webinar) =>
                $webinar->users()->sync($users->pluck('id')->toArray())
            );
    }
}
