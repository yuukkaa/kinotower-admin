<?php

namespace Tests\Feature\Api;

use App\Models\Film;
use App\Models\User;
use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRatingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list_and_delete_ratings(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create(['name' => 'Black Mirror']);
        $token = app(ApiTokenService::class)->createToken($user);

        // Create rating
        $create = $this->withToken($token)->postJson("/api/v1/users/{$user->id}/ratings", [
            'film_id' => $film->id,
            'ball' => 5,
        ]);

        $create->assertCreated()
            ->assertJsonPath('film.id', $film->id)
            ->assertJsonPath('film.name', 'Black Mirror')
            ->assertJsonPath('score', 5);

        $ratingId = $create->json('id');

        // Duplicate rating returns 401 Score exist
        $duplicate = $this->withToken($token)->postJson("/api/v1/users/{$user->id}/ratings", [
            'film_id' => $film->id,
            'ball' => 4,
        ]);

        $duplicate->assertUnauthorized()
            ->assertExactJson([
                'status' => 'invalid',
                'message' => 'Score exist',
            ]);

        // List user ratings
        $list = $this->withToken($token)->getJson("/api/v1/users/{$user->id}/ratings");
        $list->assertOk()
            ->assertJsonCount(1, 'ratings')
            ->assertJsonPath('ratings.0.id', $ratingId)
            ->assertJsonPath('ratings.0.film.name', 'Black Mirror')
            ->assertJsonPath('ratings.0.score', 5);

        // Delete rating
        $delete = $this->withToken($token)->deleteJson("/api/v1/users/{$user->id}/ratings/{$ratingId}");
        $delete->assertNoContent();
        $this->assertDatabaseMissing('ratings', ['id' => $ratingId]);
    }

    public function test_user_ratings_return_404_for_missing_user_or_rating(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokenService::class)->createToken($user);

        $this->withToken($token)
            ->getJson('/api/v1/users/999999/ratings')
            ->assertNotFound()
            ->assertExactJson(['message' => 'User not found']);

        $this->withToken($token)
            ->deleteJson('/api/v1/users/999999/ratings/1')
            ->assertNotFound()
            ->assertExactJson(['message' => 'User not found']);

        $this->withToken($token)
            ->deleteJson("/api/v1/users/{$user->id}/ratings/999999")
            ->assertNotFound()
            ->assertExactJson(['message' => 'Rating not found']);
    }

    public function test_create_rating_validates_payload(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokenService::class)->createToken($user);

        $this->withToken($token)
            ->postJson("/api/v1/users/{$user->id}/ratings", [
                'film_id' => 999999,
                'ball' => 10,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['film_id', 'ball']);
    }
}
