<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Боевик' => ['Шпионский боевик', 'Боевые искусства'],
            'Комедия' => ['Романтическая комедия', 'Черная комедия'],
            'Драма' => ['Историческая драма', 'Биографическая драма'],
            'Фантастика' => ['Киберпанк', 'Космическая опера'],
            'Триллер' => ['Психологический триллер'],
            'Ужасы' => [],
            'Мультфильм' => ['Аниме'],
            'Документальный' => [],
        ];

        foreach ($categories as $parentName => $children) {
            $parent = Category::firstOrCreate([
                'name' => $parentName,
                'parent_id' => null,
            ]);

            foreach ($children as $childName) {
                Category::firstOrCreate([
                    'name' => $childName,
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
