@extends('layouts.admin')

@section('title', 'Добавление жанра к фильму — ' . $film->name)
@section('page_title', 'Добавление жанра к фильму: ' . $film->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.films.index') }}">Фильмы</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.films.categories.index', $film) }}">Жанры фильма</a></li>
    <li class="breadcrumb-item active">Добавление</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags mr-1"></i> Новый жанр для фильма «{{ $film->name }}»
                    </h3>
                </div>
                <form action="{{ route('admin.films.categories.store', $film) }}" method="POST">
                    @csrf

                    <div class="card-body">
                        @if ($categories->isEmpty())
                            <div class="alert alert-info mb-0">
                                Все доступные жанры уже добавлены к этому фильму.
                            </div>
                        @else
                            <div class="form-group">
                                <label for="category_id">Жанр <span class="text-danger">*</span></label>
                                <select
                                    id="category_id"
                                    name="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">— Выберите жанр —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.films.categories.index', $film) }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Назад
                        </a>
                        @if ($categories->isNotEmpty())
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Добавить жанр
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
