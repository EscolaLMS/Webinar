<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Models\User;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use EscolaLms\Webinar\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;

class WebinarListForUserTest extends TestCase
{
    use DatabaseTransactions;
    private string $apiUrl;
    private Collection $webinars;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebinarsPermissionSeeder::class);

        $this->user = User::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->apiUrl = 'api/webinars/me';
    }

    private function initVariable(): void
    {
        $student = User::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole('student');
        $this->webinars = Webinar::factory(3)->create()->each(function (Webinar $webinar) use ($student) {
            $webinar->users()->sync([$student->getKey()]);
            $webinar->trainers()->sync([$this->user->getKey()]);
        });
    }

    public function testWebinarListForUser(): void
    {
        $this->initVariable();
        $this->response = $this->actingAs($this->user, 'api')->json('GET', $this->apiUrl);
        $consArray = $this->webinars->pluck('id')->toArray();
        $this->response->assertJson(fn (AssertableJson $json) => $json->has(
            'data',
                fn ($json) => $json->each(fn (AssertableJson $json) =>
                    $json->where('id', fn ($json) =>
                        in_array($json, $consArray)
                    )
                    ->has('in_coming')
                    ->has('is_ended')
                    ->has('is_started')
                    ->etc()
                )
                ->etc()
            )
            ->etc()
        );
        $this->response->assertOk();
    }
}
