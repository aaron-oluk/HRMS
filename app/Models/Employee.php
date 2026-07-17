<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'employee_number',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'national_id_number',
        'nssf_number',
        'tin_number',
        'phone',
        'personal_email',
        'marital_status',
        'nationality',
        'photo_path',
        'status',
        'consent_captured_at',
        'consent_version',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'consent_captured_at' => 'datetime',
        ];
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->other_names} {$this->last_name}");
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class)->latest('effective_from');
    }

    public function currentEmployment(): HasOne
    {
        return $this->hasOne(Employment::class)
            ->whereNull('effective_to')
            ->where('status', 'active')
            ->latestOfMany('effective_from');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function mobileMoneyAccounts(): HasMany
    {
        return $this->hasMany(EmployeeMobileMoney::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function clockEvents(): HasMany
    {
        return $this->hasMany(ClockEvent::class);
    }

    public function attendanceDays(): HasMany
    {
        return $this->hasMany(AttendanceDay::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    /**
     * Current employment rows of employees who report to this employee.
     */
    public function directReportEmployments(): HasMany
    {
        return $this->hasMany(Employment::class, 'reporting_to_employee_id')
            ->whereNull('effective_to')
            ->where('status', 'active');
    }
}
