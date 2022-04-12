<?php

namespace EscolaLms\Consultations\Tests\APIs;

use EscolaLms\Webinar\Tests\Mocks\YTLiveDtoMock;
use EscolaLms\Webinar\Events\WebinarTrainerAssigned;
use EscolaLms\Webinar\Events\WebinarTrainerUnassigned;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

class WebinarStoreApiTest extends TestCase
{
    use DatabaseTransactions;
    private string $apiUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebinarsPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->apiUrl = '/api/admin/webinars';
    }

    public function testWebinarStoreUnauthorized(): void
    {
        $response = $this->json('POST',$this->apiUrl);
        $response->assertUnauthorized();
    }

    public function testWebinarStore(): void
    {
        Event::fake([WebinarTrainerAssigned::class, WebinarTrainerUnassigned::class]);

        $webinar = Webinar::factory()->make()->toArray();
        $trainers = config('auth.providers.users.model')::factory(2)->create()->pluck('id')->toArray();
        $tags = ['Event', 'Webinar'];
        $image = UploadedFile::fake()->image('image.jpg');
        $logotype = UploadedFile::fake()->image('image.jpg');

        $requestArray = array_merge(
            $webinar,
            ['image' => $image],
            ['logotype' => $logotype],
            ['trainers' => $trainers],
            ['tags' => $tags]
        );

        $ytLiveDtoMock = new YTLiveDtoMock();
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('generateYTStream')->once()->andReturn($ytLiveDtoMock);

        $response = $this->actingAs($this->user, 'api')->json(
            'POST',
            $this->apiUrl,
            $requestArray
        );
        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => $webinar['name'],
            'status' => $webinar['status'],
        ]);
        $response->assertJsonFragment(['success' => true]);
        $response->assertJson(fn (AssertableJson $json) => $json->has(
            'data',
            fn ($json) => $json
                ->where('image_path', fn ($json) => (bool)preg_match('/^.*'.$image->hashName().'$/', $json, $output))
                ->where('logotype_path', fn ($json) => (bool)preg_match('/^.*'.$image->hashName().'$/', $json, $output))
                ->has('tags', fn (AssertableJson $json) => $json->each(
                        fn (AssertableJson $json) => $json->where('title', fn ($json) =>
                            in_array($json, $tags)
                        )->etc()
                    )
                    ->etc()
                )
                ->where('yt_url', $ytLiveDtoMock->getYtUrl())
                ->where('yt_stream_url', $ytLiveDtoMock->getYTStreamDto()->getYTCdnDto()->getStreamUrl())
                ->where('yt_stream_key', $ytLiveDtoMock->getYTStreamDto()->getYTCdnDto()->getStreamName())
                ->has('trainers', fn (AssertableJson $json) =>
                    $json->each(fn (AssertableJson $json) =>
                        $json->where('id', fn ($json) =>
                            in_array($json, $trainers)
                        )
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            )
            ->etc()
        );

        Event::assertDispatchedTimes(WebinarTrainerAssigned::class, 2);
        Event::assertDispatchedTimes(WebinarTrainerUnassigned::class, 0);
    }

    public function testWebinarStoreRequiredValidation(): void
    {
        $response = $this->actingAs($this->user, 'api')->json('POST', $this->apiUrl);
        $response->assertJsonValidationErrors(['name', 'status', 'description']);
    }
}
