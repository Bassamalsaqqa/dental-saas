### Task: S3-01 Migrations (Columns & Indexes)
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Added `clinic_id` column (nullable) and index to all tenant tables (`patients`, `appointments`, etc.).
- **Files Changed:**
    - `app/Database/Migrations/2026-01-14-101059_AddClinicIdToTenantTables.php`
- **Verification:** `docs/SaaS/verification/S3-01.md`

### Task: S3-02 Backfill Strategy
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Backfilled all existing tenant records with Default Clinic ID (1). Handled missing default clinic scenario.
- **Files Changed:**
    - `app/Database/Migrations/2026-01-14-101229_BackfillClinicId.php`
- **Verification:** `docs/SaaS/verification/S3-02.md`

### Task: S3-04 Write-Path Enforcement
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Implemented `TenantTrait` to automatically inject `session('active_clinic_id')` on insert. Applied trait to all tenant models.
- **Files Changed:**
    - `app/Traits/TenantTrait.php` (Created)
    - `app/Models/PatientModel.php`, `AppointmentModel.php`, `ExaminationModel.php`, `FinanceModel.php`, `TreatmentModel.php`, `PrescriptionModel.php`, `InventoryModel.php`, `InventoryUsageModel.php`, `OdontogramModel.php`, `ActivityLogModel.php`, `SettingsModel.php` (Updated)
- **Verification:** `docs/SaaS/verification/S3-04.md`

### Task: S3-03 Constraints (NOT NULL & Uniques)
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Modified `clinic_id` to `NOT NULL` and added Foreign Keys to `clinics` table. Skipped Unique Constraints to avoid failing on duplicates (deferred to Phase 4/Cleanup).
- **Files Changed:**
    - `app/Database/Migrations/2026-01-14-102341_EnforceClinicIdConstraints.php`
- **Verification:** `docs/SaaS/verification/S3-03.md`

### Task: S3-05 Allowlist Hygiene
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Reviewed allowlist. No entries removed as controller read queries remain raw (Phase 4 scope).
- **Verification:** `docs/SaaS/verification/S3-05.md`

### Task: D-01 Tenant Diagnostic Logging
- **Date:** 2026-01-14
- **Status:** Instrumentation (temporary)
- **Description:** Added TENANT_DIAG-guarded logging inside `Patient::getData()` so we can prove the tenant list still returns all rows after switching clinics while `active_clinic_id` actually changes. Removal will follow after diagnostics (Task D-03).
- **Files Changed:**
    - `app/Controllers/Patient.php`
    - `docs/SaaS/verification/D-01.md`
- **Verification:** Set `TENANT_DIAG=1`, reproduce Switch → List, and inspect `[TENANT-DIAG]` lines in `writable/logs/log-*.log`.
