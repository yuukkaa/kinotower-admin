<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use App\Models\Rating;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'films' => Film::count(),
                'categories' => Category::count(),
                'countries' => Country::count(),
                'users' => User::count(),
                'reviews' => Review::count(),
                'ratings' => Rating::count(),
            ],
            'categories' => Category::with('parent')->orderBy('id')->take(8)->get(),
            'countries' => Country::orderBy('id')->take(8)->get(),
        ]);
    }
}
