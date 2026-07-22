<?php

namespace Database\Seeders;

use App\Actions\Attendance\RecomputeAttendanceDay;
use App\Actions\Cases\SubmitHrCase;
use App\Actions\Engagement\LaunchSurvey;
use App\Actions\Engagement\SubmitSurveyResponse;
use App\Actions\ESignature\SendDocumentForSignature;
use App\Actions\ESignature\UploadSignature;
use App\Actions\Payroll\GeneratePayrollRun;
use App\Actions\Performance\CreatePerformanceReviewCycle;
use App\Actions\Performance\RequestPeerFeedback;
use App\Actions\Performance\ScheduleOneOnOne;
use App\Actions\Performance\SubmitManagerReview;
use App\Actions\Performance\SubmitSelfReview;
use App\Actions\Tenancy\ProvisionDefaultRoles;
use App\Actions\Tenancy\SeedDefaultStatutoryConfig;
use App\Models\Area;
use App\Models\Branch;
use App\Models\ClockEvent;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\Grade;
use App\Models\HrCase;
use App\Models\JobRequisition;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\PerformanceReviewCycle;
use App\Models\Position;
use App\Models\Shift;
use App\Models\SignableDocument;
use App\Models\StatutoryPayeBand;
use App\Models\Survey;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Imagick;
use ImagickDraw;
use ImagickPixel;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'aloflux-demo'],
            [
                'name' => 'Aloflux Demo Ltd', 'status' => 'active', 'timezone' => 'Africa/Kampala', 'currency' => 'UGX',
                // Segmented + a non-default theme, so both new features are visible out of
                // the box rather than only reachable by manually opting in after seeding.
                'structure' => 'segmented',
                'theme_id' => Theme::where('slug', 'ocean')->value('id'),
            ]
        );

        app(TenantContext::class)->set($tenant);

        $roles = app(ProvisionDefaultRoles::class)->handle($tenant);

        if (! StatutoryPayeBand::where('tenant_id', $tenant->id)->exists()) {
            app(SeedDefaultStatutoryConfig::class)->handle($tenant);
        }

        $entity = Entity::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Aloflux Demo Ltd'],
            [
                'registration_number' => 'REG-100234',
                'tax_identification_number' => '1000234567',
                'nssf_employer_number' => 'NSSF-77812',
                'address' => 'Plot 14, Acacia Avenue, Kampala',
                'currency' => 'UGX',
                'status' => 'active',
            ]
        );

        $departments = collect(['Human Resources', 'Finance', 'Engineering', 'Sales'])
            ->mapWithKeys(fn (string $name) => [$name => Department::firstOrCreate(
                ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => $name],
                ['code' => strtoupper(substr($name, 0, 3))]
            )]);

        $positions = collect([
            'HR Manager' => 'Human Resources',
            'Payroll Officer' => 'Finance',
            'Software Engineer' => 'Engineering',
            'Sales Executive' => 'Sales',
        ])->mapWithKeys(fn (string $department, string $title) => [$title => Position::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'title' => $title],
            ['department_id' => $departments[$department]->id]
        )]);

        $grade = Grade::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Grade 5'],
            ['level' => 5, 'min_salary' => 1500000, 'max_salary' => 3500000]
        );

        // Segmented-structure demo data: one Area grouping two Branches, so the Area/Branch
        // Manager roles and the location-restricted employee search have something real to
        // scope against out of the box.
        $centralArea = Area::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Central Region'],
            ['code' => 'CENTRAL']
        );

        $kampalaBranch = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Kampala Branch'],
            ['code' => 'KLA', 'area_id' => $centralArea->id, 'address' => 'Plot 14, Acacia Avenue, Kampala']
        );

        $entebbeBranch = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Entebbe Branch'],
            ['code' => 'EBB', 'area_id' => $centralArea->id, 'address' => 'Berkeley Road, Entebbe']
        );

        $hrAdminEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00001'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Grace',
                'last_name' => 'Namu',
                'gender' => 'female',
                'date_of_birth' => '1988-04-12',
                'national_id_number' => 'CM88041200001',
                'nssf_number' => 'NSSF-000001',
                'phone' => '+256700000001',
                'personal_email' => 'grace.namu@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $hrAdminEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'department_id' => $departments['Human Resources']->id,
                'position_id' => $positions['HR Manager']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 3200000,
                'effective_from' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $hrAdmin = User::firstOrCreate(
            ['email' => 'admin@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $hrAdminEmployee->id,
                'name' => 'Grace Namu',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $hrAdmin->syncRoles([$roles['HR Admin']]);

        $managerEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00002'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Peter',
                'last_name' => 'Okello',
                'gender' => 'male',
                'date_of_birth' => '1985-09-03',
                'national_id_number' => 'CM85090300002',
                'nssf_number' => 'NSSF-000002',
                'phone' => '+256700000002',
                'personal_email' => 'peter.okello@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $managerEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'department_id' => $departments['Engineering']->id,
                'position_id' => $positions['Software Engineer']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 2800000,
                'effective_from' => now()->subYear()->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $managerEmployee->id,
                'name' => 'Peter Okello',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $manager->syncRoles([$roles['Team Lead']]);

        $teamMembers = Employee::factory()
            ->count(6)
            ->for($entity)
            ->create(['tenant_id' => $tenant->id])
            ->each(function (Employee $employee, int $index) use ($tenant, $entity, $departments, $positions, $grade, $managerEmployee): void {
                Employment::factory()->create([
                    'tenant_id' => $tenant->id,
                    'employee_id' => $employee->id,
                    'entity_id' => $entity->id,
                    'department_id' => $departments['Engineering']->id,
                    'position_id' => $positions['Software Engineer']->id,
                    'grade_id' => $grade->id,
                    'reporting_to_employee_id' => $index < 2 ? $managerEmployee->id : null,
                ]);
            });

        $deptManagerEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00003'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Sarah',
                'last_name' => 'Nabirye',
                'gender' => 'female',
                'date_of_birth' => '1982-11-20',
                'national_id_number' => 'CM82112000003',
                'nssf_number' => 'NSSF-000003',
                'phone' => '+256700000003',
                'personal_email' => 'sarah.nabirye@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $deptManagerEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'department_id' => $departments['Finance']->id,
                'position_id' => $positions['Payroll Officer']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 3000000,
                'effective_from' => now()->subYears(3)->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $deptManager = User::firstOrCreate(
            ['email' => 'dept-manager@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $deptManagerEmployee->id,
                'name' => 'Sarah Nabirye',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $deptManager->syncRoles([$roles['Department Manager']]);

        $branchManagerEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00004'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Tom',
                'last_name' => 'Byaruhanga',
                'gender' => 'male',
                'date_of_birth' => '1987-06-15',
                'phone' => '+256700000004',
                'personal_email' => 'tom.byaruhanga@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $branchManagerEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'branch_id' => $kampalaBranch->id,
                'department_id' => $departments['Sales']->id,
                'position_id' => $positions['Sales Executive']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 2400000,
                'effective_from' => now()->subYear()->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $branchManager = User::firstOrCreate(
            ['email' => 'branch-manager@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $branchManagerEmployee->id,
                'name' => 'Tom Byaruhanga',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $branchManager->syncRoles([$roles['Branch Manager']]);

        $areaManagerEmployee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00005'],
            [
                'entity_id' => $entity->id,
                'first_name' => 'Irene',
                'last_name' => 'Katusiime',
                'gender' => 'female',
                'date_of_birth' => '1984-02-28',
                'phone' => '+256700000005',
                'personal_email' => 'irene.katusiime@example.com',
                'nationality' => 'Ugandan',
                'status' => 'active',
            ]
        );

        Employment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $areaManagerEmployee->id, 'effective_to' => null],
            [
                'entity_id' => $entity->id,
                'branch_id' => $entebbeBranch->id,
                'department_id' => $departments['Sales']->id,
                'position_id' => $positions['Sales Executive']->id,
                'grade_id' => $grade->id,
                'basic_salary' => 2900000,
                'effective_from' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'reason' => 'initial',
            ]
        );

        $areaManager = User::firstOrCreate(
            ['email' => 'area-manager@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $areaManagerEmployee->id,
                'name' => 'Irene Katusiime',
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $areaManager->syncRoles([$roles['Area Manager']]);

        $financeEmployee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
        Employment::factory()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $financeEmployee->id,
            'entity_id' => $entity->id,
            'department_id' => $departments['Finance']->id,
            'position_id' => $positions['Payroll Officer']->id,
            'grade_id' => $grade->id,
        ]);

        foreach ([
            ['email' => 'hr-manager@aloflux-demo.test', 'name' => 'Josephine Kobusingye', 'role' => 'HR Manager'],
            ['email' => 'hr-specialist@aloflux-demo.test', 'name' => 'David Ssemwogerere', 'role' => 'HR Specialist'],
            ['email' => 'auditor@aloflux-demo.test', 'name' => 'Ruth Achieng', 'role' => 'Auditor'],
            ['email' => 'accountant@aloflux-demo.test', 'name' => 'Moses Tumwine', 'role' => 'Accountant'],
            ['email' => 'executive@aloflux-demo.test', 'name' => 'Patrick Mugisha', 'role' => 'Executive'],
        ] as $demoUser) {
            $user = User::firstOrCreate(
                ['email' => $demoUser['email']],
                ['tenant_id' => $tenant->id, 'name' => $demoUser['name'], 'password' => 'password', 'status' => 'active']
            );
            $user->syncRoles([$roles[$demoUser['role']]]);
        }

        $leaveTypes = collect([
            ['name' => 'Annual Leave', 'code' => 'ANNUAL', 'default_days_per_year' => 21, 'max_carry_forward_days' => 5],
            ['name' => 'Sick Leave', 'code' => 'SICK', 'default_days_per_year' => 10, 'max_carry_forward_days' => null],
            ['name' => 'Unpaid Leave', 'code' => 'UNPAID', 'is_paid' => false, 'default_days_per_year' => 30, 'max_carry_forward_days' => null],
        ])->mapWithKeys(fn (array $attributes) => [$attributes['code'] => LeaveType::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'code' => $attributes['code']],
            [...$attributes, 'tenant_id' => $tenant->id, 'entity_id' => $entity->id]
        )]);

        LeaveRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $managerEmployee->id, 'start_date' => now()->addWeek()->startOfWeek(6)->toDateString()],
            [
                'leave_type_id' => $leaveTypes['ANNUAL']->id,
                'end_date' => now()->addWeek()->startOfWeek(6)->addDays(4)->toDateString(),
                'days' => 5,
                'reason' => 'Family trip',
                'status' => 'pending',
            ]
        );

        $reportEmployee = $teamMembers->first();

        $employeeUser = User::firstOrCreate(
            ['email' => 'employee@aloflux-demo.test'],
            [
                'tenant_id' => $tenant->id,
                'employee_id' => $reportEmployee->id,
                'name' => $reportEmployee->fullName(),
                'password' => 'password',
                'status' => 'active',
            ]
        );
        $employeeUser->syncRoles([$roles['Employee']]);

        LeaveRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'start_date' => now()->addDays(3)->toDateString()],
            [
                'leave_type_id' => $leaveTypes['SICK']->id,
                'end_date' => now()->addDays(3)->toDateString(),
                'days' => 1,
                'reason' => 'Doctor appointment',
                'status' => 'pending',
            ]
        );

        $shift = Shift::firstOrCreate(
            ['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'name' => 'Day Shift'],
            ['start_time' => '08:00', 'end_time' => '17:00', 'break_minutes' => 60]
        );

        $recomputeAttendanceDay = app(RecomputeAttendanceDay::class);
        foreach ([$hrAdminEmployee, $managerEmployee, $reportEmployee] as $employee) {
            for ($daysAgo = 4; $daysAgo >= 1; $daysAgo--) {
                $date = now()->subDays($daysAgo);
                if ($date->isWeekend()) {
                    continue;
                }

                $clockIn = $date->copy()->setTime(8, fake()->numberBetween(0, 20));
                $clockOut = $date->copy()->setTime(17, fake()->numberBetween(0, 15));

                ClockEvent::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'type' => 'clock_in', 'occurred_at' => $clockIn]
                );
                ClockEvent::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'type' => 'clock_out', 'occurred_at' => $clockOut]
                );

                $recomputeAttendanceDay->handle($employee, $date);
            }
        }

        OvertimeRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'date' => now()->subDay()->toDateString()],
            ['hours' => 2.5, 'reason' => 'Production incident', 'status' => 'pending']
        );

        if (! PayrollRun::where('entity_id', $entity->id)->whereDate('period_month', now()->startOfMonth())->exists()) {
            app(GeneratePayrollRun::class)->handle($entity, now()->toDateString(), $hrAdmin);
        }

        $requisition = JobRequisition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'department_id' => $departments['Engineering']->id, 'title' => 'Backend Engineer'],
            [
                'entity_id' => $entity->id,
                'position_id' => $positions['Software Engineer']->id,
                'headcount' => 2,
                'status' => 'open',
                'requested_by' => $manager->id,
                'description' => 'Growing the platform team — 2 openings.',
                'opened_at' => now()->subWeek(),
            ]
        );

        foreach ([
            ['first_name' => 'Aisha', 'last_name' => 'Nantongo', 'email' => 'aisha.nantongo@example.com', 'source' => 'referral', 'status' => 'interview'],
            ['first_name' => 'Brian', 'last_name' => 'Kato', 'email' => 'brian.kato@example.com', 'source' => 'job board', 'status' => 'applied'],
            ['first_name' => 'Carol', 'last_name' => 'Auma', 'email' => 'carol.auma@example.com', 'source' => 'linkedin', 'status' => 'hired'],
        ] as $candidate) {
            $requisition->candidates()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $candidate['email']],
                $candidate
            );
        }

        if (! PerformanceReviewCycle::where('tenant_id', $tenant->id)->exists()) {
            $cycle = app(CreatePerformanceReviewCycle::class)->handle([
                'name' => now()->year.' H'.(now()->month <= 6 ? 1 : 2),
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
            ]);

            // One review self-submitted (awaiting the manager's half, visible in their Inbox)...
            $reportReview = $cycle->reviews()->where('employee_id', $reportEmployee->id)->first();
            if ($reportReview) {
                app(SubmitSelfReview::class)->handle($reportReview, $reportEmployee, [
                    'rating' => 4,
                    'comments' => 'Shipped the Q2 migration ahead of schedule.',
                ]);
            }

            // ...and one fully completed, to show the finished state.
            $hrAdminReview = $cycle->reviews()->where('employee_id', $hrAdminEmployee->id)->first();
            if ($hrAdminReview) {
                app(SubmitSelfReview::class)->handle($hrAdminReview, $hrAdminEmployee, [
                    'rating' => 5,
                    'comments' => 'Rolled out the new onboarding process tenant-wide.',
                ]);
                app(SubmitManagerReview::class)->handle($hrAdminReview, $hrAdmin, [
                    'rating' => 5,
                    'comments' => 'Strong quarter, exceeded expectations.',
                ]);
            }

            // Goal, peer feedback nomination, and a scheduled 1-on-1 for the same report,
            // to demonstrate the performance-depth features against a real review cycle.
            if (! $reportEmployee->performanceGoals()->exists()) {
                $reportEmployee->performanceGoals()->create([
                    'performance_review_cycle_id' => $cycle->id,
                    'title' => 'Ship the API v2 migration',
                    'target_value' => 100,
                    'current_value' => 60,
                    'unit' => '%',
                    'status' => 'on_track',
                    'due_date' => now()->addMonths(2)->toDateString(),
                ]);
            }

            if ($reportReview && ! $reportReview->feedbackRequests()->exists()) {
                app(RequestPeerFeedback::class)->handle($reportReview, $deptManagerEmployee, $manager);
            }

            if (! $reportEmployee->oneOnOnes()->exists()) {
                app(ScheduleOneOnOne::class)->handle($reportEmployee->id, $manager, [
                    'scheduled_at' => now()->addWeek()->toDateTimeString(),
                    'agenda' => 'Career growth check-in',
                ]);
            }
        }

        if (! Survey::where('tenant_id', $tenant->id)->exists()) {
            $survey = app(LaunchSurvey::class)->handle([
                'title' => 'Quarterly pulse check',
                'description' => 'Two quick questions — takes a minute.',
                'is_anonymous' => true,
                'questions' => [
                    ['text' => 'How supported do you feel by your manager?', 'type' => 'rating'],
                    ['text' => 'Anything HR should know about right now?', 'type' => 'text'],
                ],
            ], $hrAdmin);

            app(SubmitSurveyResponse::class)->handle($survey, $managerEmployee, [
                ['question_id' => $survey->questions[0]->id, 'rating_value' => 4],
                ['question_id' => $survey->questions[1]->id, 'text_value' => 'Things are going well overall.'],
            ]);
        }

        if (! HrCase::where('tenant_id', $tenant->id)->exists()) {
            app(SubmitHrCase::class)->handle($reportEmployee, [
                'category' => 'payroll',
                'subject' => 'Question about my last payslip',
                'description' => 'The NSSF deduction looks different from last month — could someone confirm it\'s correct?',
            ]);
        }

        if (! $manager->signature_path) {
            $signatureSource = tempnam(sys_get_temp_dir(), 'demo-signature').'.png';
            $signatureImage = new Imagick;
            $signatureImage->newImage(300, 120, new ImagickPixel('white'));
            $signatureImage->setImageFormat('png');
            $draw = new ImagickDraw;
            $draw->setStrokeColor(new ImagickPixel('black'));
            $draw->setStrokeWidth(4);
            $draw->bezier([['x' => 20, 'y' => 90], ['x' => 100, 'y' => 20], ['x' => 200, 'y' => 100], ['x' => 280, 'y' => 30]]);
            $signatureImage->drawImage($draw);
            $signatureImage->writeImage($signatureSource);
            $signatureImage->clear();
            $signatureImage->destroy();

            app(UploadSignature::class)->handle($manager, new UploadedFile($signatureSource, 'signature.png', 'image/png', null, true));
            @unlink($signatureSource);
        }

        if (! SignableDocument::where('tenant_id', $tenant->id)->exists()) {
            $documentSource = tempnam(sys_get_temp_dir(), 'demo-document').'.pdf';
            $documentImage = new Imagick;
            $page = new Imagick;
            $page->newImage(850, 1100, new ImagickPixel('white'));
            $page->setImageFormat('png');
            $draw = new ImagickDraw;
            $draw->setFillColor(new ImagickPixel('black'));
            $draw->setFontSize(28);
            $draw->annotation(60, 100, 'Aloflux Demo Ltd — Offer Letter');
            $draw->setFontSize(16);
            $draw->annotation(60, 160, 'This letter confirms your offer of employment.');
            $page->drawImage($draw);
            $documentImage->addImage($page);
            $documentImage->setImageFormat('pdf');
            $documentImage->writeImages($documentSource, true);
            $documentImage->clear();
            $documentImage->destroy();
            $page->clear();
            $page->destroy();

            app(SendDocumentForSignature::class)->handle(
                $hrAdmin,
                $employeeUser,
                'Offer Letter — '.$reportEmployee->fullName(),
                new UploadedFile($documentSource, 'offer-letter.pdf', 'application/pdf', null, true)
            );
            @unlink($documentSource);
        }
    }
}
