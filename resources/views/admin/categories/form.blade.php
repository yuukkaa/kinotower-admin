@extends('layouts.admin')

@php($isEdit = isset($category))

@section('title', $isEdit ? 'Редактирование жанра' : 'Добавление жанра')
@section('page_title', $isEdit ? 'Редактирование жанра' : 'Добавление жанра')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Жанры</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Редактирование' : 'Добавление' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags mr-1"></i>
                        {{ $isEdit ? 'Изменение жанра' : 'Новый жанр' }}
                    </h3>
                </div>
                <form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Название жанра <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name ?? '') }}"
                                maxlength="150"
                                placeholder="Например: Фантастика"
                                required
                                autofocus
                            >
                            @error('name')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="parent_id">Родительский жанр</label>
                            <select
                                id="parent_id"
                                name="parent_id"
                                class="form-control @error('parent_id') is-invalid @enderror"
                            >
                                <option value="">— Без родительского жанра —</option>
                                @foreach ($parents as $parent)
                                    <option
                                        value="{{ $parent->id }}"
                                        @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $parent->id)
                                    >
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Сохранить изменения' : 'Добавить жанр' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
