<?php

namespace Database\Factories;

use App\Models\Gender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'fio' => fake()->name(),
            'birthday' => fake()->date('Y-m-d', '-18 years'),
            'gender_id' => Gender::factory(),
            'email' => mb_substr(fake()->unique()->safeEmail(), 0, 50),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
