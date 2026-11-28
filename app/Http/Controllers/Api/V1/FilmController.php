<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Film;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $size = max(1, (int) $request->query('size', 10));

        $sortBy = (string) $request->query('sortBy', 'name');
        if (! in_array($sortBy, ['name', 'year', 'rating'], true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->query('sortDir', 'asc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        $categoryId = (int) $request->query('category', 0);
        $countryId = (int) $request->query('country', 0);
        $search = trim((string) $request->query('search', ''));

        $baseQuery = Film::query()
            ->when($categoryId > 0, fn ($query) => $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId)))
            ->when($countryId > 0, fn ($query) => $query->where('country_id', $countryId))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"));

        $total = (clone $baseQuery)->count();

        $filmsQuery = $baseQuery
            ->with(['country', 'categories'])
            ->withAvg('ratings', 'ball')
            ->withCount('reviews');

        if ($sortBy === 'name') {
            $filmsQuery->orderBy('films.name', $sortDir);
        } elseif ($sortBy === 'year') {
            $filmsQuery->orderBy('films.year_of_issue', $sortDir);
        } else {
            $filmsQuery->orderByRaw("COALESCE(ratings_avg_ball, 0) {$sortDir}");
        }

        $films = $filmsQuery
            ->forPage($page, $size)
            ->get();

        return response()->json([
            'page' => $page,
            'size' => $films->count(),
            'total' => $total,
            'films' => $films->map(fn (Film $film) => $this->formatFilm($film))->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $film = Film::with(['country', 'categories'])
            ->withAvg('ratings', 'ball')
            ->withCount('reviews')
            ->find($id);

        if (! $film) {
            return response()->json([
                'message' => 'Film not found',
            ], 404);
        }

        return response()->json($this->formatFilm($film));
    }

    public function reviews(int $filmId): JsonResponse
    {
        $film = Film::find($filmId);

        if (! $film) {
            return response()->json([
                'message' => 'Film not found',
            ], 404);
        }

        $reviews = $film->reviews()
            ->where('is_approved', true)
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->latest('id')
            ->get();

        return response()->json([
            'reviews' => $reviews->map(fn ($review) => [
                'id' => (int) $review->id,
                'user' => $review->user ? [
                    'id' => (int) $review->user->id,
                    'fio' => $review->user->fio,
                ] : null,
                'message' => $review->message,
                'created_at' => $review->created_at?->utc()->format('Y-m-d\TH:i:s.v\Z'),
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFilm(Film $film): array
    {
        return [
            'id' => (int) $film->id,
            'name' => $film->name,
            'duration' => (int) $film->duration,
            'year_of_issue' => (int) $film->year_of_issue,
            'age' => (int) $film->age,
            'link_img' => $film->link_img ?: null,
            'link_kinopoisk' => $film->link_kinopoisk ?: null,
            'link_video' => $film->link_video,
            'created_at' => $film->created_at?->utc()->format('Y-m-d\TH:i:s.v\Z'),
            'country' => $film->country ? [
                'id' => (int) $film->country->id,
                'name' => $film->country->name,
            ] : null,
            'categories' => $film->categories->map(fn ($category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])->values()->all(),
            'ratingAvg' => $film->ratings_avg_ball !== null ? round((float) $film->ratings_avg_ball, 1) : 0,
            'reviewCount' => (int) ($film->reviews_count ?? 0),
        ];
    }
}
