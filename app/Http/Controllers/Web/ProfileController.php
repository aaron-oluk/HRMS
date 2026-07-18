<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('employee.currentEmployment.department', 'employee.currentEmployment.position', 'employee.entity');

        return view('profile.edit', [
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
        ]);
    }
}
