<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserReviewController extends Controller
{
    public function index(int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $reviews = $user->reviews()
            ->with(['film' => fn ($query) => $query->withTrashed()])
            ->latest('id')
            ->get();

        return response()->json([
            'reviews' => $reviews->map(fn (Review $review) => $this->formatReview($review))->values(),
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
            'message' => ['required', 'string', 'min:4', 'max:1024'],
        ]);

        $review = $user->reviews()->create([
            'film_id' => $validated['film_id'],
            'message' => $validated['message'],
            'is_approved' => false,
        ]);

        $review->load('film');

        return response()->json($this->formatReview($review), 201);
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

        $review = $user->reviews()->find($id);

        if (! $review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        $review->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReview(Review $review): array
    {
        return [
            'id' => (int) $review->id,
            'film' => $review->film ? [
                'id' => (int) $review->film->id,
                'name' => $review->film->name,
            ] : null,
            'message' => $review->message,
            'is_approved' => (int) $review->is_approved,
            'created_at' => $review->created_at?->utc()->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }
}
