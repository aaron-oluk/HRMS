---

# SYSTEM FEATURES & LAYOUT WITH RBAC
## Complete System Specification

---

# TABLE OF CONTENTS

1. Architecture & Technical Layout
2. Mobile App Navigation Flow
3. Module Map & Feature Set
   - Layer 1: System of Record
   - Layer 2: Workforce Operations
   - Layer 3: Self-Service Portal
   - Layer 4: HMBC Audit Mode
   - Layer 5: Reports & Analytics
4. Cross-Cutting Features
5. Role-Based Access Control (RBAC)
   - RBAC Architecture
   - System Roles Definition
   - Role Permissions Matrix
   - Permission Dependencies
   - Default Role Assignment Workflow
   - Audit Trail for Access
   - Default Role Permission Summaries
6. Adoption Instrumentation
7. Non-Functional Requirements
8. Build Phases
9. User Journeys
10. Definition of Done
11. Open Decisions
12. Database Entity Relationships
13. Statutory Engine Rules

---

# 1. ARCHITECTURE & TECHNICAL LAYOUT

## 1.1 Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel with PostgreSQL 15+ strict mode |
| **Frontend** | Blade templates with minimal JavaScript |
| **Mobile** | Flutter app (Android-first) |
| **API** | REST, versioned `/api/v1`, API-first discipline |
| **Queues** | Redis-backed for payroll runs, reports, notifications |
| **Multi-tenancy** | Single database with `tenant_id` scoping |
| **Audit** | Append-only `audit_logs` with actor, tenant, entity, field changes |
| **Offline** | Local queue with idempotency keys (client-generated UUIDs) |

## 1.2 Core Architectural Principles

- **API First** – UI consumes public API; same API for integrations
- **Effective Dating** – Version all changes; never overwrite history
- **Immutable Facts** – Clock events, payroll inputs are immutable snapshots
- **Stateless Computation** – Payroll engine = pure deterministic functions
- **Audit Everything** – Every mutation logged with before/after values
- **No Lock-in** – Full data export/import for every client

---

# 2. MOBILE APP NAVIGATION FLOW

```
Splash Screen
    ↓
Login/Sign In
    ↓
New User? ────Yes──→ Sign Up/Registration
    ↓                    ↓
    No             Email/Phone Verification
    ↓                    ↓
Set Password      Set Password
    ↓                    ↓
Two-Factor Auth ←────────┘
    ↓
Home Page (Overview)
```

## 2.1 App Home Screen Components

- **Overview Dashboard** – Real-time metrics (attendance, leave, tasks)
- **Work Notes** – Daily check-in/checkout with notes
- **Quick Actions** – Clock in, request leave, submit report
- **Notifications** – Alerts, approvals, reminders

---

# 3. MODULE MAP & FEATURE SET

## LAYER 1: SYSTEM OF RECORD (Foundation)

### 3.1 Organization Structure

| Feature | Description |
|---------|-------------|
| Entities | Multi-company, multi-branch support |
| Departments | Hierarchical department tree |
| Positions | Job titles with reporting lines |
| Grades | Salary bands and career progression |
| Reporting Lines | Manager-subordinate approval chains |

### 3.2 Employee Management

| Feature | Description |
|---------|-------------|
| Add/Edit Employee | Create and update employee profiles |
| View Profile | Complete employee record with documents |
| Contracts | Effective-dated employment terms |
| Documents | CV, ID, certifications, work permits |
| Bank Accounts | Payment method configuration |
| Mobile Money | MTN MoMo, Airtel Money wallet setup |
| Sensitive Fields | Salary, NIN, bank details restricted by RBAC |

### 3.3 Data Protection & Compliance

| Feature | Description |
|---------|-------------|
| Consent Capture | Data Protection and Privacy Act 2019 compliance |
| Subject Access | Employee data export workflow |
| Deletion Workflows | Right to be forgotten with audit trails |
| Breach Notification | Security incident reporting support |

---

## LAYER 2: WORKFORCE OPERATIONS

### 3.4 Attendance & Time Tracking

| Feature | Description |
|---------|-------------|
| Clock Events | Immutable raw punch data with GPS location |
| Shifts & Rosters | Schedule management with shift patterns |
| Attendance Days | Derived computations from clock events |
| Overtime Requests | Approval workflow with payroll feed |
| Device Sync API | Biometric/terminal integration |
| Offline Support | Mobile clock-in with local queue |
| View Attendance Logs | Historical record with filtering |
| Approve/Reject Logs | Manager approval of attendance entries |

