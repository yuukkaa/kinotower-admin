<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryFilmController extends Controller
{
    public function index(Film $film): View
    {
        $categories = $film->categories()
            ->with('parent')
            ->orderBy('categories.id')
            ->paginate(15);

        return view('admin.films.categories.index', compact('film', 'categories'));
    }

    public function create(Film $film): View
    {
        $assignedIds = $film->categories()->pluck('categories.id');

        $categories = Category::whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('admin.films.categories.create', compact('film', 'categories'));
    }

    public function store(Request $request, Film $film): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
                Rule::unique('category_films', 'category_id')->where('film_id', $film->id),
            ],
        ], [
            'category_id.required' => 'Выберите жанр.',
            'category_id.exists' => 'Выбранный жанр не найден.',
            'category_id.unique' => 'Этот жанр уже добавлен к фильму.',
        ]);

        $film->categories()->attach($validated['category_id']);

        return redirect()
            ->route('admin.films.categories.index', $film)
            ->with('status', 'Жанр успешно добавлен к фильму.');
    }

    public function destroy(Film $film, Category $category): RedirectResponse
    {
        $film->categories()->detach($category->id);

        return redirect()
            ->route('admin.films.categories.index', $film)
            ->with('status', 'Жанр успешно удалён у фильма.');
    }
}
