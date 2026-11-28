<?php

namespace Tests\Feature\Api;

use App\Models\Film;
use App\Models\Gender;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_user_endpoints_require_bearer_token(): void
    {
        $user = User::factory()->create();

        $this->getJson("/api/v1/users/{$user->id}")->assertUnauthorized();
        $this->putJson('/api/v1/users', [])->assertUnauthorized();
        $this->deleteJson('/api/v1/users')->assertUnauthorized();
    }

    public function test_get_user_returns_profile_with_counts_or_404(): void
    {
        $gender = Gender::factory()->create(['name' => 'Мужской']);
        $user = User::factory()->create([
            'fio' => 'Ivanov Ivan',
            'email' => 'ivanov@ivan.kz',
            'birthday' => '2006-04-15',
            'gender_id' => $gender->id,
        ]);

        $film = Film::factory()->create();
        Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Nice film',
            'is_approved' => true,
        ]);
        Rating::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'ball' => 5,
        ]);

        $token = app(ApiTokenService::class)->createToken($user);

        $response = $this->withToken($token)->getJson("/api/v1/users/{$user->id}");
        $response->assertOk()
            ->assertExactJson([
                'id' => $user->id,
                'fio' => 'Ivanov Ivan',
                'email' => 'ivanov@ivan.kz',
                'birthday' => '2006-04-15',
                'gender' => [
                    'id' => $gender->id,
                    'name' => 'Мужской',
                ],
                'reviewCount' => 1,
                'ratingCount' => 1,
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/users/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'User not found']);
    }

    public function test_user_can_update_own_profile(): void
    {
        $genderA = Gender::factory()->create(['name' => 'Мужской']);
        $genderB = Gender::factory()->create(['name' => 'Женский']);

        $user = User::factory()->create([
            'fio' => 'Old Name',
            'email' => 'old@example.com',
            'birthday' => '1999-01-01',
            'gender_id' => $genderA->id,
        ]);

        $token = app(ApiTokenService::class)->createToken($user);

        $response = $this->withToken($token)->putJson('/api/v1/users', [
            'fio' => 'New Name',
            'email' => 'new@example.com',
            'birthday' => '2000-05-10',
            'gender_id' => $genderB->id,
        ]);

        $response->assertOk()
            ->assertExactJson(['status' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fio' => 'New Name',
            'email' => 'new@example.com',
            'gender_id' => $genderB->id,
        ]);
    }

    public function test_user_can_delete_own_account(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokenService::class)->createToken($user);

        $response = $this->withToken($token)->deleteJson('/api/v1/users');
        $response->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $this->withToken($token)
            ->getJson("/api/v1/users/{$user->id}")
            ->assertUnauthorized();
    }
}
