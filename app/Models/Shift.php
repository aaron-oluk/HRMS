<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    /** @use HasFactory<ShiftFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'name',
        'start_time',
        'end_time',
        'break_minutes',
        'status',
    ];

    protected $attributes = [
        'break_minutes' => 0,
        'status' => 'active',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function formattedStartTime(): string
    {
        return Carbon::parse($this->start_time)->format('H:i');
    }

    public function formattedEndTime(): string
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }
}
