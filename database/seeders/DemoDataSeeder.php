<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Gender;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $male = Gender::firstOrCreate(['name' => 'Мужской']);
        $female = Gender::firstOrCreate(['name' => 'Женский']);

        $usa = Country::firstOrCreate(['name' => 'США']);
        $southKorea = Country::firstOrCreate(['name' => 'Южная Корея']);
        $japan = Country::firstOrCreate(['name' => 'Япония']);

        $filmsData = [
            [
                'name' => 'Интерстеллар',
                'country_id' => $usa->id,
                'duration' => 169,
                'year_of_issue' => 2014,
                'age' => 12,
                'link_img' => 'https://upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/258687/',
                'link_video' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
                'categories' => ['Фантастика', 'Драма', 'Космическая опера'],
            ],
            [
                'name' => 'Начало',
                'country_id' => $usa->id,
                'duration' => 148,
                'year_of_issue' => 2010,
                'age' => 12,
                'link_img' => 'https://upload.wikimedia.org/wikipedia/en/2/2e/Inception_%282010%29_theatrical_poster.jpg',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/447301/',
                'link_video' => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
                'categories' => ['Фантастика', 'Триллер', 'Боевик'],
            ],
            [
                'name' => 'Паразиты',
                'country_id' => $southKorea->id,
                'duration' => 132,
                'year_of_issue' => 2019,
                'age' => 18,
                'link_img' => 'https://upload.wikimedia.org/wikipedia/en/5/53/Parasite_%282019_film%29.png',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/1043758/',
                'link_video' => 'https://www.youtube.com/watch?v=5xH0HfJHsaY',
                'categories' => ['Триллер', 'Драма', 'Черная комедия'],
            ],
            [
                'name' => 'Унесённые призраками',
                'country_id' => $japan->id,
                'duration' => 125,
                'year_of_issue' => 2001,
                'age' => 12,
                'link_img' => 'https://upload.wikimedia.org/wikipedia/en/d/db/Spirited_Away_Japanese_poster.png',
                'link_kinopoisk' => 'https://www.kinopoisk.ru/film/370/',
                'link_video' => 'https://www.youtube.com/watch?v=ByXuk9QqQkk',
                'categories' => ['Мультфильм', 'Аниме'],
            ],
        ];

        $films = [];
        foreach ($filmsData as $data) {
            $categoryNames = $data['categories'];
            unset($data['categories']);

            $film = Film::withTrashed()->firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($film->trashed()) {
                $film->restore();
                $film->update($data);
            }

            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id')->all();
            if ($categoryIds !== []) {
                $film->categories()->syncWithoutDetaching($categoryIds);
            }

            $films[$data['name']] = $film;
        }

        $usersData = [
            [
                'fio' => 'Иванов Алексей Сергеевич',
                'email' => 'alexey.ivanov@example.com',
                'birthday' => '1995-04-12',
                'gender_id' => $male->id,
                'password' => 'password123',
                'deleted' => false,
            ],
            [
                'fio' => 'Петрова Мария Дмитриевна',
                'email' => 'maria.petrova@example.com',
                'birthday' => '1998-09-23',
                'gender_id' => $female->id,
                'password' => 'password123',
                'deleted' => false,
            ],
            [
                'fio' => 'Смирнов Дмитрий Андреевич',
                'email' => 'dmitry.smirnov@example.com',
                'birthday' => '1991-01-17',
                'gender_id' => $male->id,
                'password' => 'password123',
                'deleted' => false,
            ],
            [
                'fio' => 'Козлова Елена Викторовна',
                'email' => 'elena.kozlova@example.com',
                'birthday' => '2000-06-05',
                'gender_id' => $female->id,
                'password' => 'password123',
                'deleted' => true,
            ],
            [
                'fio' => 'Волков Артём Игоревич',
                'email' => 'artem.volkov@example.com',
                'birthday' => '1989-11-30',
                'gender_id' => $male->id,
                'password' => 'password123',
                'deleted' => true,
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $shouldDelete = $data['deleted'];
            unset($data['deleted']);

            $user = User::withTrashed()->firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            if ($shouldDelete && ! $user->trashed()) {
                $user->delete();
            }

            $users[$data['email']] = $user;
        }

        $reviewsData = [
            [
                'film' => 'Интерстеллар',
                'user' => 'alexey.ivanov@example.com',
                'message' => 'Невероятная музыка Ханса Циммера и потрясающая визуализация чёрной дыры Гаргантюа. Один из лучших научно-фантастических фильмов.',
                'is_approved' => true,
            ],
            [
                'film' => 'Интерстеллар',
                'user' => 'maria.petrova@example.com',
                'message' => 'Очень трогательная история отца и дочери на фоне космического путешествия. В конце невозможно сдержать слёзы.',
                'is_approved' => false,
            ],
            [
                'film' => 'Начало',
                'user' => 'dmitry.smirnov@example.com',
                'message' => 'Блестящий многоуровневый сюжет со снами внутри снов. Открытый финал с волчком до сих пор вызывает споры.',
                'is_approved' => true,
            ],
            [
                'film' => 'Начало',
                'user' => 'alexey.ivanov@example.com',
                'message' => 'Сцена драки в вращающемся коридоре отеля снята гениально, пересматриваю фильм каждый год.',
                'is_approved' => false,
            ],
            [
                'film' => 'Паразиты',
                'user' => 'maria.petrova@example.com',
                'message' => 'Мастерское сочетание социальной сатиры и напряжённого триллера. Поворот в середине фильма полностью меняет тон истории.',
                'is_approved' => true,
            ],
            [
                'film' => 'Паразиты',
                'user' => 'elena.kozlova@example.com',
                'message' => 'Отличная актёрская игра и внимание к каждой детали в доме семьи Пак. Заслуженный «Оскар».',
                'is_approved' => false,
            ],
            [
                'film' => 'Унесённые призраками',
                'user' => 'dmitry.smirnov@example.com',
                'message' => 'Волшебная атмосфера купален Юбабы и удивительный мир Миядзаки. Шедевр анимации на все времена.',
                'is_approved' => false,
            ],
        ];

        foreach ($reviewsData as $data) {
            Review::withTrashed()->firstOrCreate(
                [
                    'film_id' => $films[$data['film']]->id,
                    'user_id' => $users[$data['user']]->id,
                    'message' => $data['message'],
                ],
                [
                    'is_approved' => $data['is_approved'],
                ]
            );
        }

        $ratingsData = [
            ['film' => 'Интерстеллар', 'user' => 'alexey.ivanov@example.com', 'ball' => 5],
            ['film' => 'Интерстеллар', 'user' => 'maria.petrova@example.com', 'ball' => 5],
            ['film' => 'Интерстеллар', 'user' => 'dmitry.smirnov@example.com', 'ball' => 4],
            ['film' => 'Начало', 'user' => 'dmitry.smirnov@example.com', 'ball' => 5],
            ['film' => 'Начало', 'user' => 'alexey.ivanov@example.com', 'ball' => 5],
            ['film' => 'Паразиты', 'user' => 'maria.petrova@example.com', 'ball' => 5],
            ['film' => 'Паразиты', 'user' => 'elena.kozlova@example.com', 'ball' => 4],
            ['film' => 'Унесённые призраками', 'user' => 'dmitry.smirnov@example.com', 'ball' => 5],
            ['film' => 'Унесённые призраками', 'user' => 'artem.volkov@example.com', 'ball' => 4],
        ];

        foreach ($ratingsData as $data) {
            Rating::firstOrCreate(
                [
                    'film_id' => $films[$data['film']]->id,
                    'user_id' => $users[$data['user']]->id,
                ],
                [
                    'ball' => $data['ball'],
                ]
            );
        }
    }
}
