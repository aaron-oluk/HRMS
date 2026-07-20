<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\SignableDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignableDocument extends Model
{
    /** @use HasFactory<SignableDocumentFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'uploaded_by',
        'signer_user_id',
        'title',
        'original_path',
        'signed_path',
        'status',
        'page_count',
        'sign_page_number',
        'sign_x',
        'sign_y',
        'sign_width',
        'sign_height',
        'sent_at',
        'signed_at',
        'declined_at',
        'decline_reason',
    ];

    protected $attributes = [
        'status' => 'draft',
        'page_count' => 1,
    ];

    protected function casts(): array
    {
        return [
            'sign_x' => 'decimal:4',
            'sign_y' => 'decimal:4',
            'sign_width' => 'decimal:4',
            'sign_height' => 'decimal:4',
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }
}
