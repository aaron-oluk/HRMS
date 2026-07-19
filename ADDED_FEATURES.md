---

# ADDED FEATURES
## Payroll, Recruitment, and Performance Management

---

This document covers three modules added after a competitive benchmark against SeamlessHR (a live commercial HRMS) identified them as gaps: **Payroll & statutory engine**, **Recruitment/ATS**, and **Performance Management**. It follows the same spirit as `DOC.md` — what exists, how it's gated, and what's deliberately not built — but only for what's in this document; `DOC.md` remains the source of truth for everything else.

Two of these modules (Recruitment, Performance) have **no specification in `DOC.md`** — confirmed by full-text search before building. They were designed from scratch to match this codebase's existing conventions (money handling, permission naming, `TeamScope` record scoping, the Action-class pattern) rather than any written spec. Payroll *is* specced in `DOC.md` §3.8/3.9/3.10 and §5.3.7, and is noted below wherever the implementation diverges from that spec.

All three were built as a **"solid working core"**: real, working end-to-end, but deliberately scoped down from full spec depth. Every scope reduction is called out explicitly rather than left implicit.

---

## 1. Payroll & Uganda Statutory Engine

### What it does

- **Generate a payroll run** for an entity + calendar month. Pulls every active `Employment` in that entity and computes PAYE, NSSF (employee + employer), and net pay per employee, using `App\Support\Payroll\StatutoryEngine`.
- **Approval lifecycle**: `draft → pending_approval → approved → disbursed`. Whoever generates a run submits it for approval; a separate approver signs off; disbursement is a final "mark as paid" step.
- **Payslips**: each `PayrollRunLine` row *is* the payslip — no separate model. Employees view their own payslip history on a new **Payslips tab** on their Profile page (`/profile`).
- **Notifications**: marking a run disbursed notifies every affected employee ("Payslip available") via the existing in-app notification system.

### Statutory engine

`App\Support\Payroll\StatutoryEngine` implements:
- **PAYE**: `DOC.md` §3.9 gives only two anchor points — a 235,000 UGX/month free threshold and a 40% top marginal rate. The full band table implemented here is Uganda's standard published monthly PAYE schedule (Income Tax Act, employment income): 0% to 235,000; 10% from 235,001–335,000; 20% from 335,001–410,000; 30% from 410,001–10,000,000; plus a 10% surcharge above 10,000,000 (which is what produces DOC's stated 40% top marginal rate). This is public tax law, not invented, but worth flagging since `DOC.md` itself doesn't spell out the middle bands.
- **NSSF**: 5% employee deduction, 10% employer contribution, both against basic salary — matches `DOC.md` exactly.
- **LST is NOT computed.** `DOC.md` describes it only as "configurable band table per local authority" with zero actual figures given anywhere in the document. Fabricating band values would present invented numbers as authoritative statutory data, so it's left out entirely rather than guessed at.

### Permissions

| Permission | Grant |
|---|---|
| `payroll.view` | Full line-level detail (salary, PAYE, NSSF, net pay per employee). HR Admin, HR Manager, Accountant, Auditor. |
| `payroll.view-team-summary` | Aggregate headcount + totals only, no per-employee salary. Department Manager, Team Lead, Executive (tenant-wide for Executive, since it's already an unscoped role in `TeamScope`; team-scoped for Dept Manager/Team Lead). |
| `payroll.view-own` | An employee's own payslips (Profile page tab). Employee. |
| `payroll.run` | Generate + submit a run for approval. HR Admin, HR Manager, Accountant. |
| `payroll.approve` | Approve a submitted run. HR Admin, HR Manager only. |
| `payroll.disburse` | Mark an approved run as disbursed. HR Admin, HR Manager, Accountant. |

**Deviation from `DOC.md`'s 5.3.7 permission matrix**: that table grants HR Specialist full payroll-run access. This codebase already excludes HR Specialist from all salary visibility (no `employees.view-salary`) — granting them payroll-run access would contradict that existing, tested precedent. HR Specialist gets **no payroll permissions at all** here; the codebase's established salary-access boundary won by design.

### Deliberately not built

- No configurable Pay Components catalog (earnings/deductions line-item builder) — gross pay is `Employment.basic_salary` only. No taxable-benefit modeling (housing/transport/meals).
- DOC's 5-stage lifecycle (Draft→Review→Approved→**Locked**→Disbursed) collapses to 4 stages — "Locked" added nothing "Approved" doesn't already cover in this scope.
- No PDF payslip export ("Download Payslips" in `DOC.md`) — would require adding a PDF-generation dependency (DomPDF or similar), which needs approval per this project's conventions. On-screen view only.
- No real bank-file or mobile-money disbursement integration — "mark as disbursed" is a manual confirmation step (`disbursed_at` timestamp), not a payment rail.
- No Loans & Advances module.

---

## 2. Recruitment / ATS

Greenfield — `DOC.md` has zero mentions of recruitment, applicants, candidates, or hiring anywhere in the document.

### What it does

- **Job requisitions**: a headcount request tied to an entity/department/position, with a status lifecycle (`draft → open → on_hold → closed → filled`).
- **Candidates**: attached to a requisition, moving through a fixed pipeline — `applied → screening → interview → offer → hired / rejected` — via an inline stage-select on the requisition's show page.
- Department Managers see only their own department's requisitions (filtered directly by `department_id` in the controller — not routed through `TeamScope`, which is keyed to `employee_id` and doesn't fit a requisition).

