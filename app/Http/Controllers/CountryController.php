<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::withCount('films')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('admin.countries.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('countries', 'name')],
        ], [
            'name.required' => 'Введите название страны.',
            'name.max' => 'Название страны не должно превышать 50 символов.',
            'name.unique' => 'Такая страна уже существует.',
        ]);

        Country::create($validated);

        return redirect()
            ->route('admin.countries.index')
            ->with('status', 'Страна успешно добавлена.');
    }

    public function edit(Country $country): View
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('countries', 'name')->ignore($country->id)],
        ], [
            'name.required' => 'Введите название страны.',
            'name.max' => 'Название страны не должно превышать 50 символов.',
            'name.unique' => 'Такая страна уже существует.',
        ]);

        $country->update($validated);

        return redirect()
            ->route('admin.countries.index')
            ->with('status', 'Страна успешно обновлена.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        if ($country->films()->withTrashed()->exists()) {
            return redirect()
                ->route('admin.countries.index')
                ->with('error', 'Невозможно удалить страну, так как к ней привязаны фильмы.');
        }

        $country->delete();

        return redirect()
            ->route('admin.countries.index')
            ->with('status', 'Страна успешно удалена.');
    }
}
