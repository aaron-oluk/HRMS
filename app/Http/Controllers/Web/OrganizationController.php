<?php

namespace App\Http\Controllers\Web;

use App\Actions\Tenancy\UpdateStatutoryConfig;
use App\Actions\Tenancy\UpdateTenant;
use App\Actions\Tenancy\UpdateTenantStructure;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationSettingsRequest;
use App\Http\Requests\StatutoryConfigRequest;
use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use App\Models\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function edit(Request $request): View
    {
        return view('organization.edit', [
            'tenant' => $request->user()->tenant,
            'bands' => StatutoryPayeBand::query()->orderBy('order')->get(),
            'settings' => StatutorySetting::current(),
            'themes' => Theme::orderBy('name')->get(),
        ]);
    }

    public function updateGeneral(OrganizationSettingsRequest $request, UpdateTenant $updateTenant): RedirectResponse
    {
        $updateTenant->handle($request->user()->tenant, $request->validated());

        return redirect()->route('organization.edit')->with('status', 'Organization settings were updated.');
    }

    public function updateStatutory(StatutoryConfigRequest $request, UpdateStatutoryConfig $updateStatutoryConfig): RedirectResponse
    {
        $updateStatutoryConfig->handle($request->validated());

        return redirect()->route('organization.edit')->with('status', 'Statutory configuration was updated.');
    }

    public function updateStructure(Request $request, UpdateTenantStructure $updateTenantStructure): RedirectResponse
    {
        $request->validate(['structure' => ['required', 'string', 'in:simple,segmented']]);

        $updateTenantStructure->handle($request->user()->tenant, $request->string('structure')->toString());

        return redirect()->route('organization.edit')->with('status', 'Organization structure was updated.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $request->validate(['theme_id' => ['nullable', 'exists:themes,id']]);

        $request->user()->tenant->update(['theme_id' => $request->input('theme_id')]);

        return redirect()->route('organization.edit')->with('status', 'Theme was updated.');
    }
}