### 3.5 Leave Management

| Feature | Description |
|---------|-------------|
| Leave Types | Annual, sick, compassionate, study |
| Leave Policies | Employment Act 2006 defaults preloaded |
| Accrual Engine | Rules-based balance computation |
| Holiday Calendar | Public holidays per country |
| View Leave Requests | Employee and manager view |
| Approve/Reject Requests | Workflow-based approval |
| Leave Balances | Real-time visibility with transaction history |

### 3.6 Task Management

| Feature | Description |
|---------|-------------|
| Create/Assign Task | Task creation with assignment |
| Track Progress | Status monitoring and updates |
| Task Categories | Project, department, priority labels |
| Deadline Tracking | Due date monitoring with alerts |
| Task History | Complete audit trail |

### 3.7 Scheduling & Shift Management

| Feature | Description |
|---------|-------------|
| Create Schedule | Weekly and monthly scheduling |
| Assign Shift/Rota | Staff allocation to shifts |
| Shift Patterns | Configurable rotation templates |
| Notify Employees | Push notifications for schedule changes |
| Conflict Detection | Prevent double-booking and overtime violations |

### 3.8 Payroll & Compensation

| Feature | Description |
|---------|-------------|
| Upload Payroll | Process payroll run |
| View Payslips | Employee portal access |
| Download Payslips | PDF export |
| Pay Components | Earnings, deductions, taxable flags |
| Payroll Runs | Draft → Review → Approved → Locked → Disbursed |
| Loans & Advances | Loan schedules and recovery tracking |

### 3.9 Uganda Statutory Engine (Country Pack v1)

| Component | Rate/Rule |
|-----------|-----------|
| PAYE Free Threshold | UGX 235,000/month |
| PAYE Top Rate | 40% for high monthly incomes |
| NSSF Employee | 5% deduction |
| NSSF Employer | 10% contribution |
| NSSF Registration | Within 30 days of engagement |
| LST | Configurable band table per local authority |
| Remittance Due | 15th of following month |
| Taxable Benefits | Housing, transport, meals per URA rules |

### 3.10 Disbursement

| Feature | Description |
|---------|-------------|
| Bank Transfers | CSV/Excel per bank format |
| Mobile Money | MTN MoMo and Airtel Money direct payout |
| Partial Failure | Retry per employee, reconciliation screen |
| Confirmation | Store references against payroll items |

---

## LAYER 3: SELF-SERVICE PORTAL (ESS & MSS)

### 3.11 Mobile App Features

| Feature | Description |
|---------|-------------|
| Work Notes | Daily check-in/out with notes |
| Home Page | Overview dashboard |
| Reports | Access to generated reports |
| Submit Ticket | Issue reporting workflow |
| Track Tickets | Support ticket status |
| Notifications | Alerts and approvals inbox |
| Edit Profile | Personal information updates |
| Account Settings | Preferences and configurations |
| Security Settings | 2FA, password, biometrics |
| Logout | Session termination |

### 3.12 Manager Self-Service (MSS)

| Feature | Description |
|---------|-------------|
| Approve/Reject Requests | Leave, overtime, expense claims |
| View Attendance Logs | Team attendance monitoring |
| Monthly Summary | Team performance overview |
| Assign Tasks | Work allocation and tracking |
| Create Schedule | Shift planning for team |
| Notify Employees | Team communications |

---

## LAYER 4: HMBC AUDIT MODE (Compliance & Governance)

### 3.13 Auditor Dashboard

| Feature | Description |
|---------|-------------|
| One-click Auditor View | Instant compliance perspective |
| View Action History Log | Complete system audit trail |
| Filter/Search | Advanced audit log queries |
| Upload Documents | Supporting evidence submission |
| Expiry Reminders | License, certification, compliance deadlines |
| Digital Sign/E-Sign | Electronic document approval |

### 3.14 Communication & Support

| Feature | Description |
|---------|-------------|
| Notifications | System, approval, reminder alerts |
| Chat | Real-time internal messaging |
| Submit Ticket | Support request creation |
| Track Tickets | Issue resolution monitoring |

---

## LAYER 5: REPORTS & ANALYTICS

### 3.15 Standard Reports

