<?php

namespace Tests\Feature\Api;

use App\Models\Film;
use App\Models\Review;
use App\Models\User;
use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list_and_delete_reviews(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create(['name' => 'Black Mirror']);
        $token = app(ApiTokenService::class)->createToken($user);

        // Create review
        $create = $this->withToken($token)->postJson("/api/v1/users/{$user->id}/reviews", [
            'film_id' => $film->id,
            'message' => 'Very Good!',
        ]);

        $create->assertCreated()
            ->assertJsonPath('film.id', $film->id)
            ->assertJsonPath('film.name', 'Black Mirror')
            ->assertJsonPath('message', 'Very Good!')
            ->assertJsonPath('is_approved', 0);

        $reviewId = $create->json('id');

        // List user reviews
        $list = $this->withToken($token)->getJson("/api/v1/users/{$user->id}/reviews");
        $list->assertOk()
            ->assertJsonCount(1, 'reviews')
            ->assertJsonPath('reviews.0.id', $reviewId)
            ->assertJsonPath('reviews.0.film.name', 'Black Mirror')
            ->assertJsonPath('reviews.0.message', 'Very Good!')
            ->assertJsonPath('reviews.0.is_approved', 0);

        // Delete review
        $delete = $this->withToken($token)->deleteJson("/api/v1/users/{$user->id}/reviews/{$reviewId}");
        $delete->assertNoContent();
        $this->assertSoftDeleted('reviews', ['id' => $reviewId]);
    }

    public function test_user_reviews_return_404_for_missing_user_or_review(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokenService::class)->createToken($user);

        $this->withToken($token)
            ->getJson('/api/v1/users/999999/reviews')
            ->assertNotFound()
            ->assertExactJson(['message' => 'User not found']);

        $this->withToken($token)
            ->deleteJson('/api/v1/users/999999/reviews/1')
            ->assertNotFound()
            ->assertExactJson(['message' => 'User not found']);

        $this->withToken($token)
            ->deleteJson("/api/v1/users/{$user->id}/reviews/999999")
            ->assertNotFound()
            ->assertExactJson(['message' => 'Review not found']);
    }

    public function test_create_review_validates_payload(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokenService::class)->createToken($user);

        $this->withToken($token)
            ->postJson("/api/v1/users/{$user->id}/reviews", [
                'film_id' => 999999,
                'message' => 'Hi',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['film_id', 'message']);
    }
}
