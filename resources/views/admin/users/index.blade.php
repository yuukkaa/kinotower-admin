@extends('layouts.admin')

@section('title', 'Пользователи')
@section('page_title', 'Пользователи')

@section('breadcrumb')
    <li class="breadcrumb-item active">Пользователи</li>
@endsection

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-users mr-1"></i> Список пользователей
            </h3>
            <div class="btn-group btn-group-sm ml-auto mt-2 mt-sm-0" role="group" aria-label="Фильтр пользователей">
                <a href="{{ route('admin.users.index', ['status' => 'all']) }}"
                   class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-default' }}">
                    Все
                </a>
                <a href="{{ route('admin.users.index', ['status' => 'active']) }}"
                   class="btn {{ $status === 'active' ? 'btn-primary' : 'btn-default' }}">
                    Активные
                </a>
                <a href="{{ route('admin.users.index', ['status' => 'trashed']) }}"
                   class="btn {{ $status === 'trashed' ? 'btn-primary' : 'btn-default' }}">
                    Удалённые
                </a>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px">#</th>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Пол</th>
                        <th style="width: 140px">Дата рождения</th>
                        <th style="width: 150px">Статус</th>
                        <th style="width: 150px" class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $user->fio }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->gender?->name ?? '—' }}</td>
                            <td>{{ $user->birthday?->format('d.m.Y') ?? '—' }}</td>
                            <td>
                                @if ($user->trashed())
                                    <span class="badge badge-danger">Удалён</span>
                                    <div class="small text-muted">{{ $user->deleted_at->format('d.m.Y H:i') }}</div>
                                @else
                                    <span class="badge badge-success">Активен</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if ($user->trashed())
                                    <form action="{{ route('admin.users.restore', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button type="submit" class="btn btn-sm btn-success" title="Восстановить пользователя">
                                            <i class="fas fa-undo mr-1"></i> Восстановить
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить пользователя «{{ $user->fio }}»?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Удалить пользователя">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Пользователи не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
