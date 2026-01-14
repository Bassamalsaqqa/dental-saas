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
- **Guardrails:** `bash scripts/ci/saas_guardrails.sh` → DOM=0, Group=0, Raw=8; SaaS guardrail validation succeeded.

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
