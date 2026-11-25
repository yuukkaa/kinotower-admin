@extends('layouts.admin')

@section('title', 'Страны')
@section('page_title', 'Страны')

@section('breadcrumb')
    <li class="breadcrumb-item active">Страны</li>
@endsection

@section('content')
    <div class="card card-outline card-warning">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-globe mr-1"></i> Список стран
            </h3>
            <a href="{{ route('admin.countries.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus mr-1"></i> Добавить страну
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px">#</th>
                        <th>Название</th>
                        <th style="width: 120px">Фильмов</th>
                        <th style="width: 180px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($countries as $country)
                        <tr>
                            <td>{{ $countries->firstItem() + $loop->index }}</td>
                            <td>{{ $country->name }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ $country->films_count }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-sm btn-info" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить страну «{{ $country->name }}»?');">
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
                            <td colspan="4" class="text-center text-muted py-4">
                                Страны пока не добавлены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($countries->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $countries->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
