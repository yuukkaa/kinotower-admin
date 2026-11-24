<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! Admin::where('email', 'admin@kinotower.ru')->exists()) {
            Admin::factory()->create([
                'name' => 'Администратор',
                'email' => 'admin@kinotower.ru',
                'password' => 'password',
            ]);
        }
    }
}
