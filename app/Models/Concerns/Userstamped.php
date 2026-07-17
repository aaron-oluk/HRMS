<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Userstamped
{
    public static function bootUserstamped(): void
    {
        static::creating(function ($model): void {
            if ($model->created_by === null) {
                $model->created_by = auth()->id();
            }

            if ($model->updated_by === null) {
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model): void {
            $model->updated_by = auth()->id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
