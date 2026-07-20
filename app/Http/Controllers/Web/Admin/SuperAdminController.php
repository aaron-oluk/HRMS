<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\Tenancy\CreateSuperAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdminRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SuperAdminController extends Controller
{
    public function index(): View
    {
        $superAdmins = User::where('is_super_admin', true)->latest()->paginate(15);

        return view('admin.super-admins.index', ['superAdmins' => $superAdmins]);
    }

    public function create(): View
    {
        return view('admin.super-admins.create');
    }

    public function store(SuperAdminRequest $request, CreateSuperAdmin $createSuperAdmin): RedirectResponse
    {
        $superAdmin = $createSuperAdmin->handle($request->validated());

        return redirect()->route('admin.super-admins.index')->with('status', "{$superAdmin->name} was added as a platform admin.");
    }
}
