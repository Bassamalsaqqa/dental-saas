# SaaS Readiness Deep Scan Report - DentaCare Pro

## 1) SaaS Readiness Executive Summary
*   **Critical Blocker (Data Isolation):** Zero multi-tenant capability. All core tables (`users`, `patients`, `appointments`) lack a `clinic_id` or `tenant_id` column.
*   **Critical Blocker (Settings):** `SettingsModel` is a global key-value store. `Settings::updateClinic` actively deletes *any* existing logo file before saving a new one, meaning one tenant's logo would wipe out another's.
*   **Critical Blocker (Routing):** Application is host-agnostic. `App.php` accepts any hostname but maps them all to the same global route content. No middleware exists to resolve `subdomain -> tenant`.
*   **Major Enablement (Auth):** `IonAuth` is used, which supports Groups. This can be leveraged for "Global Super Admin" vs "Clinic Admin" if extended properly.
*   **Major Enablement (Code Structure):** Code is well-structured in Models and Controllers (MVC). Injecting `where('clinic_id', $id)` into a `BaseModel` would cover 90% of read/write logic efficiently.
*   **Key Unknown:** How "permissions" in `UserModel` (hardcoded array) interact with `IonAuth` groups in practice. The code shows a mix of DB groups and hardcoded role checks.
*   **Deployment:** `democa_dental.sql` provides a clean baseline, but migrations (`app/Database/Migrations`) are present, suggesting a hybrid schema management approach.

## 2) Tenant Resolution Surfaces
*   **Request Host:** `app/Config/App.php` (L27) dynamically detects `baseURL` but stores no tenant context.
*   **Routes:** `app/Config/Routes.php` has no host-based route groups. All routes are global.
*   **Resolution Point:** Needs to be injected in `app/Config/Filters.php` as a "before" filter (e.g., `TenantResolver`) to parse `$_SERVER['HTTP_HOST']`.
*   **Evidence:** `App.php` (L38-60) contains the logic for host detection which currently ignores subdomains for identity.

## 3) AuthN/AuthZ & Scope Model
*   **Current State:**
    *   **Auth:** IonAuth (`app/Config/IonAuth.php`).
    *   **Roles:** `users` table has a `role` integer. `UserModel::getUserPermissions` (L158) maps these integers to hardcoded permission arrays.
    *   **Super Admin:** `Auth::index` (L45) checks `$this->ionAuth->isAdmin()`. This likely checks the 'admin' group in the `groups` table.
*   **Touchpoints for Multi-Tenancy:**
    *   **A) Global Super Admin:** Use existing 'admin' group in IonAuth. Bypasses tenant checks.
    *   **B) Multi-clinic user:** Requires a `user_clinics` pivot table. `BaseController` must allow switching `active_clinic_id` in session.
    *   **C) Single-clinic user:** `clinic_id` column on `users` table.
*   **Evidence:** `UserModel.php` L158-228 (hardcoded permissions) and `Auth.php` L45 (admin check).

## 4) Data Access Layer Audit (Tenant Leak Risk)
| File | Function | Query Type | Tables Touched | Leak Risk | Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `app/Models/PatientModel.php` | `findAll`, `searchPatients` | Model | `patients` | **HIGH** | No tenant filter. |
| `app/Models/AppointmentModel.php` | `getAllAppointments` | Model | `appointments` | **HIGH** | Joins patients globally. |
| `app/Controllers/Settings.php` | `updateClinic` | File Op | `uploads/clinic/` | **CRITICAL** | Deletes `clinic-logo.*` globally before save. |
| `app/Controllers/Settings.php` | `performBackup` | Raw SQL | *ALL* | **HIGH** | Dumps entire DB via `SHOW CREATE TABLE`. |
| `app/Models/UserModel.php` | `getDoctors` | Query Builder | `users`, `roles` | **HIGH** | No clinic dimension. |

