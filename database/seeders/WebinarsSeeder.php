<?php

namespace EscolaLms\Webinar\Database\Seeders;

use EscolaLms\Auth\Models\User;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
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
            ->each(function (Webinar $webinar) use($users) {
                $consultationsForOrder = collect();
                $consultationsForOrder->push($webinar);
                $price = $consultationsForOrder->reduce(fn ($acc, Webinar $webinar) => $acc + $webinar->getBuyablePrice(), 0);
                for ($i = 0; $i < random_int(1, 7); $i++) {
                    Order::factory()->afterCreating(
                        function (Order $order) use($consultationsForOrder) {
                            $order->items()->saveMany(
                                $consultationsForOrder->map(
                                    function (Webinar $webinar) {
                                        return OrderItem::query()->make([
                                            'quantity' => 1,
                                            'buyable_id' => $webinar->getKey(),
                                            'buyable_type' => Webinar::class,
                                        ]);
                                    }
                                )
                            );
                        }
                    )->create([
                        'user_id' => $users->random()->getKey(),
                        'total' => $price,
                        'subtotal' => $price,
                    ]);
                }
            });
    }
}
