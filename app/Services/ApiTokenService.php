<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ApiTokenService
{
    private const CACHE_PREFIX = 'api_token:';

    public function createToken(User $user): string
    {
        $plainTextToken = bin2hex(random_bytes(32));

        Cache::forever($this->cacheKey($plainTextToken), $user->id);

        return $plainTextToken;
    }

    public function findUserByToken(?string $token): ?User
    {
        if (! $token) {
            return null;
        }

        $userId = Cache::get($this->cacheKey($token));

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    public function revokeToken(?string $token): void
    {
        if (! $token) {
            return;
        }

        Cache::forget($this->cacheKey($token));
    }

    private function cacheKey(string $token): string
    {
        return self::CACHE_PREFIX.hash('sha256', $token);
    }
}
