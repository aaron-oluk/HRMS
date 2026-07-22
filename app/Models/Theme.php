<?php

namespace App\Models;

use Database\Factories\ThemeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    /** @use HasFactory<ThemeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color_50',
        'color_100',
        'color_500',
        'color_600',
        'color_700',
        'color_800',
        'font_stack',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public static function default(): self
    {
        return static::where('is_default', true)->firstOrFail();
    }
}
