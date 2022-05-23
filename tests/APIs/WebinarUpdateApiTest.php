<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Enum\ConstantEnum;
use EscolaLms\Webinar\Tests\Mocks\YTLiveDtoMock;
use EscolaLms\Webinar\Events\WebinarTrainerAssigned;
use EscolaLms\Webinar\Events\WebinarTrainerUnassigned;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
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
        $trainers = config('auth.providers.users.model')::factory(2)->create()->pluck('id')->toArray();
        $tags = ['Event', 'Webinar'];
        $requestArray = array_merge(
            $webinarUpdate,
            ['image' => UploadedFile::fake()->image('image.jpg')],
            ['trainers' => $trainers],
            ['tags' => $tags],
        );
        $ytLiveDtoMock = new YTLiveDtoMock();
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('updateYTStream')->zeroOrMoreTimes()->andReturn($ytLiveDtoMock);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect(['s']));

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
    }

    public function testWebinarUpdateTrainers(): void
    {
        Event::fake([WebinarTrainerAssigned::class, WebinarTrainerUnassigned::class]);

        $trainer1 = config('auth.providers.users.model')::factory()->create();
        $trainer2 = config('auth.providers.users.model')::factory()->create();

        $this->webinar->trainers()->sync($trainer1->getKey());

        $ytLiveDtoMock = new YTLiveDtoMock();
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('updateYTStream')->zeroOrMoreTimes()->andReturn($ytLiveDtoMock);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect(['s']));

        $response = $this->actingAs($this->user, 'api')->json(
            'POST',
            $this->apiUrl,
            ['trainers' => [$trainer2->getKey()]]
        );

        $response->assertOk();

        Event::assertDispatched(WebinarTrainerAssigned::class, function (WebinarTrainerAssigned $event) use ($trainer2){
            $this->assertEquals($trainer2->getKey(), $event->getUser()->getKey());
            return true;
        });

        Event::assertDispatched(WebinarTrainerUnassigned::class, function (WebinarTrainerUnassigned $event) use ($trainer1){
            $this->assertEquals($trainer1->getKey(), $event->getUser()->getKey());
            return true;
        });
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

    public function testWebinarUpdateImageAndLogotypeFromExistingFiles(): void
    {
        Storage::fake();
        $directoryPath = ConstantEnum::DIRECTORY . "/{$this->webinar->getKey()}/images";
        UploadedFile::fake()->image('image.jpg')->storeAs($directoryPath, 'image-test.jpg');
        UploadedFile::fake()->image('logotype.jpg')->storeAs($directoryPath, 'logotype-test.jpg');

        $imagePath = "{$directoryPath}/image-test.jpg";
        $logotypePath = "{$directoryPath}/logotype-test.jpg";

        $ytLiveDtoMock = new YTLiveDtoMock();
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('updateYTStream')->zeroOrMoreTimes()->andReturn($ytLiveDtoMock);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect(['s']));

        $response = $this->actingAs($this->user, 'api')->postJson($this->apiUrl, [
            'image' => $imagePath,
            'logotype' => $logotypePath,
        ])->assertOk();

        $data = $response->getData()->data;
        Storage::assertExists($data->image_path);
        Storage::assertExists($data->logotype_path);
    }
}
