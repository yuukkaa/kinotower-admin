<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->words(3, true)),
            'country_id' => Country::factory(),
            'duration' => fake()->numberBetween(80, 180),
            'year_of_issue' => fake()->numberBetween(1980, 2026),
            'age' => fake()->randomElement([0, 6, 12, 16, 18]),
            'link_img' => 'https://example.com/posters/'.fake()->slug().'.jpg',
            'link_kinopoisk' => 'https://www.kinopoisk.ru/film/'.fake()->numberBetween(1000, 999999).'/',
            'link_video' => 'https://example.com/videos/'.fake()->slug().'.mp4',
        ];
    }
}
