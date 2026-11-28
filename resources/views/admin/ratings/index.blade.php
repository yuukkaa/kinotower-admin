@extends('layouts.admin')

@section('title', $selectedFilm ? 'Оценки — ' . $selectedFilm->name : 'Оценки')
@section('page_title', $selectedFilm ? 'Оценки фильма: ' . $selectedFilm->name : 'Оценки фильмов')

@section('breadcrumb')
    @if ($selectedFilm)
        <li class="breadcrumb-item"><a href="{{ route('admin.ratings.index') }}">Оценки</a></li>
        <li class="breadcrumb-item active">{{ $selectedFilm->name }}</li>
    @else
        <li class="breadcrumb-item active">Оценки</li>
    @endif
@endsection

@section('content')
    <div class="card card-outline card-danger mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Фильтр по фильму</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.ratings.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-6 mb-2 mb-md-0">
                    <label for="film_id">Фильм</label>
                    <select id="film_id" name="film_id" class="form-control">
                        <option value="">— Все фильмы —</option>
                        @foreach ($films as $item)
                            <option value="{{ $item->id }}" @selected((int) $filmId === $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex">
                    <button type="submit" class="btn btn-danger mr-2">
                        <i class="fas fa-search mr-1"></i> Показать
                    </button>
                    @if ($filmId)
                        <a href="{{ route('admin.ratings.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-danger">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-star mr-1"></i>
                @if ($selectedFilm)
                    Оценки фильма «{{ $selectedFilm->name }}»
                @else
                    Список всех оценок
                @endif
            </h3>
            @if ($selectedFilm)
                <a href="{{ route('admin.films.index') }}" class="btn btn-default btn-sm ml-auto">
                    <i class="fas fa-arrow-left mr-1"></i> К фильмам
                </a>
            @endif
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px">#</th>
                        <th>Фильм</th>
                        <th>Пользователь</th>
                        <th style="width: 120px">Оценка</th>
                        <th style="width: 150px">Дата</th>
                        <th style="width: 110px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ratings as $rating)
                        <tr>
                            <td>{{ $ratings->firstItem() + $loop->index }}</td>
                            <td>
                                @if ($rating->film)
                                    <a href="{{ route('admin.films.ratings.index', $rating->film) }}">
                                        {{ $rating->film->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $rating->user?->fio ?? '—' }}
                                @if ($rating->user?->trashed())
                                    <span class="badge badge-secondary">удалён</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-warning">
                                    <i class="fas fa-star mr-1"></i>{{ $rating->ball }}
                                </span>
                            </td>
                            <td>{{ $rating->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="text-right text-nowrap">
                                <form action="{{ route('admin.ratings.destroy', $rating) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить эту оценку?');">
                                    @csrf
                                    @method('DELETE')
                                    @if ($filmId)
                                        <input type="hidden" name="film_id" value="{{ $filmId }}">
                                    @endif
                                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить оценку">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Оценки не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ratings->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $ratings->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
