<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Film;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryFilmCrudTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_film_categories(): void
    {
        $film = Film::factory()->create();

        $response = $this->get(route('admin.films.categories.index', $film));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_film_categories_list(): void
    {
        $film = Film::factory()->create(['name' => 'Интерстеллар']);
        $sciFi = Category::factory()->create(['name' => 'Фантастика']);
        $comedy = Category::factory()->create(['name' => 'Комедия']);

        $film->categories()->attach($sciFi->id);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.categories.index', $film));

        $response->assertOk();
        $response->assertSee('Интерстеллар');
        $response->assertSee('Фантастика');
        $response->assertDontSee('Комедия');
    }

    public function test_admin_can_add_category_to_film(): void
    {
        $film = Film::factory()->create(['name' => 'Матрица']);
        $existing = Category::factory()->create(['name' => 'Боевик']);
        $available = Category::factory()->create(['name' => 'Фантастика']);

        $film->categories()->attach($existing->id);

        $createPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.categories.create', $film));

        $createPage->assertOk();
        $createPage->assertSee('Фантастика');
        $createPage->assertDontSee('Боевик');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.films.categories.store', $film), [
                'category_id' => $available->id,
            ]);

        $response->assertRedirect(route('admin.films.categories.index', $film));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('category_films', [
            'film_id' => $film->id,
            'category_id' => $available->id,
        ]);
    }

    public function test_duplicate_category_cannot_be_added_to_film(): void
    {
        $film = Film::factory()->create();
        $category = Category::factory()->create(['name' => 'Драма']);
        $film->categories()->attach($category->id);

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.films.categories.create', $film))
            ->post(route('admin.films.categories.store', $film), [
                'category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.films.categories.create', $film));
        $response->assertSessionHasErrors('category_id');
    }

    public function test_admin_can_remove_category_from_film(): void
    {
        $film = Film::factory()->create(['name' => 'Начало']);
        $category = Category::factory()->create(['name' => 'Триллер']);
        $film->categories()->attach($category->id);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.films.categories.destroy', [$film, $category]));

        $response->assertRedirect(route('admin.films.categories.index', $film));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('category_films', [
            'film_id' => $film->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }
}
