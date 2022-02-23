<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WebinarApiTest extends TestCase
{
    use DatabaseTransactions;
    private Webinar $webinar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebinarsPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->webinar = Webinar::factory()->create();
        $this->webinar->authors()->sync($this->user);
    }

    public function testWebinarsList(): void
    {
        $this->response = $this->actingAs($this->user, 'api')->get('/api/admin/webinars');
        $this->response->assertOk();
    }

    public function testWebinarsListWithFilter(): void
    {
        $filterData = [
            'base_price=' . $this->webinar->base_price,
            'name=' . $this->webinar->name,
            'status[]=' . $this->webinar->status,
        ];
        $this->response = $this->actingAs($this->user, 'api')->get('/api/admin/webinars?' . implode('&', $filterData));
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'id' => $this->webinar->getKey(),
            'name' => $this->webinar->name,
            'status' => $this->webinar->status,
            'created_at' => $this->webinar->created_at,
        ]);
    }

    public function testWebinarsListUnauthorized(): void
    {
        $this->response = $this->json('GET','/api/admin/webinars');
        $this->response->assertUnauthorized();
    }
}
