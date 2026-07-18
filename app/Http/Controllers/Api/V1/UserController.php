<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(25);

        return UserResource::collection($users);
    }

    public function store(UserRequest $request, CreateUser $createUser)
    {
        $user = $createUser->handle($request->user()->tenant, $request->validated());

        return UserResource::make($user)->response()->setStatusCode(201);
    }

    public function show(Request $request, User $user)
    {
        $this->ensureSameTenant($request, $user);

        return UserResource::make($user);
    }

    public function update(UserRequest $request, User $user, UpdateUser $updateUser)
    {
        $this->ensureSameTenant($request, $user);

        $user = $updateUser->handle($user, $request->validated());

        return UserResource::make($user);
    }

    protected function ensureSameTenant(Request $request, User $user): void
    {
        if ($user->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException;
        }
    }
}
