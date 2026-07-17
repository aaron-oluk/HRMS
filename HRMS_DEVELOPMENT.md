# Aloflux HRMS. Development Reference

Companion to the feature requirements and gap analysis document. This file is the working reference for engineering: scope, architecture, data model, statutory engine rules, and build phases. Written to serve as the seed for a CLAUDE.md when the repository is created.

## 1. Product Thesis

Build an HRMS that wins on the gaps most systems get wrong, not on feature count:

* Genuinely usable self service on cheap Android phones over weak connections
* Uganda statutory payroll that is correct out of the box, with country rules as versioned data
* Native mobile money disbursement (MTN MoMo, Airtel Money) alongside bank files
* Offboarding and final settlement treated as first class workflows
* Self service reporting with honest Excel export, not canned reports only
* Configuration by HR admins, never vendor tickets, for policies, fields, approvals and templates
* Full data export and import for every client, no lock in
* Transparent tiered pricing with API access in every tier

Target segment: organizations of 10 to 1,000 employees in Uganda and East Africa, generalizing to other markets through configuration packs.

## 2. Stack and Architecture

* Backend: Laravel (aligns with Nexus POS and NoteVault; heavier domain logic, queues, and policy engine favor Laravel over CodeIgniter 4 here). Final call at repo creation.
* Database: MySQL 8 with strict mode. Every table carries created_at, updated_at, created_by, updated_by.
* Frontend: Blade plus Livewire or Inertia with Vue for the admin web app. Keep payloads small.
* Mobile: Android first. Options in order of preference: Flutter app consuming the public API, or a PWA if timeline is tight. Offline queue for clock in, leave requests and form submissions with idempotency keys on sync.
* API: REST, versioned under /api/v1. The product's own UI consumes the same public API (API first discipline). Webhooks for employee.created, leave.approved, payroll.approved, employee.exited.
* Queues: Redis backed queues for payroll runs, report generation, notifications, and integration sync.
* Multi tenancy: single database with tenant_id scoping enforced by a global scope and tested at the query layer. Entity level statutory settings within a tenant (multi company, multi branch).
* Audit: append only audit_logs table capturing actor, tenant, entity, field, old value, new value, timestamp, request id. No deletes; corrections are new rows.

## 3. Module Map and Ownership

Layer 1, system of record:

* employees: profiles, contracts, documents, effective dated changes
* org: entities, branches, departments, positions, grades, reporting lines

Layer 2, workforce operations:

* attendance: clock events, shifts, rosters, overtime, device sync API
* leave: policies, accrual engine, requests, approvals, calendars
* payroll: components, runs, statutory engine, payslips, disbursement, loans and advances
* selfservice: employee and manager portals, request center, notifications inbox

Layer 3, talent lifecycle:

* recruitment: requisitions, careers page, pipeline, offers, convert to employee
* onboarding and offboarding: checklist engine, probation, settlement calculator, access revocation tracking

Layer 4, strategic:

* performance, learning, compensation, engagement, analytics

Shared kernel:

* auth and RBAC with field level sensitivity, workflow engine (approval chains), document templates and letter generation, report builder, import and export framework, integration hub with health monitoring and retries

## 4. Core Data Model (first cut)

Key entities and relationships. Names indicative, refine at design time.

* tenants, entities, branches, departments, positions, grades
* employees (person data), employments (effective dated: position, grade, salary, entity, status), employee_documents, employee_bank_accounts, employee_mobile_money
* leave_types, leave_policies, leave_balances, leave_requests, holiday_calendars
* shifts, rosters, clock_events (raw, immutable), attendance_days (derived), overtime_requests
* pay_components (earning or deduction, taxable flags, formulas), employee_pay_structures, payroll_runs, payroll_items (every line traceable to component, rule version, and inputs), payslips, loans, loan_schedules
* statutory_rule_packs (country, version, effective_from, json rules), statutory_returns, remittance_calendar
* candidates, applications, pipeline_stages, offers
* checklists, checklist_tasks (polymorphic: onboarding, offboarding, custom)
* reviews, goals, feedback (phase 3)
* audit_logs, webhooks, integration_connections, sync_jobs

Design rules:

* Effective dating on employments and pay structures. Never overwrite salary history.
* clock_events are immutable facts; attendance_days are recomputable derivations.
* payroll_items store the rule pack version and input snapshot so any payslip can be re derived and explained.
* Soft state machines with explicit status enums and transition guards (payroll run: draft, review, approved, locked, disbursed).

## 5. Uganda Statutory Engine (country pack v1)

All rates and bands live in statutory_rule_packs as effective dated data. Verify current figures against URA and NSSF publications before release; the figures below are the working baseline.

PAYE:

* Progressive monthly bands with a tax free threshold of UGX 235,000 per month and a top marginal rate of 40 percent for high monthly incomes
* NSSF contributions are NOT deductible from taxable income in Uganda; compute PAYE on gross taxable pay
* Include taxable benefits (housing, transport, meals) per URA valuation rules
* Monthly PAYE return and payment to URA due by the 15th of the following month; annual PAYE reconciliation return after the tax year (July to June)

NSSF:

* 5 percent employee deduction plus 10 percent employer contribution on gross monthly wages
* Register new employees with NSSF within 30 days of engagement; surface this as an onboarding checklist task with a deadline
* Remittance due by the 15th of the following month; late remittance attracts penalties, so the statutory calendar must alert before the deadline

Local Service Tax:

* Annual tax by income band, collected by local governments, typically deducted in installments early in the financial year; configurable band table and installment schedule per local authority

Other:

* Payslips mandatory; payroll records retained per the Employment Act 2006
* Employment Act leave entitlements preloaded as default leave policies
* Data Protection and Privacy Act 2019: consent capture at onboarding, purpose limitation, subject access and deletion workflows, breach notification support

Engine requirements:

* Pure, deterministic calculation functions: inputs in, payslip lines out, no side effects
* Golden test suite: a spreadsheet of worked examples (low earner under threshold, mid earner, high earner at 40 percent band, employee with LST, employee with loan recovery, joiner mid month, leaver with settlement) that must pass on every commit
* Every computed line records the rule pack version; changing a rate means a new pack version with a new effective date

## 6. Disbursement

* Bank: generate per bank CSV or Excel transfer files in the formats Ugandan banks accept; templates configurable per bank
* Mobile money: direct payout via MTN MoMo and Airtel Money disbursement APIs for employees paid to wallets; reuse Aloflux payment integration work; store confirmation references against payroll_items
* Disbursement only from a locked payroll run; partial failure handling with retry per employee and a reconciliation screen

## 7. Non Functional Requirements

* Performance: self service pages under 200 KB initial payload; payroll run for 1,000 employees completes in under 2 minutes as a queued job
* Offline: mobile queues clock in and requests locally with client generated UUIDs; server dedupes on idempotency key
* Security: RBAC with field level sensitivity (salary fields restricted), 2FA, encryption at rest and in transit, encrypted backups with a tested restore runbook
* Auditability: every field change logged; audit log exportable; payroll figures re derivable from stored inputs
* Localization: currency, date formats and language strings externalized; UGX default
* Availability: graceful read only mode if the primary database is degraded; status page for clients

## 8. Adoption Instrumentation

Track per tenant from day one and expose to the client:

* Self service completion rate (requests started vs completed)
* Approval latency per approver with escalation after a configurable SLA
* Payslip open rate
* Percentage of leave requests submitted via self service vs entered by HR

Low adoption is churn risk; these metrics drive onboarding success playbooks.

## 9. Build Phases

Phase 1 (MVP):

* Core records and org structure with effective dating and audit
* Leave (policies, accruals, approvals, calendar)
* Attendance (web and mobile clock in with GPS, shifts, overtime approval, payroll feed)
* Uganda payroll engine, payslips, statutory reports, remittance calendar
* Bank files plus mobile money disbursement
* ESS and MSS web plus Android app
* Standard reports and dashboards, Excel export everywhere
* RBAC, 2FA, audit logs, data export

Phase 2:

* Recruitment and ATS with convert to employee
* Onboarding and offboarding checklist engine, probation tracking, final settlement calculator, access revocation tracking
* Self service report builder over a semantic layer
* Accounting journal export (QuickBooks, Xero) and native posting into Aloflux POS accounting
* Letter templates and e signature
* Import framework with mapping templates for spreadsheet migration

Phase 3:

* Performance (goals, cycles, 360), learning and certification tracking, compensation review cycles
* Engagement: announcements with read tracking, pulse surveys, recognition, policy acknowledgment
* Kenya country pack, then Tanzania and Rwanda
* SSO, predictive analytics (attrition flags, overtime anomalies), USSD or SMS fallback channel

## 10. Definition of Done per Module

* Public API endpoints documented and consumed by the UI
* RBAC rules and field sensitivity applied and tested
* Audit logging verified for all mutations
* Mobile flow works on a low end Android device over a throttled connection
* Excel export available for every list view
* Golden tests pass (payroll modules) or workflow tests pass (approval modules)
* Seed data and demo tenant updated

## 11. Open Decisions

* Laravel confirmed vs CodeIgniter 4 (recommendation: Laravel)
* Flutter app vs PWA for v1 mobile
* Single database multi tenancy vs schema per tenant at enterprise tier
* Build vs integrate for e signature
* Hosting region and data residency posture for Ugandan clients under the Data Protection and Privacy Act