| Report | Description |
|--------|-------------|
| Attendance Report | Daily/weekly/monthly attendance patterns |
| Leave Report | Leave utilization, balances, trends |
| Payroll Report | Cost analysis, statutory deductions |
| Compliance Report | Regulatory adherence status |
| Monthly Data Overview | Executive summary of all metrics |

### 3.16 Advanced Reporting (Phase 2)

| Feature | Description |
|---------|-------------|
| Report Builder | Self-service over semantic layer |
| Excel Export | Every list view with honest data |
| Profit & Loss | Financial performance |
| Cash Flow | Money in/out tracking |
| Year-End Reports | Annual summarization |

---

# 4. CROSS-CUTTING FEATURES

## 4.1 Auth & Security

| Feature | Description |
|---------|-------------|
| Role-Based Access Control | Field-level sensitivity |
| Two-Factor Authentication | App-based or SMS |
| 2FA Settings | Enable/disable, recovery codes |
| Biometric Login | Fingerprint/FaceID (mobile) |
| Session Management | Active sessions view |
| Password Policies | Complexity, rotation, history |

## 4.2 Workflow Engine

| Feature | Description |
|---------|-------------|
| Approval Chains | Configurable multi-level |
| Escalation | After configurable SLA |
| State Machines | Status enums with transition guards |
| Webhooks | employee.created, leave.approved, payroll.approved |

## 4.3 Document Management

| Feature | Description |
|---------|-------------|
| Template Library | Contracts, offer letters, termination letters |
| E-Signature | Digital signing (Phase 2) |
| Document Upload | Employee and compliance documents |
| Bulk Generation | Batch document creation |

## 4.4 Integration Framework

| Feature | Description |
|---------|-------------|
| Accounting Export | QuickBooks, Xero, Aloflux POS |
| API Integrations | REST with versioning |
| Bank File Formats | Configurable per bank |
| Mobile Money APIs | MTN, Airtel |
| Health Monitoring | Retries and failure notifications |

---

# 5. ROLE-BASED ACCESS CONTROL (RBAC)

## 5.1 RBAC Architecture

### Access Control Model
- **Role-Based Access Control (RBAC)** with field-level sensitivity
- **Tenant-scoped** permissions (global across entities within tenant)
- **Hierarchical inheritance** – Permissions cascade from parent roles
- **Field-level sensitivity** – Restrict access to specific fields (salary, NIN, bank details)
- **Action-based permissions** – Create, Read, Update, Delete, Approve, Export per module
- **Contextual permissions** – Access based on organizational hierarchy (manager → direct reports only)

## 5.2 System Roles Definition

### Role Hierarchy
```
System Admin (Super Admin)
    └── Tenant Admin (HR Director)
        ├── HR Manager
        │   ├── HR Specialist
        │   └── Payroll Specialist
        ├── Department Manager
        │   └── Team Lead
        ├── Auditor
        ├── Employee (Self-Service)
        └── Accountant
```

## 5.3 Role Permissions Matrix

### Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | Full Access (Create, Read, Update, Delete, Approve) |
| 📖 | Read Only |
| ✏️ | Create & Update Only |
| 👁️ | View Only (No Export) |
| 🚫 | No Access |
| ⚠️ | Restricted (Manager only for direct reports) |
| 🔒 | Field-level restrictions apply |

### 5.3.1 Organization Module

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Entities | ✅ | ✅ | 📖 | 📖 | 🚫 | 🚫 | 📖 | 🚫 | 📖 |
| Branches | ✅ | ✅ | 📖 | 📖 | 🚫 | 🚫 | 📖 | 🚫 | 📖 |
| Departments | ✅ | ✅ | ✅ | ✏️ | 📖 | 🚫 | 📖 | 🚫 | 📖 |
| Positions | ✅ | ✅ | ✅ | ✏️ | 📖 | 🚫 | 📖 | 🚫 | 📖 |
| Grades | ✅ | ✅ | ✅ | ✏️ | 📖 | 🚫 | 📖 | 🚫 | 📖 |
| Reporting Lines | ✅ | ✅ | ✅ | ✏️ | 📖 | 🚫 | 📖 | 🚫 | 📖 |

