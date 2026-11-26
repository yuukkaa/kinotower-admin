@extends('layouts.admin')

@section('title', 'Жанры фильма — ' . $film->name)
@section('page_title', 'Жанры фильма: ' . $film->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.films.index') }}">Фильмы</a></li>
    <li class="breadcrumb-item active">Жанры фильма</li>
@endsection

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-tags mr-1"></i> Жанры фильма «{{ $film->name }}»
            </h3>
            <div class="ml-auto">
                <a href="{{ route('admin.films.index') }}" class="btn btn-default btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> К фильмам
                </a>
                <a href="{{ route('admin.films.categories.create', $film) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Добавить жанр
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px">#</th>
                        <th>Название жанра</th>
                        <th>Родительский жанр</th>
                        <th style="width: 140px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td class="text-right">
                                <form action="{{ route('admin.films.categories.destroy', [$film, $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить жанр «{{ $category->name }}» у фильма «{{ $film->name }}»?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить жанр у фильма">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                У этого фильма пока нет жанров.
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
