<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Youtube\Services\Contracts\AuthServiceContract;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

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
        $this->webinar->trainers()->sync($this->user);
        $this->webinar->tags()->save(new Tag(['title' => 'Event']));
    }

    public function testWebinarsList(): void
    {
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect());
        $this->response = $this->actingAs($this->user, 'api')->get('/api/admin/webinars');
        $this->response->assertOk();
    }

    public function testWebinarsListWithFilter(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));
        $filterData = [
            'name=' . $this->webinar->name,
            'status[]=' . $this->webinar->status,
            'tags[]=' . 'Event',
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

    public function testWebinarsListForApi(): void
    {
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect());
        $this->response = $this->get('/api/webinars');
        $this->response->assertOk();
    }

    public function testWebinarsListWithFilterForApi(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));
        $filterData = [
            'name=' . $this->webinar->name,
            'status[]=' . $this->webinar->status,
            'tags[]=' . 'Event',
        ];
        $this->response = $this->actingAs($this->user, 'api')->get('/api/webinars?' . implode('&', $filterData));
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'id' => $this->webinar->getKey(),
            'name' => $this->webinar->name,
            'status' => $this->webinar->status,
            'created_at' => $this->webinar->created_at,
        ]);
    }


    public function testYtUnAuthorizedException(): void
    {
        $authServiceContractMock = $this->mock(AuthServiceContract::class);
        $authServiceContractMock->shouldReceive('setAccessToken')->zeroOrMoreTimes()->andReturn(false);
        $this->response = $this->actingAs($this->user, 'api')->get('/api/webinars');
        $this->response->assertStatus(400);
        $this->response->assertJsonFragment(['code' => 400,]);
    }
}
