<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private readonly ApiTokenService $tokens,
    ) {}

    public function show(int $id): JsonResponse
    {
        $user = User::with('gender')
            ->withCount(['reviews', 'ratings'])
            ->find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'id' => (int) $user->id,
            'fio' => $user->fio,
            'email' => $user->email,
            'birthday' => $user->birthday?->format('Y-m-d'),
            'gender' => $user->gender ? [
                'id' => (int) $user->gender->id,
                'name' => $user->gender->name,
            ] : null,
            'reviewCount' => (int) $user->reviews_count,
            'ratingCount' => (int) $user->ratings_count,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'fio' => ['required', 'string', 'min:2', 'max:150'],
            'email' => [
                'required',
                'email',
                'min:4',
                'max:50',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'birthday' => ['required', 'date'],
            'gender_id' => ['required', 'integer', 'exists:genders,id'],
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function destroy(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $user->delete();
        $this->tokens->revokeToken($request->bearerToken());

        return response()->noContent();
    }
}
