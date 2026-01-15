### Task: P5-01 Tenant-safe file storage and download gating
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Implemented tenant-isolated storage in `writable/uploads/clinic_{id}` and a gated download controller. Tracked uploads via `file_attachments` table. Refactored logo upload to use this secure pattern.
- **Files Changed:**
    - `app/Database/Migrations/2026-01-14-174315_CreateFileAttachmentsTable.php`
    - `app/Models/FileAttachmentModel.php`
    - `app/Services/StorageService.php`
    - `app/Controllers/FileController.php`
    - `app/Controllers/Settings.php`
    - `app/Config/Services.php`
    - `app/Config/Routes.php`
- **Verification:** `docs/SaaS/verification/P5-01.md`. Files are stored privately and gated by clinic context.
- **Schema fix:** Ran `php spark migrate` to finish `2026-01-15-000000 AdjustSettingsUniqueIndex`, confirmed the composite `(clinic_id, setting_key)` index via `SHOW INDEX FROM settings`, and demonstrated Care Clinic names persist per clinic (`setting_key='clinic_name'`). Guardrails still pass (DOM=0, Group=0, Raw=24).

### Task: P5-02 IDOR Sweep on Tenant Resources
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Implemented strict tenant isolation (clinic_id scoping) across all critical resource controllers.
- **Scope:**
  - **Patient:** Fixed `show`, `edit`, `update`, `delete`, `search`, `getPatientData`. Updated `Api\Patient`.
  - **Appointment:** Fixed `show`, `update`, `delete`, `confirm`, `complete`, `cancel`, `print`. Updated `AppointmentModel` to support scoping in `getAppointmentWithPatient`. Updated `Api\Appointment`.
  - **Finance:** Fixed `show`, `update`, `delete`, `markAsPaid`, `generateInvoice`, `export`, `bulk*`. Updated `Api\Finance`.
  - **Inventory:** Fixed `index`, `show`, `edit`, `update`, `delete`, `adjust`, `lowStock`, `expired`, `usage`, `getInventoryData`, `getUsageHistoryData`.
  - **Treatment:** Fixed `index`, `getTreatmentsData`, `update`, `delete`, `complete`.
  - **Prescription:** Fixed `index`, `getPrescriptionsData`.
  - **Odontogram:** Fixed `index`, `getPatientsData`, `updateTooth`, `resetTooth`, `export`, `print`, `pdf`.
  - **Examination:** Updated `Api\Examination`.
- **Verification:** All ID-based fetches now verify `clinic_id` matches `session('active_clinic_id')`. Failures return 404 (HTML) or 403 (API).

### Task: P5-03 Restore Missing Management Routes & Enforce Scoping
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Restored missing routes for `doctors`, `users`, and `roles`. Identified that `users` table is global and relies on `clinic_users` pivot for tenancy. Patched Controllers and Models to enforce this scoping for lists and creation.
- **Files Changed:**
    - `app/Config/Routes.php` (Added route groups)
    - `app/Models/UserModel.php` (Added `getUsersByClinic`)
    - `app/Controllers/Users.php` (Used scoped fetch in index, added pivot insert in store)
    - `app/Controllers/Doctor.php` (Used scoped fetch in index, added pivot insert in store)
- **Verification:** Routes are active. User lists are now filtered by `clinic_users`.

### Task: P5-02c Guardrail Remediation (Raw SQL Removal)
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Removed all usages of `$db->table()` and raw SQL queries from controllers, moving logic to tenant-aware Models and Services.
- **Files Changed:**
    - `app/Controllers/Inventory.php` (Used InventoryModel)
    - `app/Controllers/Treatment.php` (Used TreatmentModel)
    - `app/Controllers/Prescription.php` (Used PrescriptionModel)
    - `app/Controllers/Doctor.php` (Used UserModel & ClinicUserModel)
    - `app/Controllers/Users.php` (Used ClinicUserModel)
    - `app/Controllers/Settings.php` (Moved backup to SettingsService)
    - `app/Models/InventoryModel.php`, `TreatmentModel.php`, `PrescriptionModel.php`, `UserModel.php` (Added scoped methods)
    - `app/Services/SettingsService.php` (Added backup generation)
    - `docs/SaaS/guardrails/raw-tenant-queries.allowlist` (Cleaned up)
- **Verification:** `scripts/ci/saas_guardrails.sh` (simulated via grep) passes with no unexpected raw queries.

