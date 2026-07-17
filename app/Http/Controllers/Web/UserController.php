<?php

namespace App\Http\Controllers\Web;

use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('users.manage');

        $users = User::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('users.manage');

        return view('users.create', [
            'employees' => Employee::whereDoesntHave('user')->orderBy('first_name')->get(),
        ]);
    }

    public function store(UserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->user()->tenant, $request->validated());

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, User $user): View
    {
        Gate::authorize('users.manage');
        $this->ensureSameTenant($request, $user);

        return view('users.edit', [
            'user' => $user,
            'employees' => Employee::whereDoesntHave('user')->orWhere('id', $user->employee_id)->orderBy('first_name')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        $this->ensureSameTenant($request, $user);

        $updateUser->handle($user, $request->validated());

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    protected function ensureSameTenant(Request $request, User $user): void
    {
        if ($user->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException;
        }
    }
}
