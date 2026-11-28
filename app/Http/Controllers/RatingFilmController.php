<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RatingFilmController extends Controller
{
    public function index(Request $request, ?Film $film = null): View
    {
        $filmId = $film?->id ?? ($request->integer('film_id') ?: null);
        $selectedFilm = $film ?? ($filmId ? Film::find($filmId) : null);

        $ratings = Rating::with([
            'film' => fn ($query) => $query->withTrashed(),
            'user' => fn ($query) => $query->withTrashed(),
        ])
            ->when($filmId, fn ($query) => $query->where('film_id', $filmId))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $films = Film::orderBy('name')->get();

        return view('admin.ratings.index', compact('ratings', 'films', 'filmId', 'selectedFilm'));
    }

    public function destroy(Request $request, Rating $rating): RedirectResponse
    {
        $rating->delete();

        $params = array_filter([
            'film_id' => $request->integer('film_id') ?: null,
        ]);

        return redirect()
            ->route('admin.ratings.index', $params)
            ->with('status', 'Оценка успешно удалена.');
    }
}
