<?php

namespace App\Actions\ESignature;

use App\Models\User;
use App\Support\Signature\BackgroundRemover;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadSignature
{
    public function __construct(protected BackgroundRemover $backgroundRemover) {}

    public function handle(User $user, UploadedFile $file): User
    {
        $rawPath = $file->store('signatures/raw', 'local');
        $transparentPath = 'signatures/'.Str::uuid().'.png';

        $this->backgroundRemover->process(
            Storage::disk('local')->path($rawPath),
            Storage::disk('local')->path($transparentPath),
        );

        Storage::disk('local')->delete($rawPath);

        if ($user->signature_path) {
            Storage::disk('local')->delete($user->signature_path);
        }

        $user->update(['signature_path' => $transparentPath]);

        return $user;
    }
}