### 5.3.2 Employee Management

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Add Employee | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| Edit Employee | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | ✏️² | 🚫 |
| View Profile | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | ✅ | ✅ | 📖³ |
| Delete Employee | ✅ | ✅ | 🔒⁴ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| View Documents | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | ✅ | ✅ | 📖 |
| Upload Documents | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | ✅ | ✅ | 🚫 |
| Contracts | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | 📖 | 📖 |
| Bank Accounts | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🔒⁵ | ✏️⁶ | 📖⁷ |
| Mobile Money | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🔒⁵ | ✏️⁶ | 📖⁷ |
| Sensitive Fields⁸ | ✅ | ✅ | 🔒⁹ | 🔒⁹ | 🔒¹⁰ | 🚫 | 🔒¹¹ | 🔒¹² | 🔒¹³ |

**Notes:**
1. Manager only for direct reports
2. Employee can edit own profile (non-sensitive fields only)
3. Accountant can view employee financial data only
4. Soft delete with approval workflow required
5. Auditor can view but not modify financial data
6. Employee can update own payment method
7. Accountant can view for payroll processing
8. Sensitive fields: Salary, NIN, Bank Details, Medical History, Disciplinary Records
9. HR Manager/Specialist – Restricted by need-to-know
10. Department Manager – Only for direct reports, salary hidden
11. Auditor – Can view for compliance but cannot export
12. Employee – Cannot view own salary? (Configured per policy)
13. Accountant – Can view for payroll processing only

### 5.3.3 Attendance & Time Tracking

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| View Attendance Logs | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| Approve/Reject Logs | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 🚫 | 🚫 | 🚫 |
| Clock In/Out | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅ | 🚫 |
| Create Shifts | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |
| Assign Rosters | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |
| Overtime Requests | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✏️² | 🚫 |
| Approve Overtime | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |
| Export Attendance | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 📖 | 🚫 | 🚫 |
| Device Sync | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |

**Notes:**
1. Manager only for direct reports
2. Employee can view own logs and request overtime

### 5.3.4 Leave Management

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Leave Types | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | 🚫 |
| Leave Policies | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | 🚫 |
| Leave Balances | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| View Leave Requests | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| Submit Leave Request | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅ | 🚫 |
| Approve/Reject Leave | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️³ | 🚫 | 🚫 | 🚫 |
| Holiday Calendar | ✅ | ✅ | ✅ | ✅ | 📖 | 📖 | 📖 | 📖 | 📖 |
| Leave Report | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 📖 | 📖 | ✅² | 🚫 |

**Notes:**
1. Manager only for direct reports
2. Employee can view own leave
3. Team Lead can approve only if delegated authority

### 5.3.5 Task Management

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Create Task | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✏️⁴ | 🚫 |
| Assign Task | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| View Tasks | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅² | 🚫 |
| Track Progress | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅² | 🚫 |
| Update Task | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✏️² | 🚫 |
| Task Categories | ✅ | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 🚫 | 🚫 |
| Task History | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅² | 🚫 |

**Notes:**
1. Manager only for team tasks
2. Employee can view/update own assigned tasks
3. Employee can create task requests
4. Employee can request task creation

### 5.3.6 Scheduling & Shift Management

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Create Schedule | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |
| Assign Shifts | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |
| View Schedule | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| Shift Patterns | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | 🚫 | 🚫 |
| Notify Employees | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 🚫 | 🚫 | 🚫 |
| Conflict Detection | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 🚫 | 🚫 | 🚫 |

**Notes:**
1. Manager only for direct reports
2. Employee can view own schedule only

### 5.3.7 Payroll & Compensation

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Pay Components | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | 📖 |
| Pay Structures | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | 📖 |
| Upload Payroll | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | ✏️¹ |
| View Payslips | ✅ | ✅ | ✅ | ✅ | ⚠️² | ⚠️² | 📖 | ✅³ | 📖 |
| Download Payslips | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | ✅³ | 📖 |
| Payroll Reports | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Payroll Run | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | ✏️⁴ |
| Loans & Advances | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | ✅³ | 📖 |
| Statutory Returns | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | ✏️⁵ |
| Disbursement | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Bank Files | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | ✅ |
| Mobile Money | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | ✅ |

**Notes:**
1. Accountant can prepare payroll but requires approval
2. Manager can view payroll for team only (aggregated, no salary details)
3. Employee can view/download own payslip only
4. Accountant can run payroll but requires HR Manager approval
5. Accountant can prepare statutory returns

