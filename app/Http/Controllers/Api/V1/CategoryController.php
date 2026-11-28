<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('parent')
            ->withCount('films')
            ->orderBy('id')
            ->get();

        return response()->json([
            'categories' => $categories->map(fn (Category $category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
                'parentCategory' => $category->parent ? [
                    'id' => (int) $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
                'filmCount' => (int) $category->films_count,
            ])->values(),
        ]);
    }
}
