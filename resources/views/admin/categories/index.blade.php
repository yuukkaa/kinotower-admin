@extends('layouts.admin')

@section('title', 'Жанры')
@section('page_title', 'Жанры')

@section('breadcrumb')
    <li class="breadcrumb-item active">Жанры</li>
@endsection

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-tags mr-1"></i> Список жанров
            </h3>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus mr-1"></i> Добавить жанр
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px">#</th>
                        <th>Название</th>
                        <th>Родительский жанр</th>
                        <th style="width: 120px">Фильмов</th>
                        <th style="width: 180px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ $category->films_count }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-info" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить жанр «{{ $category->name }}»?');">
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
                            <td colspan="5" class="text-center text-muted py-4">
                                Жанры пока не добавлены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