### Permissions

| Permission | Grant |
|---|---|
| `recruitment.view` | HR Admin, HR Manager, HR Specialist (tenant-wide), Department Manager (own department only), Auditor, Executive (tenant-wide, read-only) |
| `recruitment.manage` | Create/edit requisitions, add candidates, move pipeline stage. HR Admin, HR Manager, HR Specialist. |
| `recruitment.view-candidate-pii` | Email/phone visible. Without it, `recruitment.view` holders see name + stage only. HR Admin, HR Manager, HR Specialist, Auditor. |

Team Lead, Accountant, and Employee get none of these — there's no external candidate self-service portal, consistent with this system's no-public-signup posture (see the tenant onboarding console: companies are onboarded by a platform admin, not self-service; the same philosophy applies here).

### Deliberately not built

- **"Hire" does not auto-create an Employee record.** Marking a candidate `hired` is just that status transition. Auto-provisioning a full employee (position, grade, effective-dated employment) is exactly the kind of decision that belongs in the existing Employee-create flow, not a one-click shortcut bolted onto a candidate row. A natural next step, not built here.
- No resume file upload UI (the `resume_path` column exists on `candidates` for future use, but nothing populates it yet).
- No drag-and-drop Kanban board — pipeline stage changes are a per-row select, matching this codebase's existing inline-form conventions (see Leave's team-approvals table) rather than introducing new JS complexity.
- No API endpoints — web only, since nothing in the original plan committed to an API surface for this module.

---

## 3. Performance Management

Greenfield — `DOC.md`'s only trace of this concept is a single field-sensitivity row (§5.4, line 591) naming "Performance Reviews" with an access list of Manager, HR Manager, Employee (own), System Admin. No feature design is attached to it anywhere else in the document.

### What it does

A real, working **two-way review**: self-rating + comments, then manager-rating + comments, per review cycle. Not a goals/OKR framework — `DOC.md` gives no basis for one, and building one would be inventing scope rather than closing a gap.

- **Review cycles**: HR creates a cycle (name + date range). Creating one automatically opens a `pending` review for every active employee, with the reviewer defaulting to whoever they currently report to (`Employment.reporting_to_employee_id`).
- **Self-review**: the employee rates themselves 1–5 with optional comments, from a new **Performance tab** on their Profile page. This flips the review to `self_submitted`.
- **Manager review**: the employee's manager (checked via `TeamScope::canActOn` — the same direct-report/department scoping used for Leave and Overtime approvals) rates and comments, completing the review. Reviews awaiting the manager's half also surface in the shared **Inbox** (`/inbox`), alongside Leave and Overtime.
- Completing a review notifies the employee via the existing in-app notification system.

### Permissions

Deliberately narrower than my own original plan text, corrected to match `DOC.md`'s explicit (if minimal) access list at line 591 rather than the broader grant I'd initially sketched:

| Permission | Grant |
|---|---|
| `performance.view` | HR Admin, HR Manager (tenant-wide); Department Manager, Team Lead (scoped to their own team via `TeamScope`) |
| `performance.manage-cycles` | Create/close review cycles. HR Admin, HR Manager only. |
| `performance.review` | Submit the manager half of a review. HR Admin, HR Manager, Department Manager, Team Lead — each still gated per-record by `TeamScope::canActOn`. |

Auditor, Accountant, HR Specialist, and Executive get **no** performance permissions — `DOC.md`'s access list for this data doesn't include them, and personal review content is more sensitive than the general read-only visibility those roles get elsewhere.

### Deliberately not built

- No goals/OKR tracking, no 360-degree/peer feedback, no calibration workflows — just the self + manager two-step described above.
- No dedicated API endpoints.
- No "reject" concept for a manager review (unlike Leave/Overtime) — a review is submitted, not approved-or-denied — so the shared Inbox view was extended to support a single "Review" link action alongside its existing approve/reject dual-button shape, rather than forcing an awkward binary decision onto a rating-and-comments submission.

---

## Cross-cutting notes

- All six new models (`PayrollRun`, `PayrollRunLine`, `JobRequisition`, `Candidate`, `PerformanceReviewCycle`, `PerformanceReview`) use the same `Auditable, BelongsToTenant, Userstamped` trait bundle as every other domain model — every create/update/delete is written to the existing `audit_logs` table automatically, with zero extra code.
- Money follows the codebase's existing convention exactly: `decimal(14,2)` + a separate `currency` `string(3)` column defaulting to `'UGX'` — no new Money value object introduced.
- Demo data for all three modules is seeded in `database/seeders/DemoTenantSeeder.php`: one draft payroll run for the demo entity, one open "Backend Engineer" requisition with three candidates at different pipeline stages, and one active review cycle with one review awaiting a manager's input and one fully completed.
