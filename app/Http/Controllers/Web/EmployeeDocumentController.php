<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDocumentRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EmployeeDocumentController extends Controller
{
    public function store(EmployeeDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('employee-documents', 'local');

        $employee->documents()->create([
            'type' => $request->validated('type'),
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('employees.show', $employee)->with('status', 'Document uploaded.');
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        Gate::authorize('employees.manage');

        $document->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Document deleted.');
    }
}
