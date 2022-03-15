<?php

namespace EscolaLms\Consultations\Tests\APIs;

use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Events\WebinarAuthorAssigned;
use EscolaLms\Webinar\Events\WebinarAuthorUnassigned;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
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
        Event::fake([WebinarAuthorAssigned::class, WebinarAuthorUnassigned::class]);

        $webinar = Webinar::factory()->make()->toArray();
        $authors = config('auth.providers.users.model')::factory(2)->create()->pluck('id')->toArray();
        $tags = ['Event', 'Webinar'];
        $requestArray = array_merge(
            $webinar,
            ['image' => UploadedFile::fake()->image('image.jpg')],
            ['authors' => $authors],
            ['tags' => $tags]
        );
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
                ->has('image_path')
                ->has('tags', fn (AssertableJson $json) => $json->each(
                        fn (AssertableJson $json) => $json->where('title', fn ($json) =>
                            in_array($json, $tags)
                        )->etc()
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

        Event::assertDispatchedTimes(WebinarAuthorAssigned::class, 2);
        Event::assertDispatchedTimes(WebinarAuthorUnassigned::class, 0);
    }

    public function testWebinarStoreRequiredValidation(): void
    {
        $response = $this->actingAs($this->user, 'api')->json('POST', $this->apiUrl);
        $response->assertJsonValidationErrors(['name', 'status', 'description']);
    }
}
