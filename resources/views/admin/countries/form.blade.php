@extends('layouts.admin')

@php($isEdit = isset($country))

@section('title', $isEdit ? 'Редактирование страны' : 'Добавление страны')
@section('page_title', $isEdit ? 'Редактирование страны' : 'Добавление страны')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.countries.index') }}">Страны</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Редактирование' : 'Добавление' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-globe mr-1"></i>
                        {{ $isEdit ? 'Изменение страны' : 'Новая страна' }}
                    </h3>
                </div>
                <form action="{{ $isEdit ? route('admin.countries.update', $country) : route('admin.countries.store') }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Название страны <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $country->name ?? '') }}"
                                maxlength="50"
                                placeholder="Например: Россия"
                                required
                                autofocus
                            >
                            @error('name')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Сохранить изменения' : 'Добавить страну' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
