<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewFilmController extends Controller
{
    public function index(Request $request, ?Film $film = null): View
    {
        $filmId = $film?->id ?? ($request->integer('film_id') ?: null);
        $selectedFilm = $film ?? ($filmId ? Film::find($filmId) : null);

        $reviews = Review::with([
            'film' => fn ($query) => $query->withTrashed(),
            'user' => fn ($query) => $query->withTrashed(),
        ])
            ->when($filmId, fn ($query) => $query->where('film_id', $filmId))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $films = Film::orderBy('name')->get();

        return view('admin.reviews.index', compact('reviews', 'films', 'filmId', 'selectedFilm'));
    }

    public function approved(Request $request, Review $review): RedirectResponse
    {
        $review->update([
            'is_approved' => true,
        ]);

        $params = array_filter([
            'film_id' => $request->integer('film_id') ?: null,
        ]);

        return redirect()
            ->route('admin.reviews.index', $params)
            ->with('status', 'Отзыв успешно одобрен.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $review->delete();

        $params = array_filter([
            'film_id' => $request->integer('film_id') ?: null,
        ]);

        return redirect()
            ->route('admin.reviews.index', $params)
            ->with('status', 'Отзыв успешно удалён.');
    }
}
