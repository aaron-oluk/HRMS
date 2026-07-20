<?php

namespace App\Actions\ESignature;

use App\Models\SignableDocument;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\ESignature\PdfSigning;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SendDocumentForSignature
{
    public function __construct(protected PdfSigning $pdfSigning) {}

    public function handle(User $uploader, User $signer, string $title, UploadedFile $file): SignableDocument
    {
        $path = $file->store('signable-documents', 'local');
        $pageCount = $this->pdfSigning->pageCount(Storage::disk('local')->path($path));

        $document = SignableDocument::create([
            'uploaded_by' => $uploader->id,
            'signer_user_id' => $signer->id,
            'title' => $title,
            'original_path' => $path,
            'page_count' => $pageCount,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $signer->notify(new GenericNotification(
            title: 'Document to sign: '.$document->title,
            message: "{$uploader->name} sent you a document to sign.",
            icon: 'bx-file-blank',
            url: route('documents.show', $document),
        ));

        return $document;
    }
}
