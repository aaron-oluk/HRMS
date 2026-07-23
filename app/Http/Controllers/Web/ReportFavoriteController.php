<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportFavoriteRequest;
use App\Models\ReportFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportFavoriteController extends Controller
{
    public function store(ReportFavoriteRequest $request): RedirectResponse
    {
        ReportFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'report_key' => $request->validated('report_key'),
        ]);

        return back();
    }

    public function destroy(Request $request, string $reportKey): RedirectResponse
    {
        ReportFavorite::where('user_id', $request->user()->id)
            ->where('report_key', $reportKey)
            ->first()
            ?->delete();

        return back();
    }
}
