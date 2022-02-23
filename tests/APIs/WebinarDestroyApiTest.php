<?php

namespace EscolaLms\Consultations\Tests\APIs;

use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WebinarDestroyApiTest extends TestCase
{
    use DatabaseTransactions;
    private Webinar $webinar;
    private string $apiUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebinarsPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    private function initVariable(): void
    {
        $this->webinar = Webinar::factory()->create();
        $this->apiUrl = '/api/admin/webinars/' . $this->webinar->getKey();
    }

    public function testWebinarDestroyUnauthorized(): void
    {
        $this->initVariable();
        $response = $this->json('DELETE', $this->apiUrl);
        $response->assertUnauthorized();
    }

    public function testWebinarDestroy(): void
    {
        $this->initVariable();
        $response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            $this->apiUrl
        );
        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);
    }

    public function testWebinarDestroyFailed(): void
    {
        $webinar = Webinar::factory()->create();
        $id = $webinar->getKey();
        $webinar->delete();
        $response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/webinars/' . $id
        );
        $response->assertNotFound();
    }
}
