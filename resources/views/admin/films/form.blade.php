@extends('layouts.admin')

@php($isEdit = isset($film))

@section('title', $isEdit ? 'Редактирование фильма' : 'Добавление фильма')
@section('page_title', $isEdit ? 'Редактирование фильма' : 'Добавление фильма')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.films.index') }}">Фильмы</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Редактирование' : 'Добавление' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-video mr-1"></i>
                        {{ $isEdit ? 'Изменение фильма' : 'Новый фильм' }}
                    </h3>
                </div>
                <form action="{{ $isEdit ? route('admin.films.update', $film) : route('admin.films.store') }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Название фильма <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $film->name ?? '') }}"
                                maxlength="150"
                                placeholder="Например: Интерстеллар"
                                required
                                autofocus
                            >
                            @error('name')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="country_id">Страна <span class="text-danger">*</span></label>
                            <select
                                id="country_id"
                                name="country_id"
                                class="form-control @error('country_id') is-invalid @enderror"
                                required
                            >
                                <option value="">— Выберите страну —</option>
                                @foreach ($countries as $country)
                                    <option
                                        value="{{ $country->id }}"
                                        @selected((string) old('country_id', $film->country_id ?? '') === (string) $country->id)
                                    >
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year_of_issue">Год выпуска <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        id="year_of_issue"
                                        name="year_of_issue"
                                        class="form-control @error('year_of_issue') is-invalid @enderror"
                                        value="{{ old('year_of_issue', $film->year_of_issue ?? '') }}"
                                        min="1901"
                                        max="2155"
                                        placeholder="2024"
                                        required
                                    >
                                    @error('year_of_issue')
                                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration">Длительность (мин.) <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        id="duration"
                                        name="duration"
                                        class="form-control @error('duration') is-invalid @enderror"
                                        value="{{ old('duration', $film->duration ?? '') }}"
                                        min="1"
                                        max="65535"
                                        placeholder="120"
                                        required
                                    >
                                    @error('duration')
                                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="age">Возрастное ограничение <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        id="age"
                                        name="age"
                                        class="form-control @error('age') is-invalid @enderror"
                                        value="{{ old('age', $film->age ?? '') }}"
                                        min="0"
                                        max="255"
                                        placeholder="16"
                                        required
                                    >
                                    @error('age')
                                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="link_video">Ссылка на видео <span class="text-danger">*</span></label>
                            <input
                                type="url"
                                id="link_video"
                                name="link_video"
                                class="form-control @error('link_video') is-invalid @enderror"
                                value="{{ old('link_video', $film->link_video ?? '') }}"
                                maxlength="255"
                                placeholder="https://example.com/video.mp4"
                                required
                            >
                            @error('link_video')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="link_img">Ссылка на постер</label>
                            <input
                                type="url"
                                id="link_img"
                                name="link_img"
                                class="form-control @error('link_img') is-invalid @enderror"
                                value="{{ old('link_img', $film->link_img ?? '') }}"
                                maxlength="255"
                                placeholder="https://example.com/poster.jpg"
                            >
                            @error('link_img')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="link_kinopoisk">Ссылка на Кинопоиск</label>
                            <input
                                type="url"
                                id="link_kinopoisk"
                                name="link_kinopoisk"
                                class="form-control @error('link_kinopoisk') is-invalid @enderror"
                                value="{{ old('link_kinopoisk', $film->link_kinopoisk ?? '') }}"
                                maxlength="255"
                                placeholder="https://www.kinopoisk.ru/film/258687/"
                            >
                            @error('link_kinopoisk')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.films.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Сохранить изменения' : 'Добавить фильм' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
