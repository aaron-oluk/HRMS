<?php

namespace App\Http\Controllers\Web;

use App\Actions\ESignature\UploadSignature;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignatureUploadRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SignatureController extends Controller
{
    public function edit(): View
    {
        return view('documents.signature');
    }

    public function store(SignatureUploadRequest $request, UploadSignature $uploadSignature): RedirectResponse
    {
        $uploadSignature->handle($request->user(), $request->file('signature'));

        return redirect()->route('documents.signature.edit')->with('status', 'Signature saved.');
    }

    public function show(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->signature_path, 404);

        return response()->file(Storage::disk('local')->path($request->user()->signature_path), [
            'Content-Type' => 'image/png',
        ]);
    }
}
