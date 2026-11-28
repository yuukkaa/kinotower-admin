<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gender;
use Illuminate\Http\JsonResponse;

class GenderController extends Controller
{
    public function index(): JsonResponse
    {
        $genders = Gender::orderBy('id')->get();

        return response()->json([
            'genders' => $genders->map(fn (Gender $gender) => [
                'id' => (int) $gender->id,
                'name' => $gender->name,
            ])->values(),
        ]);
    }
}
