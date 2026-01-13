# Multi-Tenant SaaS Implementation Plan

## Objective
Deliver row-level tenancy with RBAC-only authorization, clinic-aware settings, and tenant-safe patient onboarding so that the single codebase can run securely for multiple clinics.

## Context
- IonAuth is authentication-only; all authorization now flows through `PermissionService`/`RoleModel`/`UserRoleModel`. Evidence: `app\Services\PermissionService.php` (role and permission checks) and `app\Config\Permissions.php`.
- Clinic context will flow from new session keys (`active_clinic_id`, `global_mode`, `is_impersonating`) set after login. Evidence: add to `app\Models\IonAuthModel.php::setSession`.
- Patients map 1:1 to users, with clinic-scoped email uniqueness and synthetic emails for receptionist-created records. Evidence: `app\Controllers\Patient.php:233-262` and schema `democa_dental.sql:277,758,830`.

## Key Tasks

1. **RBAC-only filters**
   - Remove `AdminFilter` and any `IonAuth` group helpers (`app/Helpers/auth_helper.php`, `app\Controllers\BaseController.php`).
   - Update routes to use new filter aliases: `auth`, `tenant` (enforces clinic context via new filter), `permission`, `controlplane` (super_admin global operations). Evidence: `app\Config\Routes.php:40-450`.
   - Audit controllers (Auth, Users, UserManagement, RoleController, SyncController, DebugController) to drop IonAuth group checks and rely on `PermissionService`. Evidence: `app\Controllers\Auth.php:83-360`, `app\Controllers\UserManagementController.php:34-112`.

2. **Clinic context enforcement**
   - After `IonAuth` login and impersonation endpoints, set `active_clinic_id`/`global_mode`. Update `BaseController` to inject tenant settings via `settings()->getClinicInfo()` referencing the active clinic.
   - Implement `TenantFilter` to validate session `active_clinic_id` and deny requests if user lacks membership (via `user_clinics`). Ensure APIs (e.g., `app\Controllers\Api\Search.php`, `app\Controllers\Notifications.php`) run under the tenant filter.

3. **Schema migrations**
   - Add `clinic_id` columns to `patients`, `appointments`, `examinations`, `finances`, `inventory`, `inventory_usage`, `odontograms`, `prescriptions`, `treatments`, `activity_logs`, `settings`, and summary tables. Two-step migrations (nullable → backfill → NOT NULL + index/composite unique). Evidence: `democa_dental.sql` tables.
   - Change patient email unique index to composite `(clinic_id, email)` and add `patients.user_id` linking to the `users` table. Synthetic emails (e.g., `PAT123@{clinic}.local`) are used when receptionists add patients without email; true emails required for self-registration. Evidence: `app\Controllers\Patient.php:235`, `app\Config\IonAuth.php:150`.
   - Add `clinic_id` to `user_roles` and `user_permissions`; enforce NOT NULL for non-super_admin assignments. Evidence: `democa_dental.sql:652`, `app\Services\PermissionService.php`.

4. **Query scoping**
   - Create `TenantBaseModel` (trait or base class) that automatically scopes queries with `clinic_id`. Apply to `PatientModel`, `AppointmentModel`, `FinanceModel`, `InventoryModel`, `InventoryUsageModel`, `OdontogramModel`, `PrescriptionModel`, `TreatmentModel`, `ActivityLogModel`, `SettingsModel`.
   - Replace controller raw queries or query builders (Finance datatables, Examinations, Inventory, Prescriptions, Treatments, Doctor listings) with tenant-scoped models or explicit `clinic_id` filters. Evidence: `app\Controllers\Finance.php:482`, `app\Controllers\Examination.php:354`, `app\Controllers\Inventory.php:696`, `app\Controllers\Prescription.php:157`, `app\Controllers\Doctor.php:268`.

5. **Patient onboarding**
   - Receptionist flow: allow empty email, auto-generate synthetic email and user record (inactive until real email/WhatsApp provided).
   - QR/self-registration: require email + WhatsApp (validated for E.164 format) to create `users` login and patient record. On successful registration, set `active_clinic_id` and allow bookings only after both fields are present. Evidence: `app\Controllers\Patient.php:232`, `app\Config\Routes.php:165`.

6. **Exports & audit**
   - Audit log writes include `clinic_id`; maintain dedicated audit log table for security events (`clinic_switch`, `impersonation`, `exports`, permission denials). Evidence: `democa_dental.sql:30`, `app\Services\PermissionService.php:318`.
   - All exports/backups (Settings backup, Finance export, Reports export) require explicit permission and tenant scope; global database backups remain super_admin control-plane only. Evidence: `app\Controllers\Settings.php:655`, `app\Controllers\Finance.php:554`, `app\Controllers\Reports.php:130`.

## Timeline recommendation
1. Phase 0: Hard-filter separation + audit logging.
2. Phase 1: Schema and session contract (clinic_id columns, patient-user link, synthetic email for receptionist flow).
3. Phase 2: Tenant-aware data layer + base model refactor.
4. Phase 3: UI/API updates (patient registration, DataTables, exports) and verification tests.

## Deliverables to track
- RBAC-only routes + filters updated.
- Schema migration scripts for clinic_id, patient_user link, composite uniques.
- TenantBaseModel and updated controllers for DataTables and search APIs.
- Patient registration flow enforcing email/WhatsApp for self-service and documentation for receptionist process.
- Audit log section describing recorded events (clinic switch, impersonation, exports, permission denials).
