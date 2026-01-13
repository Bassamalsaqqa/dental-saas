# Refactoring Blueprint  
## Multi-Tenancy & RBAC Implementation (Plan B)

---

## Objective
Transition the application from IonAuth group-based authorization to a strict **Role-Based Access Control (RBAC)** system with **multi-tenancy enforcement**, including explicit **patient ↔ user linking**.

---

# 1. IonAuth Removal Strategy

## Goal
Eliminate group-based checks and replace them with **permission-based gates**.

---

## Filters

**Action**  
Remove `AdminFilter` (currently uses IonAuth groups).  
Replace with:
- RBAC permission middleware  
- A dedicated **control-plane filter** for super-admin routes

**Evidence**
app/Filters/AdminFilter.php:42
app/Config/Filters.php:38
app/Config/Routes.php:322




---

## Helpers

**Action**  
Remove:
- `is_admin`
- `user_groups`
- `in_group`
- All IonAuth fallback logic  

Keep only **RBAC helpers**.

**Evidence**
app/Helpers/auth_helper.php:24
app/Helpers/auth_helper.php:48
app/Helpers/auth_helper.php:63
app/Helpers/auth_helper.php:91




---

## BaseController

**Action**
- Remove group exposure
- Remove IonAuth fallback in `isAdmin()`
- Use RBAC services only
- Expose roles and permissions to views

**Evidence**
app/Controllers/BaseController.php:94
app/Controllers/BaseController.php:115
app/Controllers/BaseController.php:308




---

## Auth Controller

**Action**
- Remove group management UI
- Remove `isAdmin` checks
- Gate admin functions via RBAC (e.g. `users.manage`)

**Evidence**
app/Controllers/Auth.php:83
app/Controllers/Auth.php:679
app/Controllers/Auth.php:720




---

## Management Controllers

**Targets**
Users
UserManagement
Role
Sync
Debug

css


**Action**
Remove all IonAuth admin fallbacks.  
Use RBAC permissions exclusively.

**Evidence**
app/Controllers/Users.php:49
app/Controllers/UserManagementController.php:57
app/Controllers/RoleController.php:51
app/Controllers/SyncController.php:15
app/Controllers/DebugController.php:34




---

## Views

**Action**
Remove all group-based UI.  
Replace with role & permission assignment UIs.

**Evidence**
app/Views/auth/index.php:21
app/Views/auth/edit_user.php:38
app/Views/layouts/main_auth.php:267
app/Views/profile/index.php:117




---

## Final Filter Strategy

| Filter | Responsibility |
|-------|----------------|
| auth | Authenticated user |
| tenant | Active clinic + membership |
| permission | RBAC permission check |
| controlplane | super_admin + global_mode |

**Evidence**
app/Config/Filters.php:37




---

# 2. Tenant Enforcement Architecture

## Session Contract

Keep IonAuth session keys. Add:
active_clinic_id
global_mode
is_impersonating
impersonated_clinic_id




**Evidence**
app/Models/IonAuthModel.php:2004




---

## TenantFilter

Runs after AuthFilter on tenant routes.

Rejects if:
- `active_clinic_id` missing or invalid
- User not a member of that clinic

**Evidence**
app/Config/Routes.php:40
app/Config/Routes.php:406




---

## ControlPlaneFilter

Only allows:
- super_admin
- global_mode = true

Rejects any request containing `active_clinic_id`.

---

## Mode Separation Rules

| Route | Must Reject |
|------|-------------|
| Tenant | global_mode |
| Control Plane | active_clinic_id |

---

# 3. Schema Migration Blueprint

## Tenant-Owned Tables

Add `clinic_id` (NOT NULL, indexed):

patients
appointments
examinations
finances
inventory
inventory_usage
odontograms
prescriptions
treatments
activity_logs
settings
summary tables




**Evidence**
democa_dental.sql: 277, 59, 95, 125, 191, 216, 254, 393, 540, 30, 506, 81, 156, 310




---

## Per-Clinic Uniqueness

Composite keys:
(clinic_id, patient_id)
(clinic_id, appointment_id)
(clinic_id, examination_id)
(clinic_id, transaction_id)
(clinic_id, prescription_id)
(clinic_id, treatment_id)
(clinic_id, setting_key)
(clinic_id, email)




**Evidence**
democa_dental.sql: 690, 700, 710, 758, 786, 820, 813




---

## Global Uniqueness

users.email




**Evidence**
democa_dental.sql:830




---

## Patient ↔ User Linking (Plan B)

Add:
patients.user_id (UNIQUE, NULLABLE)




Enforces 1:1 patient ↔ login.

---

## Scoped RBAC Assignments

Add `clinic_id` to:
user_roles
user_permissions



Nullable only for `super_admin`.

---

# 4. Query Scoping Strategy

## TenantBaseModel

Apply to:
PatientModel
AppointmentModel
FinanceModel
InventoryModel
InventoryUsageModel
OdontogramModel
PrescriptionModel
TreatmentModel
SettingsModel
ActivityLogModel




---

## Raw SQL Leak Vectors

Must inject `clinic_id`:
Examination.php:354
Finance.php:482
Inventory.php:696
Prescription.php:157
Treatment.php:157
ActivityLog.php:75
Doctor.php:270




---

## API & Search

All search endpoints must enforce tenant scope.

app/Controllers/Api/Search.php:35




---

# 5. Summary & Aggregate Readiness

## Summary Tables
Convert to per-clinic.
democa_dental.sql: 81, 156, 310




Never global.

---

## Tenant Exports

Must require RBAC export permission and enforce `active_clinic_id`.

Finance.php:554
Reports.php:130
Odontogram.php:229
Routes.php:157, 268




---

## Audit Logs

Add `clinic_id` to `activity_logs`.

Create immutable:
audit_logs



Tracking:
- Clinic switching
- Impersonation
- Mass exports
- Permission denials
- Auth failures

---

# 6. Patient Onboarding & Login Rules (Plan B)

Patients are created inside a clinic.

If a patient needs access:
- A global user is created
- Linked via `patients.user_id`

Users authenticate globally but are bound to:
- Their patient clinic
- Or choose a clinic if staff in multiple