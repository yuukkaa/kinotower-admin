<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('films')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::orderBy('name')->get();

        return view('admin.categories.form', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->whereNull('deleted_at')],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ], [
            'name.required' => 'Введите название жанра.',
            'name.max' => 'Название жанра не должно превышать 150 символов.',
            'name.unique' => 'Такой жанр уже существует.',
            'parent_id.exists' => 'Выбранный родительский жанр не найден.',
        ]);

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Жанр успешно добавлен.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($category->id)->whereNull('deleted_at')],
            'parent_id' => ['nullable', 'integer', Rule::notIn([$category->id]), Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ], [
            'name.required' => 'Введите название жанра.',
            'name.max' => 'Название жанра не должно превышать 150 символов.',
            'name.unique' => 'Такой жанр уже существует.',
            'parent_id.not_in' => 'Жанр не может быть родительским для самого себя.',
            'parent_id.exists' => 'Выбранный родительский жанр не найден.',
        ]);

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Жанр успешно обновлён.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Жанр успешно удалён.');
    }
}
