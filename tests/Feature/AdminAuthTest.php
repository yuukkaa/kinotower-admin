<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Gender;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Kino');
        $response->assertSee('Войти');
    }

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@kinotower.ru',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@kinotower.ru',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');

        $dashboard = $this->get('/admin');
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Панель управления');
        $dashboard->assertSee($admin->name);
    }

    public function test_admin_cannot_login_with_invalid_password(): void
    {
        Admin::factory()->create([
            'email' => 'admin@kinotower.ru',
            'password' => 'secret123',
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@kinotower.ru',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_can_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_seeders_and_model_relationships_work(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('genders', ['name' => 'Мужской']);
        $this->assertDatabaseHas('countries', ['name' => 'Россия']);
        $this->assertDatabaseHas('categories', ['name' => 'Боевик']);
        $this->assertDatabaseHas('admins', ['email' => 'admin@kinotower.ru']);

        $gender = Gender::first();
        $country = Country::first();
        $category = Category::whereNull('parent_id')->first();

        $user = User::factory()->create(['gender_id' => $gender->id]);
        $film = Film::create([
            'name' => 'Интерстеллар',
            'country_id' => $country->id,
            'duration' => 169,
            'year_of_issue' => 2014,
            'age' => 12,
            'link_img' => 'https://example.com/poster.jpg',
            'link_kinopoisk' => 'https://www.kinopoisk.ru/film/258687/',
            'link_video' => 'https://example.com/video.mp4',
        ]);
        $film->categories()->attach($category->id);

        $review = Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Отличный фильм!',
        ]);

        $rating = Rating::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'ball' => 10,
        ]);

        $this->assertEquals($gender->id, $user->gender->id);
        $this->assertEquals($country->id, $film->country->id);
        $this->assertTrue($film->categories->contains($category));
        $this->assertEquals($film->id, $review->film->id);
        $this->assertEquals($user->id, $rating->user->id);

        // Verify SoftDeletes
        $film->delete();
        $this->assertSoftDeleted('films', ['id' => $film->id]);
        $category->delete();
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $review->delete();
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
