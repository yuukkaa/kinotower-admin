<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmCrudTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_films_index(): void
    {
        $response = $this->get(route('admin.films.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_paginated_films_list(): void
    {
        $country = Country::factory()->create(['name' => 'США']);
        $films = Film::factory()->count(16)->create(['country_id' => $country->id]);

        $page1 = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.index'));

        $page1->assertOk();
        $page1->assertSee('Список фильмов');
        $page1->assertSee($films->first()->name);
        $page1->assertDontSee($films->last()->name);

        $page2 = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.index', ['page' => 2]));

        $page2->assertOk();
        $page2->assertSee($films->last()->name);
    }

    public function test_admin_can_filter_films_by_country_and_category(): void
    {
        $russia = Country::factory()->create(['name' => 'Россия']);
        $usa = Country::factory()->create(['name' => 'США']);

        $sciFi = Category::factory()->create(['name' => 'Фантастика']);
        $drama = Category::factory()->create(['name' => 'Драма']);

        $filmRuSciFi = Film::factory()->create([
            'name' => 'Притяжение',
            'country_id' => $russia->id,
        ]);
        $filmRuSciFi->categories()->attach($sciFi->id);

        $filmUsDrama = Film::factory()->create([
            'name' => 'Побег из Шоушенка',
            'country_id' => $usa->id,
        ]);
        $filmUsDrama->categories()->attach($drama->id);

        $byCountry = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.index', ['country_id' => $russia->id]));

        $byCountry->assertOk();
        $byCountry->assertSee('Притяжение');
        $byCountry->assertDontSee('Побег из Шоушенка');

        $byCategory = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.index', ['category_id' => $drama->id]));

        $byCategory->assertOk();
        $byCategory->assertSee('Побег из Шоушенка');
        $byCategory->assertDontSee('Притяжение');

        $byBoth = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.index', [
                'country_id' => $russia->id,
                'category_id' => $drama->id,
            ]));

        $byBoth->assertOk();
        $byBoth->assertDontSee('Притяжение');
        $byBoth->assertDontSee('Побег из Шоушенка');
    }

    public function test_admin_can_create_film(): void
    {
        $country = Country::factory()->create(['name' => 'США']);

        $createPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.create'));

        $createPage->assertOk();
        $createPage->assertSee('Новый фильм');
        $createPage->assertSee('США');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.films.store'), [
                'name' => 'Интерстеллар',
                'country_id' => $country->id,
                'duration' => 169,
                'year_of_issue' => 2014,
                'age' => 12,
                'link_img' => 'https://example.com/poster.jpg',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/258687/',
                'link_video' => 'https://example.com/video.mp4',
            ]);

        $response->assertRedirect(route('admin.films.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('films', [
            'name' => 'Интерстеллар',
            'country_id' => $country->id,
            'duration' => 169,
            'year_of_issue' => 2014,
            'age' => 12,
        ]);
    }

    public function test_film_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.films.create'))
            ->post(route('admin.films.store'), []);

        $response->assertRedirect(route('admin.films.create'));
        $response->assertSessionHasErrors([
            'name',
            'country_id',
            'duration',
            'year_of_issue',
            'age',
            'link_video',
        ]);
    }

    public function test_admin_can_edit_and_update_film(): void
    {
        $oldCountry = Country::factory()->create(['name' => 'США']);
        $newCountry = Country::factory()->create(['name' => 'Великобритания']);
        $film = Film::factory()->create([
            'name' => 'Начало',
            'country_id' => $oldCountry->id,
            'duration' => 148,
            'year_of_issue' => 2010,
            'age' => 12,
        ]);

        $editPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.films.edit', $film));

        $editPage->assertOk();
        $editPage->assertSee('Изменение фильма');
        $editPage->assertSee('Начало');

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.films.update', $film), [
                'name' => 'Начало (Режиссёрская версия)',
                'country_id' => $newCountry->id,
                'duration' => 155,
                'year_of_issue' => 2011,
                'age' => 16,
                'link_img' => 'https://example.com/new-poster.jpg',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/447301/',
                'link_video' => 'https://example.com/new-video.mp4',
            ]);

        $response->assertRedirect(route('admin.films.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'name' => 'Начало (Режиссёрская версия)',
            'country_id' => $newCountry->id,
            'duration' => 155,
            'year_of_issue' => 2011,
            'age' => 16,
        ]);
    }

    public function test_admin_can_delete_film(): void
    {
        $film = Film::factory()->create(['name' => 'Матрица']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.films.destroy', $film));

        $response->assertRedirect(route('admin.films.index'));
        $response->assertSessionHas('status');
        $this->assertSoftDeleted('films', ['id' => $film->id]);
    }
}
