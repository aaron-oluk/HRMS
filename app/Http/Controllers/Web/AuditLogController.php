<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(): View
    {
        Gate::authorize('audit.view');

        $logs = AuditLog::with('actor')->latest('created_at')->paginate(25);

        return view('audit-logs.index', ['logs' => $logs]);
    }
}
