@extends('layouts.admin')

@section('title', $selectedFilm ? 'Отзывы — ' . $selectedFilm->name : 'Отзывы')
@section('page_title', $selectedFilm ? 'Отзывы к фильму: ' . $selectedFilm->name : 'Отзывы к фильмам')

@section('breadcrumb')
    @if ($selectedFilm)
        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Отзывы</a></li>
        <li class="breadcrumb-item active">{{ $selectedFilm->name }}</li>
    @else
        <li class="breadcrumb-item active">Отзывы</li>
    @endif
@endsection

@section('content')
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Фильтр по фильму</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="row align-items-end">
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
                    <button type="submit" class="btn btn-secondary mr-2">
                        <i class="fas fa-search mr-1"></i> Показать
                    </button>
                    @if ($filmId)
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-comments mr-1"></i>
                @if ($selectedFilm)
                    Отзывы к фильму «{{ $selectedFilm->name }}»
                @else
                    Список всех отзывов
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
                        <th>Отзыв</th>
                        <th style="width: 140px">Статус</th>
                        <th style="width: 140px">Дата</th>
                        <th style="width: 140px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $reviews->firstItem() + $loop->index }}</td>
                            <td>
                                @if ($review->film)
                                    <a href="{{ route('admin.films.reviews.index', $review->film) }}">
                                        {{ $review->film->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $review->user?->fio ?? '—' }}
                                @if ($review->user?->trashed())
                                    <span class="badge badge-secondary">удалён</span>
                                @endif
                            </td>
                            <td>{{ $review->message }}</td>
                            <td>
                                @if ($review->is_approved)
                                    <span class="badge badge-success">Одобрен</span>
                                @else
                                    <span class="badge badge-warning">Не одобрен</span>
                                @endif
                            </td>
                            <td>{{ $review->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="text-right text-nowrap">
                                @unless ($review->is_approved)
                                    <form action="{{ route('admin.reviews.approved', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if ($filmId)
                                            <input type="hidden" name="film_id" value="{{ $filmId }}">
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success" title="Одобрить отзыв">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endunless
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот отзыв?');">
                                    @csrf
                                    @method('DELETE')
                                    @if ($filmId)
                                        <input type="hidden" name="film_id" value="{{ $filmId }}">
                                    @endif
                                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить отзыв">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Отзывы не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $reviews->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
