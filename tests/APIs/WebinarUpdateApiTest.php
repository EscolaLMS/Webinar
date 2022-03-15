<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Events\WebinarAuthorAssigned;
use EscolaLms\Webinar\Events\WebinarAuthorUnassigned;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

class WebinarUpdateApiTest extends TestCase
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

    public function testWebinarUpdateAuthors(): void
    {
        Event::fake([WebinarAuthorAssigned::class, WebinarAuthorUnassigned::class]);

        $author1 = config('auth.providers.users.model')::factory()->create();
        $author2 = config('auth.providers.users.model')::factory()->create();

        $this->webinar->authors()->sync($author1->getKey());

        $response = $this->actingAs($this->user, 'api')->json(
            'POST',
            $this->apiUrl,
            ['authors' => [$author2->getKey()]]
        );

        $response->assertOk();

        Event::assertDispatched(WebinarAuthorAssigned::class, function (WebinarAuthorAssigned $event) use ($author2){
            $this->assertEquals($author2->getKey(), $event->getUser()->getKey());
            return true;
        });

        Event::assertDispatched(WebinarAuthorUnassigned::class, function (WebinarAuthorUnassigned $event) use ($author1){
            $this->assertEquals($author1->getKey(), $event->getUser()->getKey());
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
}
