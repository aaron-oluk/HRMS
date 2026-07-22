# Aloflux HRMS

A multi-tenant Human Resource Management System built for Uganda-based organizations, covering the full employee lifecycle — from recruitment through payroll, attendance, performance, and offboarding — with statutory PAYE/NSSF calculations, role-based access control, and a full audit trail.

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Database | PostgreSQL |
| Frontend | Blade + Tailwind CSS v4 + Alpine.js |
| Auth | Laravel Fortify (web), Laravel Sanctum (API) |
| Authorization | spatie/laravel-permission (roles + permissions) |
| Testing | Pest 3 / PHPUnit 11 |
| Tooling | Laravel Boost (MCP dev tools), Laravel Pint |

## Features

### Organization structure
- Multi-entity (multi-company) tenants, with optional area/branch segmentation
- Departments, positions, and grades
- Custom theming per tenant

### Employee records
- Core profile: personal details, national ID/NSSF/TIN, photo, consent tracking
- Employment history — effective-dated position/department/salary changes at the current company
- **Prior work experience** — career history before joining, feeding into a computed total-tenure badge
- Documents (contracts, national ID, certificates, achievements, other), bank accounts, mobile money accounts
- Internal notes (HR-only)
- **Warnings** — structured disciplinary records (verbal/written/final), with employee self-acknowledgement via a dedicated "My Warnings" page
- **Insurance** — medical/life/dental policies tied to the employee, shown alongside Personal Information
- Compensation items (allowances/benefits) feeding into payroll

### Payroll
- Payroll runs per entity/period, generated from active employments
- Statutory PAYE and NSSF (employee + employer) calculated per Uganda's published bands
- **Advances** — employee loans with a monthly repayment schedule that auto-decrements each payroll run until settled
- **Deductions** — one-time or recurring deductions (e.g. union dues, damaged equipment), applied automatically and itemized on every payslip
- Draft → pending approval → approved → disbursed workflow
- Self-service payslip history ("My Payslips")

### Attendance & time off
- Shift definitions, clock in/out (web + mobile API), daily attendance computation
- Overtime requests with approval workflow
- Leave types, leave requests, balances, and approvals
- Public holiday calendar

### Recruitment
- Job requisitions tagged as **Career** or **Internship**
- Candidate pipeline: Advertising → Review → Shortlisting → Interviews → Negotiations & Offers → Contracts & Appointments → Probation (plus Rejected)
- PII-gated candidate contact details

### Performance
- Review cycles, self/manager reviews, peer feedback requests
- 1:1 meetings
- Performance goals ("targets at work")

### Engagement & cases
- Employee engagement surveys with aggregate results
- HR case management (grievances, requests) with comments and resolution tracking

### E-signature
- Send documents for signature, capture signatures, track completion

### Reports
- Payroll cost summary, recruitment pipeline funnel, and other operational reports (CSV export supported)

### Platform administration
- Super-admin tenant management and impersonation, independent of tenant-level RBAC

## Role-based access control

Permissions are split across two layers:
- **Route-level**, enforced by role (e.g. `role:HR Admin|HR Manager`)
- **View-level**, enforced by fine-grained Spatie permissions (`@can(...)` in Blade)

Default roles provisioned per tenant:

| Role | Scope |
|---|---|
| HR Admin | Full HR + payroll + system administration |
| HR Manager | Full HR operations tenant-wide, except system/user administration |
| HR Specialist | HR operations, no payroll or salary visibility |
| Department Manager | Scoped to their department |
| Branch Manager | Scoped to their branch (segmented tenants only) |
| Area Manager | Scoped to every branch under their area (segmented tenants only) |
| Team Lead | Scoped to direct reports, read-only |
| Auditor | Read-only, tenant-wide, including sensitive fields and access logs |
| Accountant | Payroll run/disburse and financial data only |
| Executive | Read-only, tenant-wide summaries |
| Employee | Self-service only (own payslips, warnings, leave, attendance) |

Tenants can also toggle optional modules on/off: `payroll`, `recruitment`, `performance`, `engagement`, `cases`, `reports`, `esignature`.

## Multi-tenancy

Single database, `tenant_id`-scoped on every tenant-owned table via a global `TenantScope` + `BelongsToTenant` trait. The active tenant is resolved per-request by the `IdentifyTenant` middleware and held in a `TenantContext` singleton.

## Getting started

### Requirements
- PHP 8.2+
- Composer
- PostgreSQL 15+
- Node.js (for asset building)

### Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and point `DB_*` at your local PostgreSQL instance (defaults to a database named `hrms`), then:

```bash
php artisan migrate --seed
npm run build   # or `npm run dev` for a watching dev server
php artisan serve
```

`composer run dev` will also boot the queue listener, log watcher (Pail), and Vite dev server together.

### Demo data

`php artisan migrate --seed` provisions a demo tenant with a full permission catalog and sample data across every module. All demo accounts use the password `password`:

| Email | Role |
|---|---|
| admin@aloflux-demo.test | HR Admin |
| manager@aloflux-demo.test | Team Lead |
| dept-manager@aloflux-demo.test | Department Manager |
| branch-manager@aloflux-demo.test | Branch Manager |
| area-manager@aloflux-demo.test | Area Manager |
| hr-manager@aloflux-demo.test | HR Manager |
| hr-specialist@aloflux-demo.test | HR Specialist |
| auditor@aloflux-demo.test | Auditor |
| accountant@aloflux-demo.test | Accountant |
| executive@aloflux-demo.test | Executive |
| employee@aloflux-demo.test | Employee |

### Rebuilding the database from scratch

Since this is a pre-launch project, migrations may be edited in place rather than superseded by new ones. When in doubt, rebuild fully:

```bash
php artisan migrate:fresh --seed
```

## Testing

```bash
php artisan test --compact
```

Tests run against an in-memory SQLite database (see `phpunit.xml`), so they never touch your local PostgreSQL dev database.

## Code style

```bash
vendor/bin/pint --dirty --format agent
```

## API

A versioned REST API is available under `/api/v1`, authenticated via Laravel Sanctum, intended for the companion mobile app and third-party integrations. Run `php artisan route:list --path=api` to see the full endpoint list.

## Further documentation

See [DOC.md](DOC.md) for the original system specification (architecture rationale, full RBAC permission matrix, non-functional requirements, and build phases).
