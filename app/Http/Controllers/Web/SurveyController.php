<?php

namespace App\Http\Controllers\Web;

use App\Actions\Engagement\LaunchSurvey;
use App\Actions\Engagement\SubmitSurveyResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Http\Requests\SurveyResponseRequest;
use App\Models\Survey;
use App\Models\SurveyResponseAnswer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(): View
    {
        return view('engagement.surveys.index', [
            'surveys' => Survey::withCount('responses')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('engagement.surveys.create');
    }

    public function store(SurveyRequest $request, LaunchSurvey $launchSurvey): RedirectResponse
    {
        $survey = $launchSurvey->handle($request->validated(), $request->user());

        return redirect()->route('engagement.surveys.show', $survey)->with('status', 'Survey launched.');
    }

    public function show(Request $request, Survey $survey): View
    {
        $survey->load('questions');

        $results = $survey->questions->map(function ($question) use ($survey) {
            $answers = SurveyResponseAnswer::where('survey_question_id', $question->id)
                ->whereHas('response', fn ($q) => $q->where('survey_id', $survey->id))
                ->get();

            return [
                'question' => $question,
                'average_rating' => $question->type === 'rating' ? round($answers->avg('rating_value'), 1) : null,
                'text_answers' => $question->type === 'text' ? $answers->pluck('text_value')->filter()->values() : collect(),
            ];
        });

        $myResponse = $request->user()->employee
            ? $survey->responses()->where('employee_id', $request->user()->employee->id)->exists()
            : false;

        return view('engagement.surveys.show', [
            'survey' => $survey,
            'results' => $results,
            'responseCount' => $survey->responses()->count(),
            'myResponse' => $myResponse,
        ]);
    }

    public function respond(SurveyResponseRequest $request, Survey $survey, SubmitSurveyResponse $submitSurveyResponse): RedirectResponse
    {
        $submitSurveyResponse->handle($survey, $request->user()->employee, $request->validated('answers'));

        return redirect()->route('profile.edit')->with('status', 'Thanks for your feedback.');
    }
}
