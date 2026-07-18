<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::with('actor')->latest('created_at')->paginate(25);

        return view('audit-logs.index', ['logs' => $logs]);
    }
}