## 5) Routes & Endpoint Inventory
*   **Web Routes:** Extensive grouping by module (Patient, Examination, etc.) with `auth` and `permission` filters.
*   **API Routes:** `api/v1` group (Routes.php L398) returns JSON for core entities.
*   **DataTables Endpoints:** `Patient::getData`, `Finance::getFinancesData`, etc. These are the highest blast radius for tenant leaks as they power the main lists.
*   **Evidence:** `app/Config/Routes.php` L55-434.

## 6) Settings & Branding Audit
*   **Settings Table:** `setting_key`, `setting_value`, `setting_type`.
*   **Read/Write:** `SettingsModel::getSetting` and `setSetting`.
*   **Evidence:** `app/Models/SettingsModel.php` L46 (getSetting) and L55 (setSetting).
*   **Status:** Global. Moving to multi-tenant requires a `clinic_id` column or tenant-specific keys in this table.

## 7) Uploads & File Isolation Audit
*   **Upload Paths:** `FCPATH . 'uploads/clinic'` is hardcoded in `Settings.php` (L86).
*   **Public Exposure:** Files are served directly from the public webroot.
*   **Requirement:** Change to `uploads/{tenant_uuid}/...` and implement access control via a PHP proxy or signed URLs to prevent cross-tenant enumeration.
*   **Evidence:** `app/Controllers/Settings.php` L86-107.

## 8) Database Reality Check
*   **Table Inventory:** 24 tables identified in `democa_dental.sql`.
*   **Relations:** Implied by naming (e.g., `patient_id` in `appointments`). No Foreign Keys in SQL.
*   **Candidate Composite Keys:**
    *   `appointments.appointment_id` + `clinic_id`
    *   `patients.patient_id` + `clinic_id`
*   **Evidence:** `democa_dental.sql` L30-652.

## 9) Multi-Tenancy Strategy Fit Check
*   **Option A: Single DB + clinic_id (Row-Level)**
    *   **Repo Touchpoints:** `app/Models/BaseModel.php` (must be created), all Models must extend it. `app/Filters/TenantFilter.php` for session/subdomain resolution.
    *   **Complexity:** Low operational overhead, high initial code change.
*   **Option B: DB-per-tenant (Connection Switching)**
    *   **Repo Touchpoints:** `app/Config/Database.php` needs dynamic group generation.
    *   **Complexity:** High operational risk (migrations must run N times), low data leak risk.
*   **Verdict:** Codebase favors **Option A** due to standard MVC patterns and centralized Model logic.

## 10) Verification Appendix
*   **Subdomain Check:** `grep -r "HTTP_HOST" app/`
*   **SQL Schema:** `type democa_dental.sql`
*   **Query Builder Scan:** `grep -r "->table(" app/Models/`
*   **Raw SQL Scan:** `grep -r "->query(" app/`

## 11) Tenant Leak Risk Audit (Top 30 High-Risk Occurrences)