### Task: P5-04 Management IDOR Closure (Users/Doctor)
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Secured Users and Doctor controllers against IDOR by enforcing tenant isolation on all ID-accepting methods.
- **Actions:**
    - **Models:** Added `ClinicUserModel::isUserInClinic` and `UserModel::findByClinic`. Updated `UserModel::getDoctorWithDetails` to support scoping.
    - **Users Controller:** Updated `show`, `edit`, `update`, `delete`, `changePassword`, `updatePassword`, `toggleStatus`, and all RBAC AJAX methods to validate `active_clinic_id` and ownership via `findByClinic`/`isUserInClinic`.
    - **Doctor Controller:** Updated `show`, `edit`, `update`, `delete` to use `getDoctorWithDetails($id, $clinicId)` for ownership verification.
- **Verification:** `docs/SaaS/verification/P5-04.md`. All methods now fail-closed (404/403) on tenant mismatch.
- **Guardrails:** Green. No new raw queries.

### Task: S0-01 Stabilization - Doctor Creation and Roles Sync UI
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Fixed empty Medical Role dropdown and repaired broken Roles Sync button.
- **Actions:**
    - **Models/Services:** Updated `PermissionSyncService` to correctly apply `is_medical` flag to medical roles.
    - **Database:** Created and executed migration `FixMedicalRolesAndSyncPermissions` to stabilize role data and mark `doctor`, `senior_doctor`, and `dental_assistant` as medical roles.
    - **Controllers:** Refactored `RoleController::sync()` to execute the sync directly, resolving 404 errors caused by `controlplane` filter on redirected route.
    - **Views:** Added empty-state handling to doctor creation and edit dropdowns.
- **Verification:** `docs/SaaS/verification/S0-01.md`. Dropdown is populated and Sync button is functional.
- **Guardrails:** Green. Raw count = 8.

### Task: S0-02 Roles Sync IDOR Fix (Tenant-Scoped Role-User UI)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Secured Role Management UI against cross-tenant user visibility by scoping user counts and lists to the active clinic.
- **Actions:**
    - **Models:** Updated `RoleModel::getUsers` and `RoleModel::getUserCount` to support optional `clinic_id` scoping via join with `clinic_users`.
    - **Controllers:** Updated `RoleController::index` and `RoleController::show` to pass `active_clinic_id` to model methods.
- **Verification:** `docs/SaaS/verification/S0-02.md`. Role lists and details now only reflect members of the active clinic.
- **Guardrails:** Green. Raw count = 8.

### Task: S0-03 System-only Roles Lockdown + Cleanup
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Restricted Role definition management to the Control Plane (Super Admins) and purged leaked tenant roles.
- **Actions:**
    - **Controllers:** Implemented `ensureControlPlane()` guard in `RoleController` and restricted individual permission overrides in `Users` controller to Super Admins.
    - **UI:** Updated `/roles` view to hide/disable management actions for tenant admins.
    - **Database:** Executed `PurgeTenantRoles` migration to remove leaked roles and their associated assignments.
- **Verification:** `docs/SaaS/verification/S0-03.md`. Roles are now read-only for tenants and purged of custom definitions.
- **Guardrails:** Green. Raw count = 8.

### Task: P5-05 Tenant-safe exports/PDFs/reports (Persist exports policy)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Implemented mandatory persistence for all export and print actions across the platform.
- **Actions:**
    - **Models/Infrastructure:** Enhanced `FileAttachmentModel` and `StorageService` to support export persistence with hash-based idempotency.
    - **Controllers:** Updated `Finance`, `Examination`, `Prescription`, `Odontogram`, `Appointment`, `Inventory`, and `Reports` to enforce ownership before generation and persist all artifacts (PDF/HTML/CSV) before delivery.
- **Verification:** `docs/SaaS/verification/P5-05.md`. All export/print endpoints now create a trackable `file_attachment` record scoped to the clinic.
- **Guardrails:** Green. Raw count remains at 8.

### Task: P5-06 Tenant-aware background jobs (asynchronous context hardening)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Secured background and CLI operations by enforcing explicit clinic context bootstrapping.
- **Actions:**
    - **Framework:** Implemented `TenantJob` base class to mandate `clinic_id` and bootstrap a mock session context for CLI operations.
    - **Implementation:** Created `ExportReportsJob` and a unified CLI command `tenant:run-job` to trigger tenant-scoped background tasks.
- **Verification:** `docs/SaaS/verification/P5-06.md`. Jobs fail-closed without `clinic_id` and correctly isolate data/files when run.
- **Guardrails:** Green.

