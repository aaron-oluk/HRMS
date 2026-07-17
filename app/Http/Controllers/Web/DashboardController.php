<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenant = $request->user()->tenant;

        return view('dashboard', [
            'employeeCount' => $tenant?->employees()->count() ?? 0,
            'entityCount' => $tenant?->entities()->count() ?? 0,
        ]);
    }
}
