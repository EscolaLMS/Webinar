<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\User;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
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
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));
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

    public function durationProvider(): array
    {
        return [
            'default' => ['1'],
            'seconds' => ['30 seconds'],
            'minute' => ['1 minute'],
            'minutes' => ['10 minutes'],
            'hour' => ['1 hour'],
            'hours' => ['12 hours'],
            'day' => ['1 day'],
            'days' => ['2 days'],
            'week' => ['1 week'],
            'weeks' => ['2 weeks'],
            'month' => ['1 month'],
            'months' => ['2 months'],
            'year' => ['1 year'],
            'years' => ['1 years'],
        ];
    }

    /**
     * @dataProvider durationProvider
     */
    public function testWebinarListOnlyIncoming(string $duration): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));

        $student = User::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole('student');

        $webinarGTActiveTo = Webinar::factory()->create([
           'name' => 'webinar active to gt no duration',
           'active_from' => now()->subDays(2),
           'active_to' => now()->addDay(),
           'duration' => null,
           'status' => WebinarStatusEnum::PUBLISHED,
        ]);
        $webinarGTActiveTo->users()->sync([$student->getKey()]);
        $webinarGTActiveTo->trainers()->sync([$this->user->getKey()]);

        $webinarGTActiveToDuration = Webinar::factory()->create([
            'name' => 'webinar active to gt duration',
            'active_from' => now()->subDays(2),
            'active_to' => now()->addDay(),
            'duration' => $duration,
            'status' => WebinarStatusEnum::PUBLISHED,
        ]);
        $webinarGTActiveToDuration->users()->sync([$student->getKey()]);
        $webinarGTActiveToDuration->trainers()->sync([$this->user->getKey()]);

        $webinarActiveToDuration = Webinar::factory()->create([
            'name' => 'webinar active to duration',
            'active_from' => now()->subDays(2),
            'active_to' => now(),
            'duration' => $duration,
            'status' => WebinarStatusEnum::PUBLISHED,
        ]);
        $webinarActiveToDuration->users()->sync([$student->getKey()]);
        $webinarActiveToDuration->trainers()->sync([$this->user->getKey()]);

        $webinarLTActiveTo = Webinar::factory()->create([
            'name' => 'webinar active to lt no duration',
            'active_from' => now()->subDays(2),
            'active_to' => now()->subDay(),
            'duration' => null,
            'status' => WebinarStatusEnum::PUBLISHED,
        ]);
        $webinarLTActiveTo->users()->sync([$student->getKey()]);
        $webinarLTActiveTo->trainers()->sync([$this->user->getKey()]);

        $webinarLTActiveToDuration = Webinar::factory()->create([
            'name' => 'webinar active to lt duration',
            'active_from' => now()->subDays(2),
            'active_to' => now()->subDay(),
            'duration' => '1 hour',
            'status' => WebinarStatusEnum::PUBLISHED,
        ]);
        $webinarLTActiveToDuration->users()->sync([$student->getKey()]);
        $webinarLTActiveToDuration->trainers()->sync([$this->user->getKey()]);

        $this
            ->actingAs($this->user, 'api')
            ->json('GET', $this->apiUrl, ['only_incoming' => true])
            ->assertOk()
            ->assertJsonFragment([
                'id' => $webinarGTActiveTo->getKey(),
                'name' => $webinarGTActiveTo->name,
            ])
            ->assertJsonFragment([
                'id' => $webinarGTActiveToDuration->getKey(),
                'name' => $webinarGTActiveToDuration->name,
            ])
            ->assertJsonFragment([
                'id' => $webinarActiveToDuration->getKey(),
                'name' => $webinarActiveToDuration->name,
            ])
            ->assertJsonMissing([
                'id' => $webinarLTActiveTo->getKey(),
                'name' => $webinarLTActiveTo->name,
            ])
            ->assertJsonMissing([
                'id' => $webinarLTActiveToDuration->getKey(),
                'name' => $webinarLTActiveToDuration->name,
            ]);
    }
}