### Task: P5-06b Tenant Job Governance Hardening
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Enhanced job execution with mandatory `--clinic-id`, forbidden user overrides, and a system-wide audit trail.
- **Actions:**
    - **Database:** Created `job_audits` table to track every invocation (even failures).
    - **Governance:** Updated `RunTenantJob` CLI to enforce `--clinic-id` requirement and reject per-user flags.
    - **Auditing:** Implemented fail-fast logging that records missing context even before DB access.
- **Verification:** `docs/SaaS/verification/P5-06b.md`. CLI command now exit(1) on missing clinic-id or forbidden flags.

### Task: P5-07 TenantAwareModel base + selective adoption
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Introduced a centralized `TenantAwareModel` to enforce tenant isolation at the data access layer.
- **Actions:**
    - **Base Model:** Created `TenantAwareModel` with `forClinic`, `findByClinic`, and `countByClinic` helpers.
    - **Migration:** Refactored `PatientModel`, `AppointmentModel`, and `ActivityLogModel` to inherit from the new base.
    - **Controller Cleanup:** Refactored `Patient`, `Appointment`, and `ActivityLog` controllers to use scoped model helpers, removing manual `where('clinic_id', ...)` repetitions.
- **Verification:** `docs/SaaS/verification/P5-07.md`. Models now automatically stamp `clinic_id` on insert and provide safer query APIs.
- **Guardrails:** Green.

### Task: P5-08 Export Retention Policy (Superadmin-configurable)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Implemented global export retention policies with Superadmin control and physical pruning capabilities.
- **Actions:**
    - **Service:** Created `RetentionService` to handle `latest`, `keep_last_n`, and `keep_x_days` policies.
    - **Automation:** Integrated retention enforcement into `StorageService->storeExport()`.
    - **Governance UI:** Added "Export Retention" tab to settings for Superadmins (Global Mode).
    - **Maintenance:** Implemented `exports:prune` CLI command for physical cleanup of superseded artifacts.
- **Verification:** `docs/SaaS/verification/P5-08.md`. Retention policies correctly soft-delete old exports, and pruning removes them from disk.
- **Guardrails:** Green.

### Task: S0-04 Clinic Switcher Dropdown (secure tenant context switching)
- **Date:** 2026-01-15
- **Status:** Completed (Updated UI)
- **Description:** Added a membership-validated clinic switcher UI for users belonging to multiple clinics.
- **Refinement:** Moved switcher to the top-right header for better visibility and standard UX.
- **Actions:**
    - **Infrastructure:** Enhanced `BaseController` to provide global membership data to views.
    - **Controller:** Implemented `ClinicSelector::switch()` with strict membership validation and session clearing.
    - **UI:** Added a "Switch Clinic" dropdown in the main header (right side), conditionally shown for multi-clinic users.
- **Verification:** `docs/SaaS/verification/S0-04.md`. Switcher correctly updates session context and prevents unauthorized switching.
- **Guardrails:** Green.

### Task: P5-06b Verification & Fixes (Job Governance)
- **Date:** 2026-01-15
- **Status:** Verified & Fixed
- **Description:** Verified fail-fast and audit logging for tenant jobs. Fixed a fatal error in `FileAttachmentModel` (signature mismatch) that blocked job execution. Also remediated a regression in `app/Views/settings/index.php` (unsafe `innerHTML` usage) to satisfy guardrails.
- **Files Changed:**
    - `app/Models/FileAttachmentModel.php` (Renamed `purgeDeleted` to `deletePermanently`)
    - `app/Services/RetentionService.php` (Updated method call)
    - `app/Views/settings/index.php` (Refactored `pruneExports` to use safe DOM methods)
    - `docs/SaaS/verification/P5-06b.md` (Created verification artifacts)
- **Verification:**
    - CLI `tenant:run-job` now works correctly (fail-fast and success paths).
    - `job_audits` table populates correctly.
    - Guardrails are GREEN (0 DOM sinks, 8 raw queries).

### Task: S0-04a Clinic Switcher Visibility Fix
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Resolved issue where the clinic switcher was not visible when using the global `view()` helper.
- **Actions:**
    - **BaseController:** Moved `user_memberships`, `user`, and `user_groups` injection to `initController()` using the renderer's `setVar()` method.
    - **Verification:** Confirmed visibility for multi-clinic users across all views (HTML/API context).
- **Guardrails:** Green (Raw count: 8).
