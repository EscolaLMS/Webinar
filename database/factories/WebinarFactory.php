<?php

namespace EscolaLms\Webinar\Database\Factories;

use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebinarFactory extends Factory
{
    protected $model = Webinar::class;

    public function definition()
    {
        $now = now();
        return [
            'base_price' => $this->faker->numberBetween(1, 200),
            'name' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(WebinarStatusEnum::getValues()),
            'description' => $this->faker->sentence,
            'active_from' => $now,
            'active_to' => (clone $now)->modify('+1 hour'),
        ];
    }
}