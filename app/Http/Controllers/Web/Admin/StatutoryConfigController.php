<?php

namespace App\Http\Controllers\Web\Admin;

use App\Actions\Tenancy\UpdateStatutoryConfig;
use App\Http\Controllers\Controller;
use App\Http\Requests\StatutoryConfigRequest;
use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StatutoryConfigController extends Controller
{
    public function edit(): View
    {
        return view('admin.statutory.edit', [
            'bands' => StatutoryPayeBand::query()->orderBy('order')->get(),
            'settings' => StatutorySetting::current(),
        ]);
    }

    public function update(StatutoryConfigRequest $request, UpdateStatutoryConfig $updateStatutoryConfig): RedirectResponse
    {
        $updateStatutoryConfig->handle($request->validated());

        return redirect()->route('admin.statutory.edit')->with('status', 'Statutory configuration was updated.');
    }
}
