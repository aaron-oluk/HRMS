<?php

namespace App\Http\Controllers\Web;

use App\Actions\Performance\LogOneOnOneNotes;
use App\Actions\Performance\ScheduleOneOnOne;
use App\Http\Controllers\Controller;
use App\Http\Requests\OneOnOneNotesRequest;
use App\Http\Requests\OneOnOneRequest;
use App\Models\OneOnOneMeeting;
use Illuminate\Http\RedirectResponse;

class OneOnOneController extends Controller
{
    public function store(OneOnOneRequest $request, ScheduleOneOnOne $scheduleOneOnOne): RedirectResponse
    {
        $scheduleOneOnOne->handle((int) $request->validated('employee_id'), $request->user(), $request->validated());

        return redirect()->route('performance.cycles.index')->with('status', '1-on-1 scheduled.');
    }

    public function notes(OneOnOneNotesRequest $request, OneOnOneMeeting $meeting, LogOneOnOneNotes $logOneOnOneNotes): RedirectResponse
    {
        $logOneOnOneNotes->handle($meeting, $request->user(), $request->validated('notes'));

        return redirect()->back()->with('status', 'Notes saved.');
    }
}
