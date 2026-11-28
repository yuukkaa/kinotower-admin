@extends('layouts.admin')

@section('title', 'Фильмы')
@section('page_title', 'Фильмы')

@section('breadcrumb')
    <li class="breadcrumb-item active">Фильмы</li>
@endsection

@section('content')
    <div class="card card-outline card-info mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Фильтры</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.films.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="country_id">Страна</label>
                    <select id="country_id" name="country_id" class="form-control">
                        <option value="">— Все страны —</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected((int) $countryId === $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="category_id">Жанр</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">— Все жанры —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex">
                    <button type="submit" class="btn btn-info mr-2">
                        <i class="fas fa-search mr-1"></i> Применить
                    </button>
                    @if ($countryId || $categoryId)
                        <a href="{{ route('admin.films.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-video mr-1"></i> Список фильмов
            </h3>
            <a href="{{ route('admin.films.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus mr-1"></i> Добавить фильм
            </a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px">#</th>
                        <th>Название</th>
                        <th>Страна</th>
                        <th>Жанры</th>
                        <th style="width: 90px">Год</th>
                        <th style="width: 110px">Длит.</th>
                        <th style="width: 80px">Возраст</th>
                        <th style="width: 240px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($films as $film)
                        <tr>
                            <td>{{ $films->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($film->link_img)
                                        <img src="{{ $film->link_img }}" alt="{{ $film->name }}" class="img-thumbnail mr-2" style="width: 40px; height: 56px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $film->name }}</strong>
                                        <div class="small">
                                            @if ($film->link_kinopoisk)
                                                <a href="{{ $film->link_kinopoisk }}" target="_blank" rel="noopener noreferrer" class="mr-2">
                                                    <i class="fas fa-external-link-alt"></i> Кинопоиск
                                                </a>
                                            @endif
                                            <a href="{{ $film->link_video }}" target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-play-circle"></i> Видео
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $film->country?->name ?? '—' }}</td>
                            <td>
                                @forelse ($film->categories as $category)
                                    <span class="badge badge-primary">{{ $category->name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ $film->year_of_issue }}</td>
                            <td>{{ $film->duration }} мин.</td>
                            <td>{{ $film->age }}+</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('admin.films.categories.index', $film) }}" class="btn btn-sm btn-warning" title="Жанры фильма">
                                    <i class="fas fa-tags"></i>
                                </a>
                                <a href="{{ route('admin.films.reviews.index', $film) }}" class="btn btn-sm btn-secondary" title="Отзывы к фильму">
                                    <i class="fas fa-comments"></i>
                                </a>
                                <a href="{{ route('admin.films.ratings.index', $film) }}" class="btn btn-sm btn-dark" title="Оценки фильма">
                                    <i class="fas fa-star"></i>
                                </a>
                                <a href="{{ route('admin.films.edit', $film) }}" class="btn btn-sm btn-info" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.films.destroy', $film) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить фильм «{{ $film->name }}»?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Фильмы не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($films->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $films->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