### 5.3.8 HMBC Audit Mode

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Auditor View | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | ✅ | 🚫 | 🚫 |
| Action History Log | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | ✅ | 🚫 | ✅ |
| Filter/Search Logs | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | ✅ | 🚫 | ✅ |
| Upload Documents | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | ✅ | 🚫 | 🚫 |
| Expiry Reminders | ✅ | ✅ | ✅ | ✅ | 📖 | 📖 | ✅ | 📖 | 📖 |
| Digital Sign/E-Sign | ✅ | ✅ | ✅ | ✅ | 📖 | 🚫 | ✅ | 🚫 | 🚫 |
| Compliance Report | ✅ | ✅ | ✅ | ✅ | 📖 | 🚫 | ✅ | 🚫 | 📖 |

### 5.3.9 Communication & Support

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| View Notifications | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Send Notifications | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| Chat | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Submit Ticket | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Track Tickets | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Assign Tickets | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| Resolve Tickets | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |

### 5.3.10 Reports & Analytics

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Attendance Report | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| Leave Report | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | ⚠️¹ | 📖 | ✅² | 🚫 |
| Payroll Report | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Compliance Report | ✅ | ✅ | ✅ | ✅ | 📖 | 🚫 | ✅ | 🚫 | 📖 |
| Monthly Data Overview | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 📖 | 📖 | ✅² | 📖 |
| Profit & Loss | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Cash Flow | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Export Excel | ✅ | ✅ | ✅ | ✅ | ⚠️¹ | 🚫 | 📖 | 🚫 | ✅ |
| Report Builder | ✅ | ✅ | ✅ | ✏️ | ⚠️¹ | 🚫 | 📖 | 🚫 | ✏️³ |

**Notes:**
1. Manager only for direct reports
2. Employee can view own reports only
3. Accountant can build financial reports only

### 5.3.11 Settings & Configuration

| Feature | System Admin | Tenant Admin | HR Manager | HR Specialist | Dept Manager | Team Lead | Auditor | Employee | Accountant |
|---------|--------------|--------------|------------|---------------|--------------|-----------|---------|----------|------------|
| Business Info | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 📖 | 🚫 | 📖 |
| Notifications Settings | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅¹ | ✅ |
| Bank Settings | ✅ | ✅ | ✅ | ✏️ | 🚫 | 🚫 | 📖 | 🚫 | ✅ |
| Project Tags | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | 🚫 | ✅² | 🚫 |
| AI Settings | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| Permissions | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |
| API & Integrations | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 | ✅ |
| Edit Profile | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅³ | ✅ |
| Account Settings | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅³ | ✅ |
| Security Settings | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅³ | ✅ |
| 2FA Configuration | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅³ | ✅ |
| Data Export | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | ✅ | ✅⁴ | ✅ |
| Data Import | ✅ | ✅ | ✅ | ✅ | 🚫 | 🚫 | 🚫 | 🚫 | 🚫 |

**Notes:**
1. Employee can configure personal notification preferences
2. Employee can use tags for tasks
3. Employee can edit own profile and security settings only
4. Employee can export own data (subject access request)

## 5.4 Permission Dependencies

### Manager Hierarchical Access
- Department Manager can access employees in their department only
- Team Lead can access direct reports only
- HR Manager can access all employees (tenant-wide)
- Access propagates down the reporting chain

### Field-Level Sensitivity Restrictions

| Field | Access Restriction |
|-------|-------------------|
| **Salary** | HR Manager, Payroll Specialist, Accountant (view-only), System Admin, Tenant Admin |
| **NIN (National ID)** | HR Manager, HR Specialist, System Admin, Tenant Admin |
| **Bank Account Details** | HR Manager, Payroll Specialist, Accountant, System Admin, Tenant Admin |
| **Medical History** | HR Manager, System Admin, Tenant Admin |
| **Disciplinary Records** | HR Manager, System Admin, Tenant Admin |
| **Performance Reviews** | Manager, HR Manager, Employee (own), System Admin |
| **Contracts** | HR Manager, Employee (own), Manager (view-only), System Admin |
| **Audit Logs** | System Admin, Tenant Admin, Auditor, HR Manager |

## 5.5 Default Role Assignment Workflow

### New User Provisioning
```
User Created
    ↓
Assign Primary Role
    ↓
┌───────────────┼───────────────┐
│               │               │
HR Admin    Department      Employee
Role        Manager Role    Role
    ↓           ↓               ↓
Assign        Assign          Assign
Sub-Roles     Sub-Roles       Basic
(Optional)    (Optional)      Permissions
```

### Approval Workflow for Sensitive Actions

