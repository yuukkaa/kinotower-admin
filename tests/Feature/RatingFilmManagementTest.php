<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Film;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingFilmManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_ratings(): void
    {
        $response = $this->get(route('admin.ratings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_all_ratings_and_filter_by_film(): void
    {
        $userA = User::factory()->create(['fio' => 'Ольга Кузнецова']);
        $userB = User::factory()->create(['fio' => 'Дмитрий Волков']);
        $filmA = Film::factory()->create(['name' => 'Начало']);
        $filmB = Film::factory()->create(['name' => 'Дюна']);

        Rating::create([
            'film_id' => $filmA->id,
            'user_id' => $userA->id,
            'ball' => 5,
        ]);

        Rating::create([
            'film_id' => $filmB->id,
            'user_id' => $userB->id,
            'ball' => 4,
        ]);

        $allResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ratings.index'));

        $allResponse->assertOk();
        $allResponse->assertSee('Ольга Кузнецова');
        $allResponse->assertSee('Дмитрий Волков');

        $filteredResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ratings.index', ['film_id' => $filmA->id]));

        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Ольга Кузнецова');
        $filteredResponse->assertDontSee('Дмитрий Волков');

        $filmRouteResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.ratings.index', $filmB));

        $filmRouteResponse->assertOk();
        $filmRouteResponse->assertSee('Дмитрий Волков');
        $filmRouteResponse->assertDontSee('Ольга Кузнецова');
    }

    public function test_admin_can_delete_rating(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $rating = Rating::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'ball' => 5,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.ratings.destroy', $rating));

        $response->assertRedirect(route('admin.ratings.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    }
}
