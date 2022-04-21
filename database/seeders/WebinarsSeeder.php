<?php

namespace EscolaLms\Webinar\Database\Seeders;

use EscolaLms\Auth\Models\User;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Models\WebinarUserPivot;
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
                WebinarUserPivot::factory(3, [
                    'webinar_id' => $webinar->getKey(),
                    'user_id' => $users->random(1)->first(),
                ])->create()
            );
    }
}
