<?php

namespace EscolaLms\Webinar\Tests\APIs;

use EscolaLms\Auth\Dtos\Admin\UserAssignableDto;
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
            'active_from' => now()->subDays(2),
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

        $testWebinar->trainers()->sync($this->user);

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

    public function testWebinarsListWithOrderForApi(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));

        Webinar::query()->delete();

        $webinar1 = Webinar::factory()->create([
           'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(1),
        ]);
        $webinar1->trainers()->sync($this->user);

        $webinar2 = Webinar::factory()->create([
           'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(2),
        ]);
        $webinar2->trainers()->sync($this->user);

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/webinars', [
            'order_by' => 'created_at',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $webinar2->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $webinar1->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/webinars', [
            'order_by' => 'created_at',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $webinar1->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $webinar2->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/webinars', [
            'order_by' => 'updated_at',
            'order' => 'DESC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $webinar1->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $webinar2->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('get', '/api/webinars', [
            'order_by' => 'updated_at',
            'order' => 'ASC',
        ]);

        $this->assertTrue($this->response->json('data.0.id') === $webinar2->getKey());
        $this->assertTrue($this->response->json('data.1.id') === $webinar1->getKey());
    }

    public function testWebinarsListWithFilterOnlyIncomingForApi(): void
    {
        $youtubeServiceContract = $this->mock(YoutubeServiceContract::class);
        $youtubeServiceContract->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect([1]));

        $this->webinar->update([
            'active_to' => today()->subDay(),
        ]);

        $webinar = Webinar::factory()->create([
            'name' => 'Incoming webinar',
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_from' => today()->addDay(),
            'active_to' => now()->addDays(2),
            'duration' => 120
        ]);
        $webinar->trainers()->sync($this->user);

        $webinar2 = Webinar::factory()->create([
            'name' => 'Incoming webinar now',
            'status' => WebinarStatusEnum::PUBLISHED,
            'active_from' => today()->subDay(),
            'active_to' => now(),
            'duration' => 120
        ]);
        $webinar2->trainers()->sync($this->user);

        $this->response = $this
            ->actingAs($this->user, 'api')
            ->json('GET', '/api/webinars', ['only_incoming' => true]);
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'id' => $this->webinar->getKey(),
            'name' => $this->webinar->name,
            'active_from' => $this->webinar->active_from,
        ]);
        $this->response->assertJsonMissing([
            'id' => $webinar2->getKey(),
            'name' => $webinar2->name,
            'active_from' => $webinar2->active_from,
        ]);
        $this->response->assertJsonFragment([
            'id' => $webinar->getKey(),
            'name' => $webinar->name,
            'status' => $webinar->status,
            'active_from' => $webinar->active_from,
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

        $dto = UserAssignableDto::instantiateFromArray(['assignable_by' => WebinarPermissionsEnum::WEBINAR_CREATE]);
        $users = app(UserServiceContract::class)->assignableUsersWithCriteria($dto);
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

    public function testWebinarAssignableUsersSearch(): void
    {
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();

        $dto = UserAssignableDto::instantiateFromArray([
            'assignable_by' => WebinarPermissionsEnum::WEBINAR_CREATE,
            'search' => $admin->email,
        ]);
        $users = app(UserServiceContract::class)->assignableUsersWithCriteria($dto);
        assert($users instanceof LengthAwarePaginator);

        $this->response = $this
            ->actingAs($this->user, 'api')
            ->json('GET', '/api/admin/webinars/users/assignable', ['search' => $admin->email])
            ->assertOk()
            ->assertJsonCount(min($users->total(), $users->perPage()), 'data')
            ->assertJsonFragment([
                'id' => $admin->getKey(),
                'email' => $admin->email,
            ])
            ->assertJsonMissing([
                'id' => $student->getKey(),
                'email' => $student->email,
            ])
            ->assertJsonMissing([
                'id' => $this->user->getKey(),
                'email' => $this->user->email,
            ]);
    }

    public function testWebinarListOwn(): void
    {
        $webinarService = $this->mock(YoutubeServiceContract::class);
        $webinarService->shouldReceive('getYtLiveStream')->zeroOrMoreTimes()->andReturn(collect());
        Webinar::factory()->count(3)->create();

        $this
            ->actingAs($this->user, 'api')
            ->getJson('/api/admin/webinars')
            ->assertJsonCount(1, 'data');

        $this
            ->actingAs($this->makeAdmin(), 'api')
            ->getJson('/api/admin/webinars')
            ->assertJsonCount(4, 'data');
    }
}
