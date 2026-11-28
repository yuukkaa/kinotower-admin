<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! in_array($status, ['all', 'active', 'trashed'], true)) {
            $status = 'all';
        }

        $query = User::with('gender');

        if ($status === 'all') {
            $query->withTrashed();
        } elseif ($status === 'trashed') {
            $query->onlyTrashed();
        }

        $users = $query
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'status'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $user->delete();

        $params = array_filter([
            'status' => $request->string('status')->toString() ?: null,
        ]);

        return redirect()
            ->route('admin.users.index', $params)
            ->with('status', 'Пользователь успешно удалён.');
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        $user->restore();

        $params = array_filter([
            'status' => $request->string('status')->toString() ?: null,
        ]);

        return redirect()
            ->route('admin.users.index', $params)
            ->with('status', 'Пользователь успешно восстановлен.');
    }
}
