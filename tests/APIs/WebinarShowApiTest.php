<?php

namespace EscolaLms\Consultations\Tests\APIs;

use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WebinarShowApiTest extends TestCase
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
        $this->webinar = Webinar::factory()->create();
        $this->webinar->trainers()->attach($this->user);
        $this->apiUrl = '/api/admin/webinars/' . $this->webinar->getKey();
    }

    public function testWebinarShowUnauthorized(): void
    {
        $response = $this->json('GET', $this->apiUrl);
        $response->assertUnauthorized();
    }

    public function testWebinarShow(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));
        $response = $this->actingAs($this->user, 'api')->json(
            'GET',
            $this->apiUrl
        );
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $this->webinar->getKey(),
            'name' => $this->webinar->name,
            'status' => $this->webinar->status,
            'created_at' => $this->webinar->created_at,
        ]);
        $response->assertJsonFragment(['success' => true]);
    }

    public function testConsultationShowAPI()
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));
        $response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/webinars/' . $this->webinar->getKey()
        );
        $response->assertOk();
    }

    public function testWebinarShowFailed(): void
    {
        $webinar = Webinar::factory()->create();
        $id = $webinar->getKey();
        $webinar->delete();
        $webinarUpdate = Webinar::factory()->make();
        $response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/webinars/' . $id,
            $webinarUpdate->toArray()
        );
        $response->assertNotFound();
    }
}
