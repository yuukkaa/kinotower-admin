<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Film;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFilmManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_reviews(): void
    {
        $response = $this->get(route('admin.reviews.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_all_reviews_and_filter_by_film(): void
    {
        $user = User::factory()->create(['fio' => 'Мария Иванова']);
        $filmA = Film::factory()->create(['name' => 'Интерстеллар']);
        $filmB = Film::factory()->create(['name' => 'Матрица']);

        Review::create([
            'film_id' => $filmA->id,
            'user_id' => $user->id,
            'message' => 'Отличный научно-фантастический фильм',
            'is_approved' => false,
        ]);

        Review::create([
            'film_id' => $filmB->id,
            'user_id' => $user->id,
            'message' => 'Культовый боевик',
            'is_approved' => true,
        ]);

        $allResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.reviews.index'));

        $allResponse->assertOk();
        $allResponse->assertSee('Отличный научно-фантастический фильм');
        $allResponse->assertSee('Культовый боевик');

        $filteredResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.reviews.index', ['film_id' => $filmA->id]));

        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Отличный научно-фантастический фильм');
        $filteredResponse->assertDontSee('Культовый боевик');

        $filmRouteResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.reviews.index', $filmB));

        $filmRouteResponse->assertOk();
        $filmRouteResponse->assertSee('Культовый боевик');
        $filmRouteResponse->assertDontSee('Отличный научно-фантастический фильм');
    }

    public function test_admin_can_approve_review(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $review = Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Ждёт одобрения',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.reviews.approved', $review));

        $response->assertRedirect(route('admin.reviews.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'is_approved' => 1,
        ]);
    }

    public function test_admin_can_delete_review(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $review = Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Удаляемый отзыв',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect(route('admin.reviews.index'));
        $response->assertSessionHas('status');
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
