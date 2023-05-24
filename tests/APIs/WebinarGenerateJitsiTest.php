<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Jitsi\Helpers\StringHelper;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

class WebinarGenerateJitsiTest extends TestCase
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

    public function testGenerateJitsiUnAuthorized(): void
    {
        $response = $this->json('GET', 'api/webinars/generate-jitsi/1');
        $response->assertUnauthorized();
    }

    public function testGenerateJitsiWithWebinar(): void
    {
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('getYtLiveStream')->once()->andReturn(collect(['s']));
        $this->webinar = Webinar::factory([
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_to' => now(),
            'duration' => '1 hours'
        ])->create();
        $response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/webinars/generate-jitsi/' . $this->webinar->getKey()
        );
        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) => $json->has('data',
                fn (AssertableJson $json) => $json->has('data',
                    fn (AssertableJson $json) => $json->has('jwt')
                        ->has('userInfo',
                            fn (AssertableJson $json) => $json
                                ->where('displayName', "{$this->user->first_name} {$this->user->last_name}")
                                ->where('email', $this->user->email)
                        )
                        ->where('roomName', StringHelper::convertToJitsiSlug($this->webinar->name))
                        ->etc()
                    )
                    ->where('yt_url',  $this->webinar->yt_url)
                    ->where('yt_stream_url', $this->webinar->yt_stream_url)
                    ->where('yt_stream_key', $this->webinar->yt_stream_key)
                    ->etc()
            )->where('success', true)->etc()
        );
    }

    public function testGenerateJitsiWithNotAvailable(): void
    {
        $this->webinar = Webinar::factory([
            'status' => WebinarStatusEnum::ARCHIVED,
        ])->create();

        $response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/webinars/generate-jitsi/' . $this->webinar->getKey()
        );
        $response->assertNotFound();
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', __('Webinar is not available'))->etc());
    }

    public function testGenerateJitsiAfterExpired(): void
    {
        $this->webinar = Webinar::factory([
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_to' => now()->modify('-2 hour')->format("Y-m-d\TH:i:s.000000\Z"),
            'duration' => '1 hours'
        ])->create();
        $response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/webinars/generate-jitsi/' . $this->webinar->getKey()
        );
        $response->assertNotFound();
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', __('Webinar is not available'))->etc());
    }
}