| # | File Path : Line | Query Snippet | Tables Involved | Leak Risk | Reason |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `app/Controllers/Settings.php:663` | `SELECT * FROM {$table}` | ALL | **CRITICAL** | Actively dumps all data for all tenants on demand. |
| 2 | `app/Controllers/Settings.php:655` | `SHOW CREATE TABLE {$table}` | ALL | **HIGH** | Exposes system-wide schema structure to any tenant. |
| 3 | `app/Controllers/RepairController.php:20` | `ALTER TABLE finances ADD COLUMN...` | `finances` | **HIGH** | Global schema modification risks data corruption/locking. |
| 4 | `app/Controllers/Patient.php:66` | `$this->patientModel->findAll()` | `patients` | **HIGH** | Debug/Test methods returning the entire global patient list. |
| 5 | `app/Controllers/Patient.php:121` | `->limit(...)->find()` | `patients` | **HIGH** | Primary DataTables source for patients; no tenant filtering. |
| 6 | `app/Controllers/Finance.php:581` | `$query->findAll()` | `finances`, `patients` | **HIGH** | Main financial report leaks revenue across all clinics. |
| 7 | `app/Controllers/Appointment.php:153` | `->where('status', 'active')->findAll()` | `patients` | **HIGH** | Loads all system patients into a dropdown for new appointments. |
| 8 | `app/Controllers/Treatment.php:57` | `->findAll()` | `treatments` | **HIGH** | Lists all medical treatments globally without scoping. |
| 9 | `app/Controllers/Inventory.php:44` | `$this->inventoryModel->findAll()` | `inventory` | **HIGH** | Exposes global stock levels to all clinic admins. |
| 10 | `app/Controllers/Reports.php:235` | `->where('created_at >=', ...)->findAll()` | `patients` | **HIGH** | Aggregates patients globally for reporting. |
| 11 | `app/Controllers/Reports.php:265` | `->where('created_at >=', ...)->findAll()` | `finances` | **HIGH** | Global financial aggregation leak. |
| 12 | `app/Models/PatientModel.php:112` | `->like(...)->findAll()` | `patients` | **HIGH** | Global search allows finding any patient in the system. |
| 13 | `app/Models/AppointmentModel.php:126` | `->findAll()` | `appointments`, `patients` | **HIGH** | Primary appointment list method lacks clinic scope. |
| 14 | `app/Models/FinanceModel.php:127` | `->select(...)->findAll()` | `finances` | **HIGH** | Core financial method returns global history. |
| 15 | `app/Models/UserModel.php:132` | `->findAll()` | `users` | **HIGH** | Lists all system staff and admins from all clinics. |
| 16 | `app/Controllers/Api/Patient.php:18` | `->findAll(100)` | `patients` | **HIGH** | API endpoint dumps first 100 global patients. |
| 17 | `app/Controllers/Api/Appointment.php:18` | `->findAll(100)` | `appointments` | **HIGH** | API endpoint dumps first 100 global appointments. |
| 18 | `app/Controllers/Dashboard.php:196` | `->limit(5)->findAll()` | `patients` | **HIGH** | Dashboard widget shows recent patients system-wide. |
| 19 | `app/Controllers/UserManagementController.php:429` | `$this->userModel->findAll()` | `users` | **HIGH** | User list exposes all clinic staff globally. |
| 20 | `app/Controllers/InventoryUsage.php:36` | `->findAll()` | `inventory_usage`, `patients` | **HIGH** | Usage history leaks sensitive patient-treatment links. |
| 21 | `app/Models/OdontogramModel.php:75` | `->where('patient_id', $id)->findAll()` | `odontograms` | **MED** | IDOR risk; leaks dental charts if patient_id isn't validated. |
| 22 | `app/Models/ExaminationModel.php:95` | `->where('patient_id', $id)->findAll()` | `examinations` | **MED** | IDOR risk for clinical examination records. |
| 23 | `app/Controllers/Api/Search.php:51` | `->like('first_name', $query)->findAll()` | `patients` | **HIGH** | Global patient autocomplete leaks identity. |
| 24 | `app/Controllers/Api/Search.php:249` | `->like('name', $query)->findAll()` | `roles` | **MED** | Leaks role/permission structures across tenants. |
| 25 | `app/Models/TreatmentModel.php:108` | `->join(...)->findAll()` | `treatments`, `patients` | **HIGH** | Lists all treatments globally without tenant context. |
| 26 | `app/Controllers/Examination.php:54` | `->where('status', 'active')->findAll()` | `patients` | **HIGH** | Loads all patients into UI for examination creation. |
| 27 | `app/Controllers/Prescription.php:229` | `$this->patientModel->findAll()` | `patients` | **HIGH** | Loads all patients for prescription creation. |
| 28 | `app/Models/InventoryModel.php:111` | `$this->findAll()` | `inventory` | **HIGH** | `getAllInventory` method leaks global stock data. |
| 29 | `app/Models/InventoryModel.php:189` | `->like(...)->findAll()` | `inventory` | **HIGH** | Global inventory search capability. |
| 30 | `app/Services/PermissionSyncService.php:94` | `$this->permissionModel->findAll()` | `permissions` | **MED** | Assumes permissions are global; no per-tenant overrides. |