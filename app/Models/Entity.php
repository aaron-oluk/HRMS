<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'name',
        'registration_number',
        'tax_identification_number',
        'nssf_employer_number',
        'address',
        'currency',
        'status',
    ];

    protected $attributes = [
        'currency' => 'UGX',
        'status' => 'active',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
