<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Tests\Mocks\YTLiveDtoMock;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;

class WebinarUpdateApiTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
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
        $this->apiUrl = '/api/admin/webinars/' . $this->webinar->getKey();
    }

    public function testWebinarUpdateUnauthorized(): void
    {
        $response = $this->json('POST',$this->apiUrl);
        $response->assertUnauthorized();
    }

    public function testWebinarUpdate(): void
    {
        $webinarUpdate = Webinar::factory()->make()->toArray();
        $authors = config('auth.providers.users.model')::factory(2)->create()->pluck('id')->toArray();
        $tags = ['Event', 'Webinar'];
        $requestArray = array_merge(
            $webinarUpdate,
            ['image' => UploadedFile::fake()->image('image.jpg')],
            ['authors' => $authors],
            ['tags' => $tags],
        );
        $ytLiveDtoMock = new YTLiveDtoMock();
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('updateYTStream')->once()->andReturn($ytLiveDtoMock);

        $response = $this->actingAs($this->user, 'api')->json(
            'POST',
            $this->apiUrl,
            $requestArray
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $this->webinar->getKey(),
            'name' => $webinarUpdate['name'],
            'status' => $webinarUpdate['status'],
        ]);
        $response->assertJsonFragment(['success' => true]);
        $response->assertJson(fn (AssertableJson $json) => $json->has(
            'data',
            fn ($json) => $json
                ->has('image_path')
                ->has('tags', fn (AssertableJson $json) => $json->each(
                        fn (AssertableJson $json) => $json->where('title', fn ($json) =>
                            in_array($json, $tags)
                        )
                        ->etc()
                    )
                    ->etc()
                )
                ->where('yt_url', $ytLiveDtoMock->getYtUrl())
                ->where('yt_stream_url', $ytLiveDtoMock->getYTStreamDto()->getYTCdnDto()->getStreamUrl())
                ->where('yt_stream_key', $ytLiveDtoMock->getYTStreamDto()->getYTCdnDto()->getStreamName())
                ->has('authors', fn (AssertableJson $json) =>
                    $json->each(fn (AssertableJson $json) =>
                        $json->where('id', fn ($json) =>
                            in_array($json, $authors)
                        )
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            )
            ->etc()
        );
    }

    public function testWebinarUpdateFailed(): void
    {
        $webinar = Webinar::factory()->create();
        $id = $webinar->getKey();
        $webinar->delete();
        $webinarUpdate = Webinar::factory()->make();
        $response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/webinars/' . $id,
            $webinarUpdate->toArray()
        );
        $response->assertNotFound();
    }
}