| Action | Requires Approval From |
|--------|----------------------|
| Delete Employee | HR Manager → Tenant Admin |
| Payroll Run | Payroll Specialist → HR Manager → Tenant Admin |
| Disbursement | Accountant → HR Manager |
| Mass Data Import | HR Manager → Tenant Admin |
| Permission Changes | HR Manager → Tenant Admin |
| System Configuration | Tenant Admin → System Admin |

## 5.6 Audit Trail for Access

### Access Logging Requirements

| Event | Logged Data |
|-------|-------------|
| Login Attempt | User, IP, Timestamp, Success/Failure |
| Failed Access | User, Resource, Action, Timestamp |
| Permission Change | Actor, User, Old Permissions, New Permissions |
| Sensitive Field View | User, Employee, Field, Timestamp |
| Data Export | User, Filters, Records Count, Timestamp |
| Role Assignment | Actor, User, Role, Timestamp |

## 5.7 Permission Implementation Notes

### Database Level
- Use PostgreSQL Row Level Security (RLS) where possible
- Implement tenant_id scoping on all queries
- Add policy tables: `roles`, `permissions`, `role_permissions`, `user_roles`

### API Level
- Middleware checks permissions before controller execution
- Field-level filtering on serialization
- Policy classes per model (Laravel)

### UI Level
- Conditional rendering based on permissions
- Gray-out disabled actions
- Error messages for unauthorized access attempts
- View-level restrictions enforced at backend

## 5.8 Default Role Permission Summaries

### System Admin
- Full system access across all tenants (Super Admin)
- Can manage users, roles, permissions globally
- Can view all data across all tenants (audit only)

### Tenant Admin
- Full access within assigned tenant
- Can manage all HR operations
- Can configure system settings (tenant-level)

### HR Manager
- Complete HR operations access (except system configuration)
- Can manage employees, attendance, leave, payroll (with approvals)
- Can view all HR reports

### HR Specialist
- Operational HR access
- Can create/update employee records
- Can process leave and attendance
- Restricted from payroll disbursement

### Department Manager
- Access only to direct reports
- Can approve leave, attendance, requests
- Can view team reports (aggregated payroll data)

### Team Lead
- Access only to immediate team members
- Limited approval authority (configurable)
- Can submit requests on behalf of team

### Auditor
- Read-only access for compliance
- Can view all data (including sensitive)
- Cannot modify or export sensitive data
- Access to HMBC Audit Mode

### Employee
- Self-service access only
- Can view own profile, attendance, leave, payslips
- Can submit requests and tasks
- Cannot view other employees' data

### Accountant
- Financial operations access
- Can process payroll, generate payslips
- Can manage disbursements
- Can view financial reports only

---

# 6. ADOPTION INSTRUMENTATION

| Metric | Purpose | Alert Trigger |
|--------|---------|---------------|
| Self-service completion | Requests started vs. completed | < 70% |
| Approval latency | Time from request to decision | > 48 hrs |
| Payslip open rate | Employee engagement | < 50% |
| Mobile usage | Digital adoption vs. manual | < 80% |
| Audit compliance | Policy acknowledgment rate | < 90% |

---

# 7. NON-FUNCTIONAL REQUIREMENTS

## 7.1 Performance
- Self-service pages: < 200 KB initial payload
- Payroll run (1,000 employees): < 2 minutes
- API response: < 200ms (95th percentile)

## 7.2 Offline Capability
- Mobile clients queue clock-in and requests locally
- Client-generated UUIDs with idempotency keys
- Server dedupes on idempotency key

## 7.3 Security
- RBAC with field-level sensitivity
- Encryption at rest and in transit
- Encrypted backups with tested restore runbook

## 7.4 Auditability
- Every field change logged in audit_logs
- Audit log exportable (subject access requests)
- Payroll re-derivable from stored inputs

## 7.5 Localization
- Currency, date formats externalized (UGX default)
- Language strings externalized (English v1)
- Country configuration packs (Uganda v1, Kenya/Tanzania/Rwanda later)

---

# 8. BUILD PHASES

## Phase 1 – MVP (Core HR + Payroll + Uganda Compliance + Mobile)

| Module | Key Deliverables |
|--------|------------------|
| Organization | Entities, branches, departments, positions, grades |
| Employees | Profiles, contracts, documents, effective dating |
| Leave | Policies, accruals, requests, approvals, calendar |
| Attendance | Clock events, shifts, rosters, overtime |
| Payroll | Components, runs, statutory engine, payslips |
| Disbursement | Bank files, mobile money |
| Self-Service | ESS +