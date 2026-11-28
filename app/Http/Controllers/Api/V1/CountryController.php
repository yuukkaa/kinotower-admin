<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::withCount('films')
            ->orderBy('id')
            ->get();

        return response()->json([
            'countries' => $countries->map(fn (Country $country) => [
                'id' => (int) $country->id,
                'name' => $country->name,
                'filmCount' => (int) $country->films_count,
            ])->values(),
        ]);
    }
}
