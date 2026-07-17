<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecuritySettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.security', [
            'twoFactorEnabled' => $request->user()->two_factor_confirmed_at !== null,
        ]);
    }
}
