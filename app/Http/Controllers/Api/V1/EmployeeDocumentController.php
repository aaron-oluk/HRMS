<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDocumentRequest;
use App\Http\Resources\EmployeeDocumentResource;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Gate;

class EmployeeDocumentController extends Controller
{
    public function index(Employee $employee)
    {
        Gate::authorize('employees.view-documents');

        return EmployeeDocumentResource::collection($employee->documents()->latest()->paginate(25));
    }

    public function store(EmployeeDocumentRequest $request, Employee $employee)
    {
        $file = $request->file('file');
        $path = $file->store('employee-documents', 'local');

        $document = $employee->documents()->create([
            'type' => $request->validated('type'),
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return EmployeeDocumentResource::make($document)->response()->setStatusCode(201);
    }

    public function destroy(Employee $employee, EmployeeDocument $document)
    {
        Gate::authorize('employees.manage-documents');

        $document->delete();

        return response()->noContent();
    }
}
