<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserRatingController extends Controller
{
    public function index(int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $ratings = $user->ratings()
            ->with(['film' => fn ($query) => $query->withTrashed()])
            ->latest('id')
            ->get();

        return response()->json([
            'ratings' => $ratings->map(fn (Rating $rating) => $this->formatRating($rating))->values(),
        ]);
    }

    public function store(Request $request, int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'film_id' => [
                'required',
                'integer',
                Rule::exists('films', 'id')->whereNull('deleted_at'),
            ],
            'ball' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        if ($user->ratings()->where('film_id', $validated['film_id'])->exists()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Score exist',
            ], 401);
        }

        $rating = $user->ratings()->create([
            'film_id' => $validated['film_id'],
            'ball' => $validated['ball'],
        ]);

        $rating->load('film');

        return response()->json($this->formatRating($rating), 201);
    }

    public function destroy(Request $request, int $userId, int $id): JsonResponse|Response
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if ($user->id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $rating = $user->ratings()->find($id);

        if (! $rating) {
            return response()->json([
                'message' => 'Rating not found',
            ], 404);
        }

        $rating->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRating(Rating $rating): array
    {
        return [
            'id' => (int) $rating->id,
            'film' => $rating->film ? [
                'id' => (int) $rating->film->id,
                'name' => $rating->film->name,
            ] : null,
            'score' => (int) $rating->ball,
            'created_at' => $rating->created_at?->utc()->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }
}
