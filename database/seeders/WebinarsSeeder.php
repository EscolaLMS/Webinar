<?php

namespace EscolaLms\Webinar\Database\Seeders;

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
        Webinar::factory(10);
    }
}
