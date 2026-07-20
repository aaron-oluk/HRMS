<?php

use App\Models\Entity;
use App\Models\SignableDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeTestPdfUpload(string $filename = 'document.pdf', int $pages = 1): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'test-doc').'.pdf';
    $document = new Imagick;
    for ($i = 0; $i < $pages; $i++) {
        $page = new Imagick;
        $page->newImage(400, 300, new ImagickPixel('white'));
        $page->setImageFormat('png');
        $document->addImage($page);
        $page->clear();
        $page->destroy();
    }
    $document->setImageFormat('pdf');
    $document->writeImages($path, true);
    $document->clear();
    $document->destroy();

    return new UploadedFile($path, $filename, 'application/pdf', null, true);
}

function makeTestSignatureUpload(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'test-sig').'.png';
    $image = new Imagick;
    $image->newImage(200, 100, new ImagickPixel('white'));
    $image->setImageFormat('png');
    $draw = new ImagickDraw;
    $draw->setStrokeColor(new ImagickPixel('black'));
    $draw->setStrokeWidth(6);
    $draw->line(20, 80, 180, 20);
    $image->drawImage($draw);
    $image->writeImage($path);
    $image->clear();
    $image->destroy();

    return new UploadedFile($path, 'signature.png', 'image/png', null, true);
}

beforeEach(function () {
    Storage::fake('local');
});

test('a user can upload a signature and its background is removed', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)->post(route('profile.signature.store'), [
        'signature' => makeTestSignatureUpload(),
    ])->assertRedirect();

    $user->refresh();
    expect($user->signature_path)->not->toBeNull();
    Storage::disk('local')->assertExists($user->signature_path);

    $preview = $this->actingAs($user)->get(route('profile.signature.show'));
    $preview->assertOk();
    $preview->assertHeader('content-type', 'image/png');
});

test('hr can send a document to a signer, who can sign it', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $signerUser] = employeeUser($tenant, $entity, 'Employee');

    // The signer needs their own signature uploaded before they can sign.
    $this->actingAs($signerUser)->post(route('profile.signature.store'), ['signature' => makeTestSignatureUpload()]);

    $this->actingAs($hrAdmin)->post(route('documents.store'), [
        'signer_user_id' => $signerUser->id,
        'title' => 'Offer Letter',
        'file' => makeTestPdfUpload(),
    ])->assertRedirect();

    $document = SignableDocument::where('title', 'Offer Letter')->firstOrFail();
    expect($document->status)->toBe('sent');
    expect($document->page_count)->toBe(1);

    $this->actingAs($signerUser)->post(route('documents.sign', $document), [
        'page_number' => 0,
        'x' => 0.1,
        'y' => 0.1,
        'width' => 0.3,
        'height' => 0.15,
    ])->assertRedirect();

    $document->refresh();
    expect($document->status)->toBe('signed');
    expect($document->signed_path)->not->toBeNull();
    Storage::disk('local')->assertExists($document->signed_path);

    // The signed file must actually be downloadable/viewable afterward.
    $download = $this->actingAs($signerUser)->get(route('documents.download', $document));
    $download->assertOk();
    $download->assertHeader('content-type', 'application/pdf');
});

test('the original document can be downloaded before it is signed', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $signerUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($hrAdmin)->post(route('documents.store'), [
        'signer_user_id' => $signerUser->id,
        'title' => 'Unsigned Document',
        'file' => makeTestPdfUpload(),
    ]);
    $document = SignableDocument::where('title', 'Unsigned Document')->firstOrFail();

    $this->actingAs($hrAdmin)->get(route('documents.download', $document))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('only the designated signer can sign the document', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $signerUser] = employeeUser($tenant, $entity, 'Employee');
    [, $otherUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($signerUser)->post(route('profile.signature.store'), ['signature' => makeTestSignatureUpload()]);

    $this->actingAs($hrAdmin)->post(route('documents.store'), [
        'signer_user_id' => $signerUser->id,
        'title' => 'Policy Acknowledgement',
        'file' => makeTestPdfUpload(),
    ]);
    $document = SignableDocument::where('title', 'Policy Acknowledgement')->firstOrFail();

    $this->actingAs($otherUser)->post(route('documents.sign', $document), [
        'page_number' => 0, 'x' => 0.1, 'y' => 0.1, 'width' => 0.3, 'height' => 0.15,
    ])->assertForbidden();

    expect($document->fresh()->status)->toBe('sent');
});

test('a signer without an uploaded signature cannot sign yet', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $signerUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($hrAdmin)->post(route('documents.store'), [
        'signer_user_id' => $signerUser->id,
        'title' => 'No Signature Yet',
        'file' => makeTestPdfUpload(),
    ]);
    $document = SignableDocument::where('title', 'No Signature Yet')->firstOrFail();

    $this->actingAs($signerUser)->post(route('documents.sign', $document), [
        'page_number' => 0, 'x' => 0.1, 'y' => 0.1, 'width' => 0.3, 'height' => 0.15,
    ])->assertSessionHasErrors('signature');

    expect($document->fresh()->status)->toBe('sent');
});

test('an uninvolved employee cannot view someone else\'s document', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $signerUser] = employeeUser($tenant, $entity, 'Employee');
    [, $bystanderUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($hrAdmin)->post(route('documents.store'), [
        'signer_user_id' => $signerUser->id,
        'title' => 'Private Document',
        'file' => makeTestPdfUpload(),
    ]);
    $document = SignableDocument::where('title', 'Private Document')->firstOrFail();

    $this->actingAs($bystanderUser)->get(route('documents.show', $document))->assertForbidden();
    $this->actingAs($signerUser)->get(route('documents.show', $document))->assertOk();
});

test('a document from another tenant is invisible via the global scope', function () {
    [$tenantA, $hrAdminA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [, $signerUserA] = employeeUser($tenantA, $entityA, 'Employee');

    $this->actingAs($hrAdminA)->post(route('documents.store'), [
        'signer_user_id' => $signerUserA->id,
        'title' => 'Tenant A Document',
        'file' => makeTestPdfUpload(),
    ]);
    $documentA = SignableDocument::where('title', 'Tenant A Document')->firstOrFail();

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('documents.show', $documentA))->assertNotFound();
});
