<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutoryPayeBand extends Model
{
    protected $fillable = [
        'floor',
        'rate',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'decimal:2',
            'rate' => 'decimal:4',
        ];
    }
}
