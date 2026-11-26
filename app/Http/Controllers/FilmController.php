<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FilmController extends Controller
{
    public function index(Request $request): View
    {
        $countryId = $request->integer('country_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $films = Film::with(['country', 'categories'])
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($categoryQuery) => $categoryQuery->where('categories.id', $categoryId)))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $countries = Country::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.films.index', compact(
            'films',
            'countries',
            'categories',
            'countryId',
            'categoryId',
        ));
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();

        return view('admin.films.form', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFilm($request);

        Film::create($validated);

        return redirect()
            ->route('admin.films.index')
            ->with('status', 'Фильм успешно добавлен.');
    }

    public function edit(Film $film): View
    {
        $countries = Country::orderBy('name')->get();

        return view('admin.films.form', compact('film', 'countries'));
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        $validated = $this->validateFilm($request);

        $film->update($validated);

        return redirect()
            ->route('admin.films.index')
            ->with('status', 'Фильм успешно обновлён.');
    }

    public function destroy(Film $film): RedirectResponse
    {
        $film->delete();

        return redirect()
            ->route('admin.films.index')
            ->with('status', 'Фильм успешно удалён.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFilm(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'duration' => ['required', 'integer', 'min:1', 'max:65535'],
            'year_of_issue' => ['required', 'integer', 'min:1901', 'max:2155'],
            'age' => ['required', 'integer', 'min:0', 'max:255'],
            'link_img' => ['nullable', 'url', 'max:255'],
            'link_kinopoisk' => ['nullable', 'url', 'max:255'],
            'link_video' => ['required', 'url', 'max:255'],
        ], [
            'name.required' => 'Введите название фильма.',
            'name.max' => 'Название фильма не должно превышать 150 символов.',
            'country_id.required' => 'Выберите страну.',
            'country_id.exists' => 'Выбранная страна не найдена.',
            'duration.required' => 'Укажите длительность фильма.',
            'duration.integer' => 'Длительность должна быть целым числом.',
            'duration.min' => 'Длительность должна быть не менее 1 минуты.',
            'year_of_issue.required' => 'Укажите год выпуска.',
            'year_of_issue.integer' => 'Год выпуска должен быть целым числом.',
            'year_of_issue.min' => 'Год выпуска должен быть не ранее 1901.',
            'year_of_issue.max' => 'Год выпуска должен быть не позднее 2155.',
            'age.required' => 'Укажите возрастное ограничение.',
            'age.integer' => 'Возрастное ограничение должно быть целым числом.',
            'age.min' => 'Возрастное ограничение не может быть отрицательным.',
            'link_img.url' => 'Укажите корректную ссылку на постер.',
            'link_img.max' => 'Ссылка на постер не должна превышать 255 символов.',
            'link_kinopoisk.url' => 'Укажите корректную ссылку на Кинопоиск.',
            'link_kinopoisk.max' => 'Ссылка на Кинопоиск не должна превышать 255 символов.',
            'link_video.required' => 'Укажите ссылку на видео.',
            'link_video.url' => 'Укажите корректную ссылку на видео.',
            'link_video.max' => 'Ссылка на видео не должна превышать 255 символов.',
        ]);
    }
}
