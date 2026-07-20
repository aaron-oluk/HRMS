<?php

namespace App\Http\Controllers\Web;

use App\Actions\ESignature\SendDocumentForSignature;
use App\Actions\ESignature\SignDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignableDocumentRequest;
use App\Http\Requests\SignDocumentRequest;
use App\Models\SignableDocument;
use App\Models\User;
use App\Support\ESignature\PdfSigning;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SignableDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = SignableDocument::with('uploader', 'signer')->latest();

        if (! $user->can('esignature.send')) {
            $query->where('signer_user_id', $user->id);
        }

        return view('documents.index', ['documents' => $query->paginate(15)]);
    }

    public function create(): View
    {
        return view('documents.create', ['users' => User::orderBy('name')->get()]);
    }

    public function store(SignableDocumentRequest $request, SendDocumentForSignature $sendDocumentForSignature): RedirectResponse
    {
        $signer = User::findOrFail($request->validated('signer_user_id'));
        $document = $sendDocumentForSignature->handle($request->user(), $signer, $request->validated('title'), $request->file('file'));

        return redirect()->route('documents.show', $document)->with('status', 'Document sent for signature.');
    }

    public function show(Request $request, SignableDocument $document): View
    {
        $user = $request->user();
        abort_unless(
            $user->can('esignature.send') || $user->id === $document->signer_user_id || $user->id === $document->uploaded_by,
            403
        );

        return view('documents.show', [
            'document' => $document,
            'canSign' => $user->id === $document->signer_user_id && $document->status === 'sent',
            'hasSignature' => (bool) $user->signature_path,
        ]);
    }

    public function page(Request $request, SignableDocument $document, int $page, PdfSigning $pdfSigning): Response
    {
        $user = $request->user();
        abort_unless(
            $user->can('esignature.send') || $user->id === $document->signer_user_id || $user->id === $document->uploaded_by,
            403
        );

        $blob = $pdfSigning->rasterizePage(Storage::disk('local')->path($document->original_path), $page);

        return response($blob, 200, ['Content-Type' => 'image/png']);
    }

    public function sign(SignDocumentRequest $request, SignableDocument $document, SignDocument $signDocument): RedirectResponse
    {
        $signDocument->handle($document, $request->user(), $request->validated());

        return redirect()->route('documents.show', $document)->with('status', 'Document signed.');
    }

    public function download(Request $request, SignableDocument $document): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless(
            $user->can('esignature.send') || $user->id === $document->signer_user_id || $user->id === $document->uploaded_by,
            403
        );

        $path = $document->signed_path ?? $document->original_path;

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->title.'.pdf"',
        ]);
    }
}
