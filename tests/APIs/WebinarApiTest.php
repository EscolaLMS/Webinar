<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder;
use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Tests\TestCase;
use EscolaLms\Youtube\Services\Contracts\AuthServiceContract;
use EscolaLms\Youtube\Services\Contracts\YoutubeServiceContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;

class WebinarApiTest extends TestCase
{
    use CreatesUsers;
    use DatabaseTransactions;

    private Webinar $webinar;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebinarsPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->webinar = Webinar::factory()->create([
            'name' => 'A Test webinar',
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_from' => now()->subDay(),
            'active_to' => now()->addHour(),
            'duration' => 120
        ]);
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

    public function testWebinarsListWithSorts(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));

        $testWebinar = Webinar::factory()->create([
            'name' => 'B Test webinar',
            'status' => WebinarStatusEnum::DRAFT,
            'active_from' => now()->addDay(),
            'active_to' => now()->addDays(10),
            'duration' => 240
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'status',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $this->webinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $testWebinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'status',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $testWebinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $this->webinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'id',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $testWebinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $this->webinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'id',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $this->webinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $testWebinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'active_from',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $testWebinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $this->webinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'active_from',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $this->webinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $testWebinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'active_to',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $testWebinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $this->webinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'active_to',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $this->webinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $testWebinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'duration',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $this->webinar->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $testWebinar->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/admin/webinars', [
            'order_by' => 'duration',
            'order' => 'DESC',
        ]);
    }

    public function testWebinarsListUnauthorized(): void
    {
        $this->response = $this->json('GET', '/api/admin/webinars');
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

    public function testWebinarAssignableUsersUnauthorized(): void
    {
        $this->response = $this
            ->json('GET', '/api/admin/webinars/users/assignable')
            ->assertUnauthorized();
    }

    public function testWebinarAssignableUsers(): void
    {
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();

        $users = app(UserServiceContract::class)->assignableUsers(WebinarPermissionsEnum::WEBINAR_CREATE);
        assert($users instanceof LengthAwarePaginator);

        $this->response = $this
            ->actingAs($this->user, 'api')
            ->json('GET', '/api/admin/webinars/users/assignable')
            ->assertOk()
            ->assertJsonCount(min($users->total(), $users->perPage()), 'data')
            ->assertJsonMissing([
                'id' => $student->getKey(),
                'email' => $student->email,
            ])
            ->assertJsonFragment([
                'id' => $admin->getKey(),
                'email' => $admin->email,
            ])
            ->assertJsonFragment([
                'id' => $this->user->getKey(),
                'email' => $this->user->email,
            ]);
    }
}
