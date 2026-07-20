<?php

namespace App\Actions\ESignature;

use App\Models\SignableDocument;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\ESignature\PdfSigning;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignDocument
{
    public function __construct(protected PdfSigning $pdfSigning) {}

    /**
     * @param  array{page_number: int, x: float, y: float, width: float, height: float}  $placement
     */
    public function handle(SignableDocument $document, User $actor, array $placement): SignableDocument
    {
        if ($document->signer_user_id !== $actor->id) {
            throw new AuthorizationException('You may only sign documents sent to you.');
        }

        if ($document->status !== 'sent') {
            throw ValidationException::withMessages([
                'status' => 'This document is not awaiting your signature.',
            ]);
        }

        if (! $actor->signature_path) {
            throw ValidationException::withMessages([
                'signature' => 'Upload your signature on your profile before signing.',
            ]);
        }

        $signedPath = 'signable-documents/signed-'.Str::uuid().'.pdf';

        $this->pdfSigning->signAndReassemble(
            sourcePdfPath: Storage::disk('local')->path($document->original_path),
            signatureImagePath: Storage::disk('local')->path($actor->signature_path),
            signPageNumber: $placement['page_number'],
            x: $placement['x'],
            y: $placement['y'],
            width: $placement['width'],
            height: $placement['height'],
            destinationPath: Storage::disk('local')->path($signedPath),
        );

        $document->update([
            'signed_path' => $signedPath,
            'status' => 'signed',
            'signed_at' => now(),
            'sign_page_number' => $placement['page_number'],
            'sign_x' => $placement['x'],
            'sign_y' => $placement['y'],
            'sign_width' => $placement['width'],
            'sign_height' => $placement['height'],
        ]);

        $document->uploader->notify(new GenericNotification(
            title: 'Document signed: '.$document->title,
            message: "{$actor->name} signed the document.",
            icon: 'bx-check-double',
            url: route('documents.show', $document, absolute: false),
        ));

        return $document;
    }
}
