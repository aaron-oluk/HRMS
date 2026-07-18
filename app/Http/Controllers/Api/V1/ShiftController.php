<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        return ShiftResource::collection(Shift::latest()->paginate(25));
    }

    public function store(ShiftRequest $request)
    {
        return ShiftResource::make(Shift::create($request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Shift $shift)
    {
        return ShiftResource::make($shift);
    }

    public function update(ShiftRequest $request, Shift $shift)
    {
        $shift->update($request->validated());

        return ShiftResource::make($shift);
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return response()->noContent();
    }
}
