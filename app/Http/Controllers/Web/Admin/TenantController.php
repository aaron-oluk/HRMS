<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\Tenancy\CreateTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::withCount('users', 'employees')->latest()->paginate(15);

        return view('admin.tenants.index', ['tenants' => $tenants]);
    }

    public function create(): View
    {
        return view('admin.tenants.create');
    }

    public function store(TenantRequest $request, CreateTenant $createTenant): RedirectResponse
    {
        $tenant = $createTenant->handle($request->validated());

        return redirect()->route('admin.tenants.index')->with('status', "{$tenant->name} was created.");
    }
}
