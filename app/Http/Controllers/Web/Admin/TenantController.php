<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\Tenancy\CreateTenant;
use App\Actions\Tenancy\ReactivateTenant;
use App\Actions\Tenancy\SuspendTenant;
use App\Actions\Tenancy\UpdateTenant;
use App\Actions\Tenancy\UpdateTenantModules;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\TenantFeatureFlag;
use App\Support\Audit\AccessAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class TenantController extends Controller
{
    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

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

    public function show(Tenant $tenant): View
    {
        $tenant->loadCount('users', 'employees', 'entities');

        // A super admin has no team of their own (Spatie's permission tables are
        // team-scoped by tenant_id), so the roles relation below would otherwise
        // resolve against a null team and always come back empty.
        $this->permissionRegistrar->setPermissionsTeamId($tenant->id);

        return view('admin.tenants.show', [
            'tenant' => $tenant,
            'departmentCount' => Department::where('tenant_id', $tenant->id)->count(),
            'users' => $tenant->users()->with('roles')->orderBy('name')->get(),
            'hrAdmins' => $tenant->users()->whereHas('roles', fn ($q) => $q->where('name', 'HR Admin'))->get(),
            'modules' => TenantFeatureFlag::MODULES,
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', ['tenant' => $tenant]);
    }

    public function update(TenantUpdateRequest $request, Tenant $tenant, UpdateTenant $updateTenant): RedirectResponse
    {
        $updateTenant->handle($tenant, $request->validated());

        return redirect()->route('admin.tenants.index')->with('status', "{$tenant->name} was updated.");
    }

    public function suspend(Tenant $tenant, SuspendTenant $suspendTenant): RedirectResponse
    {
        $suspendTenant->handle($tenant);

        return redirect()->route('admin.tenants.index')->with('status', "{$tenant->name} was suspended.");
    }

    public function reactivate(Tenant $tenant, ReactivateTenant $reactivateTenant): RedirectResponse
    {
        $reactivateTenant->handle($tenant);

        return redirect()->route('admin.tenants.index')->with('status', "{$tenant->name} was reactivated.");
    }

    public function updateModules(Request $request, Tenant $tenant, UpdateTenantModules $updateTenantModules): RedirectResponse
    {
        $updateTenantModules->handle($tenant, (array) $request->input('modules', []));

        return redirect()->route('admin.tenants.show', $tenant)->with('status', 'Enabled modules were updated.');
    }

    public function impersonate(Request $request, Tenant $tenant): RedirectResponse
    {
        // Same reasoning as show() above — without this, the query below always
        // returns no HR Admin for a super admin, who holds no team of their own.
        $this->permissionRegistrar->setPermissionsTeamId($tenant->id);

        $hrAdmin = $tenant->users()->whereHas('roles', fn ($q) => $q->where('name', 'HR Admin'))->first();

        if (! $hrAdmin) {
            return back()->with('error', "{$tenant->name} has no HR Admin user to log in as.");
        }

        $actor = $request->user();

        session(['impersonator_id' => $actor->id]);
        AccessAudit::impersonationStarted($actor, $hrAdmin);
        Auth::loginUsingId($hrAdmin->id);

        return redirect()->route('dashboard')->with('status', "Now viewing as {$hrAdmin->name} ({$tenant->name}).");
    }
}
