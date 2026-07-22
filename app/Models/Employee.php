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

    public function payrollRunLines(): HasMany
    {
        return $this->hasMany(PayrollRunLine::class);
    }

    public function hrCases(): HasMany
    {
        return $this->hasMany(HrCase::class);
    }

    public function performanceGoals(): HasMany
    {
        return $this->hasMany(PerformanceGoal::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function oneOnOnes(): HasMany
    {
        return $this->hasMany(OneOnOneMeeting::class);
    }

    public function compensationItems(): HasMany
    {
        return $this->hasMany(EmployeeCompensationItem::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(EmployeeNote::class)->latest();
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(EmployeeWarning::class)->latest('issued_at');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(EmployeeWorkExperience::class)->latest('start_date');
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(EmployeeInsurance::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class)->latest('issued_date');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class)->latest('effective_date');
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

    /**
     * Total months of experience: this company's employment history (summed per segment,
     * rather than first-to-last, so a gap from leaving and being rehired isn't counted)
     * plus any prior work experience entries (see EmployeeWorkExperience).
     */
    public function totalExperienceMonths(): int
    {
        $months = 0;

        foreach ($this->employments as $employment) {
            $months += $employment->effective_from->diffInMonths($employment->effective_to ?? now());
        }

        foreach ($this->workExperiences as $experience) {
            $months += $experience->start_date->diffInMonths($experience->end_date ?? now());
        }

        return $months;
    }

    /**
     * "X yr Y mo" — null when there's nothing to show yet.
     */
    public function totalExperienceLabel(): ?string
    {
        $months = $this->totalExperienceMonths();

        if ($months < 1) {
            return null;
        }

        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;

        if ($years === 0) {
            return "{$remainingMonths} mo";
        }

        return $remainingMonths > 0 ? "{$years} yr {$remainingMonths} mo" : "{$years} yr";
    }
}
