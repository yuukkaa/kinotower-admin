<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Gender;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_films_returns_paginated_filtered_and_sorted_list(): void
    {
        $usa = Country::factory()->create(['name' => 'США']);
        $japan = Country::factory()->create(['name' => 'Япония']);

        $sciFi = Category::factory()->create(['name' => 'Фантастика']);
        $anime = Category::factory()->create(['name' => 'Аниме']);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $filmA = Film::factory()->create([
            'name' => 'Alpha Movie',
            'country_id' => $usa->id,
            'year_of_issue' => 2010,
            'link_img' => null,
            'link_kinopoisk' => null,
        ]);
        $filmA->categories()->attach($sciFi->id);

        $filmB = Film::factory()->create([
            'name' => 'Beta Movie',
            'country_id' => $japan->id,
            'year_of_issue' => 2020,
        ]);
        $filmB->categories()->attach($anime->id);

        $filmC = Film::factory()->create([
            'name' => 'Gamma Alpha',
            'country_id' => $usa->id,
            'year_of_issue' => 2015,
        ]);
        $filmC->categories()->attach($sciFi->id);

        Rating::create(['film_id' => $filmA->id, 'user_id' => $userA->id, 'ball' => 4]);
        Rating::create(['film_id' => $filmA->id, 'user_id' => $userB->id, 'ball' => 5]); // avg 4.5
        Rating::create(['film_id' => $filmB->id, 'user_id' => $userA->id, 'ball' => 5]); // avg 5.0
        Rating::create(['film_id' => $filmC->id, 'user_id' => $userA->id, 'ball' => 2]); // avg 2.0

        Review::create([
            'film_id' => $filmA->id,
            'user_id' => $userA->id,
            'message' => 'Great movie!',
            'is_approved' => true,
        ]);

        // Default list + pagination
        $response = $this->getJson('/api/v1/films?page=1&size=2');
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('page', 1)
            ->assertJsonPath('size', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('films.0.name', 'Alpha Movie')
            ->assertJsonPath('films.0.ratingAvg', 4.5)
            ->assertJsonPath('films.0.reviewCount', 1)
            ->assertJsonPath('films.0.country.name', 'США')
            ->assertJsonPath('films.0.categories.0.name', 'Фантастика')
            ->assertJsonPath('films.0.link_img', null)
            ->assertJsonPath('films.0.link_kinopoisk', null)
            ->assertJsonPath('films.1.name', 'Beta Movie');

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/',
            $response->json('films.0.created_at')
        );

        // Filter by country + category + search
        $filtered = $this->getJson("/api/v1/films?country={$usa->id}&category={$sciFi->id}&search=Gamma");
        $filtered->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('size', 1)
            ->assertJsonPath('films.0.id', $filmC->id);

        // Sort by year desc
        $byYear = $this->getJson('/api/v1/films?sortBy=year&sortDir=desc');
        $byYear->assertOk()
            ->assertJsonPath('films.0.id', $filmB->id)
            ->assertJsonPath('films.1.id', $filmC->id)
            ->assertJsonPath('films.2.id', $filmA->id);

        // Sort by rating desc
        $byRating = $this->getJson('/api/v1/films?sortBy=rating&sortDir=desc');
        $byRating->assertOk()
            ->assertJsonPath('films.0.id', $filmB->id)
            ->assertJsonPath('films.1.id', $filmA->id)
            ->assertJsonPath('films.2.id', $filmC->id);
    }

    public function test_get_film_by_id_returns_film_or_404(): void
    {
        $country = Country::factory()->create(['name' => 'США']);
        $category = Category::factory()->create(['name' => 'Драма']);
        $film = Film::factory()->create([
            'name' => 'Black Mirror',
            'country_id' => $country->id,
        ]);
        $film->categories()->attach($category->id);

        $response = $this->getJson("/api/v1/films/{$film->id}");
        $response->assertOk()
            ->assertJsonPath('id', $film->id)
            ->assertJsonPath('name', 'Black Mirror')
            ->assertJsonPath('country.id', $country->id)
            ->assertJsonPath('categories.0.id', $category->id)
            ->assertJsonPath('ratingAvg', 0)
            ->assertJsonPath('reviewCount', 0);

        $notFound = $this->getJson('/api/v1/films/999999');
        $notFound->assertNotFound()
            ->assertExactJson(['message' => 'Film not found']);
    }

    public function test_get_categories_returns_categories_with_parent_and_film_count(): void
    {
        $parent = Category::factory()->create(['name' => 'Action', 'parent_id' => null]);
        $child = Category::factory()->create(['name' => 'Western', 'parent_id' => $parent->id]);
        $film = Film::factory()->create();
        $film->categories()->attach($child->id);

        $response = $this->getJson('/api/v1/categories');
        $response->assertOk()
            ->assertJsonFragment([
                'id' => $parent->id,
                'name' => 'Action',
                'parentCategory' => null,
                'filmCount' => 0,
            ])
            ->assertJsonFragment([
                'id' => $child->id,
                'name' => 'Western',
                'parentCategory' => [
                    'id' => $parent->id,
                    'name' => 'Action',
                ],
                'filmCount' => 1,
            ]);
    }

    public function test_get_countries_returns_countries_with_film_count(): void
    {
        $country = Country::factory()->create(['name' => 'Pakistan']);
        Film::factory()->count(2)->create(['country_id' => $country->id]);

        $response = $this->getJson('/api/v1/countries');
        $response->assertOk()
            ->assertJsonFragment([
                'id' => $country->id,
                'name' => 'Pakistan',
                'filmCount' => 2,
            ]);
    }

    public function test_get_genders_returns_all_genders(): void
    {
        $male = Gender::factory()->create(['name' => 'Мужской']);
        $female = Gender::factory()->create(['name' => 'Женский']);

        $response = $this->getJson('/api/v1/genders');
        $response->assertOk()
            ->assertExactJson([
                'genders' => [
                    ['id' => $male->id, 'name' => 'Мужской'],
                    ['id' => $female->id, 'name' => 'Женский'],
                ],
            ]);
    }

    public function test_get_film_reviews_returns_only_approved_reviews_or_404(): void
    {
        $user = User::factory()->create(['fio' => 'Ivanov Ivan']);
        $film = Film::factory()->create();

        $approved = Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Very Good!',
            'is_approved' => true,
        ]);

        Review::create([
            'film_id' => $film->id,
            'user_id' => $user->id,
            'message' => 'Not approved yet',
            'is_approved' => false,
        ]);

        $response = $this->getJson("/api/v1/films/{$film->id}/reviews");
        $response->assertOk()
            ->assertJsonCount(1, 'reviews')
            ->assertJsonPath('reviews.0.id', $approved->id)
            ->assertJsonPath('reviews.0.user.id', $user->id)
            ->assertJsonPath('reviews.0.user.fio', 'Ivanov Ivan')
            ->assertJsonPath('reviews.0.message', 'Very Good!');

        $notFound = $this->getJson('/api/v1/films/999999/reviews');
        $notFound->assertNotFound()
            ->assertExactJson(['message' => 'Film not found']);
    }
}
