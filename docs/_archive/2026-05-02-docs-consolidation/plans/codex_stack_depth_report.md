**1) SaaS Readiness Executive Summary**
- Core transactional tables have no tenant key, so patient/appointment/examination/finance records are global; tenant isolation is currently not possible. Evidence: `democa_dental.sql:59`, `democa_dental.sql:95`, `democa_dental.sql:125`, `democa_dental.sql:277`
- Base URL is derived directly from `HTTP_HOST` with no subdomain-to-clinic parsing logic; tenant resolution is not implemented. Evidence: `app/Config/App.php:31`, `app/Config/App.php:46`
- Session identity stores user_id/email only; no clinic context is persisted. Evidence: `app/Models/IonAuthModel.php:2004`, `app/Models/IonAuthModel.php:2008`
- RBAC exists and is enforced via PermissionFilter and PermissionService, which is a strong anchor for scope enforcement. Evidence: `app/Filters/PermissionFilter.php:49`, `app/Services/PermissionService.php:31`, `democa_dental.sql:328`
- Super admin is represented by RBAC role slug `super_admin` plus IonAuth admin group `admin`; this provides a clear global-override identity. Evidence: `democa_dental.sql:435`, `app/Services/PermissionService.php:167`, `app/Config/IonAuth.php:149`, `democa_dental.sql:171`
- Data listing/search/report endpoints (DataTables, reports, API search) are global and will leak across clinics without scoping. Evidence: `app/Controllers/Patient.php:100`, `app/Controllers/Reports.php:232`, `app/Controllers/Api/Search.php:35`
- Settings/branding are global key/value records and injected into all views; multi-tenant branding would collide. Evidence: `app/Models/SettingsModel.php:103`, `app/Services/SettingsService.php:356`, `app/Controllers/BaseController.php:72`, `democa_dental.sql:506`
- Uploads are stored under a shared public path (`uploads/clinic`) with a fixed filename, not per-clinic. Evidence: `app/Controllers/Settings.php:67`, `app/Controllers/Settings.php:88`, `app/Views/layouts/main.php:27`
- Summary tables exist but population is UNKNOWN; no code references found. Evidence: `democa_dental.sql:81`, `democa_dental.sql:156`, `democa_dental.sql:310`
- DB-per-tenant would require connection switching because the default group is used broadly. Evidence: `app/Config/Database.php:17`, `app/Config/IonAuth.php:25`, `app/Controllers/Examination.php:27`

**2) Tenant Resolution Surfaces**
- Request host/subdomain is only used to build baseURL; no clinic parsing. Evidence: `app/Config/App.php:36`, `app/Config/App.php:46`
- BaseURL can be overridden by `app.baseURL`; allowed hostnames are empty, so no multi-host allowlist is configured. Evidence: `app/Config/App.php:31`, `app/Config/App.php:63`
- Routing is static with auth/admin/permission filters; no tenant filter or subdomain route group exists. Evidence: `app/Config/Routes.php:43`, `app/Config/Routes.php:406`
- Session user identity comes from IonAuth setSession; BaseController relies on IonAuth user for view data. Evidence: `app/Models/IonAuthModel.php:2004`, `app/Controllers/BaseController.php:104`
- Clinic/global settings are injected into views via BaseController and consumed by layouts. Evidence: `app/Controllers/BaseController.php:72`, `app/Services/SettingsService.php:356`, `app/Views/layouts/main_auth.php:49`

**3) AuthN/AuthZ & Scope Model**
- IonAuth group checks: AuthFilter uses `loggedIn`, AdminFilter uses `isAdmin`, helper `in_group` wraps IonAuth groups. Evidence: `app/Filters/AuthFilter.php:38`, `app/Filters/AdminFilter.php:42`, `app/Helpers/auth_helper.php:63`
- Custom RBAC: PermissionFilter enforces `permission:<module>:<action>` via PermissionService and the permissions/roles tables. Evidence: `app/Filters/PermissionFilter.php:49`, `app/Services/PermissionService.php:31`, `democa_dental.sql:328`, `democa_dental.sql:418`
- Super admin representation: role slug `super_admin` (RBAC) and IonAuth admin group `admin`; PermissionSyncService maps IonAuth group ID 1 to super_admin. Evidence: `democa_dental.sql:435`, `app/Services/PermissionService.php:167`, `app/Config/IonAuth.php:149`, `app/Services/PermissionSyncService.php:150`, `democa_dental.sql:181`
- A) Global super admin scope enforcement touchpoints: `PermissionService::isSuperAdmin`, PermissionFilter, and menu gating via `has_permission`. Evidence: `app/Services/PermissionService.php:203`, `app/Filters/PermissionFilter.php:49`, `app/Views/roles/index.php:214`
- B) Multi-clinic user touchpoints: role assignments are in `user_roles` with no clinic attribute; Users controller assigns roles globally. Evidence: `democa_dental.sql:652`, `app/Models/UserRoleModel.php:16`, `app/Controllers/Users.php:579`
- C) Single-clinic user touchpoints: same as above plus global settings injection; no clinic-specific user context exists today. Evidence: `app/Controllers/BaseController.php:72`, `app/Models/IonAuthModel.php:2004`, `democa_dental.sql:652`

**4) Data Access Layer Audit**
- BaseModel pattern: not found (no shared model-level scoping). Evidence: `rg -n "BaseModel" app` (no matches)

Model inventory (table mapping):
| Model | Table | Evidence |
| --- | --- | --- |
| `ActivityLogModel` | `activity_logs` | `app/Models/ActivityLogModel.php:9` |
| `AppointmentModel` | `appointments` | `app/Models/AppointmentModel.php:9` |
| `ExaminationModel` | `examinations` | `app/Models/ExaminationModel.php:9` |
| `FinanceModel` | `finances` | `app/Models/FinanceModel.php:9` |
| `InventoryModel` | `inventory` | `app/Models/InventoryModel.php:9` |
| `InventoryUsageModel` | `inventory_usage` | `app/Models/InventoryUsageModel.php:9` |
| `IonAuthModel` | `users`, `groups`, `users_groups`, `login_attempts` (via config) | `app/Config/IonAuth.php:49` |
| `OdontogramModel` | `odontograms` | `app/Models/OdontogramModel.php:9` |
| `PatientModel` | `patients` | `app/Models/PatientModel.php:9` |
| `PermissionModel` | `permissions` | `app/Models/PermissionModel.php:9` |
| `PrescriptionModel` | `prescriptions` | `app/Models/PrescriptionModel.php:9` |
| `RoleModel` | `roles` | `app/Models/RoleModel.php:9` |
| `SettingsModel` | `settings` | `app/Models/SettingsModel.php:9` |
| `TreatmentModel` | `treatments` | `app/Models/TreatmentModel.php:9` |
| `UserModel` | `users` | `app/Models/UserModel.php:9` |
| `UserRoleModel` | `user_roles` | `app/Models/UserRoleModel.php:9` |

Leak-risk query inventory:
| File | Function | Query Type | Tables Touched | Leak Risk | Notes |
| --- | --- | --- | --- | --- | --- |
| `app/Models/PatientModel.php` | `searchPatients`, `getPatientsWithStats` | Query builder findAll/join/paginate | `patients`, `examinations`, `appointments` | High | No clinic filter in list/search; Evidence: `app/Models/PatientModel.php:103`, `app/Models/PatientModel.php:117`, `app/Models/PatientModel.php:121` |
| `app/Controllers/Patient.php` | `getData` | DataTables query builder | `patients`, `examinations` | High | Global counts/results; Evidence: `app/Controllers/Patient.php:127`, `app/Controllers/Patient.php:142`, `app/Controllers/Patient.php:173` |
| `app/Models/AppointmentModel.php` | `getAllAppointments`, `getAppointmentsByDate`, `getAppointmentStats` | Query builder join/findAll/counts | `appointments`, `patients` | High | No clinic filter; Evidence: `app/Models/AppointmentModel.php:92`, `app/Models/AppointmentModel.php:103`, `app/Models/AppointmentModel.php:194` |
| `app/Controllers/Appointment.php` | `getCalendarEvents` | Query builder via model | `appointments`, `patients` | High | Calendar events global; Evidence: `app/Controllers/Appointment.php:510`, `app/Controllers/Appointment.php:527` |
| `app/Models/ExaminationModel.php` | `getRecentExaminations`, `getExaminationsByDateRange`, `getExaminationStats` | Query builder join/findAll | `examinations`, `patients` | High | No clinic filter; Evidence: `app/Models/ExaminationModel.php:98`, `app/Models/ExaminationModel.php:114`, `app/Models/ExaminationModel.php:125` |
| `app/Controllers/Examination.php` | `getExaminationsData` | DataTables query builder | `examinations`, `patients` | High | Global search/list; Evidence: `app/Controllers/Examination.php:353`, `app/Controllers/Examination.php:355` |
| `app/Models/TreatmentModel.php` | `getTreatmentsWithPatientInfo`, `searchTreatments`, `getRevenueByTreatmentType` | Query builder join/findAll/groupBy | `treatments`, `patients` | High | No clinic filter; Evidence: `app/Models/TreatmentModel.php:103`, `app/Models/TreatmentModel.php:228`, `app/Models/TreatmentModel.php:203` |
| `app/Models/PrescriptionModel.php` | `getPrescriptionsWithPatientInfo`, `searchPrescriptions`, `getMedicationStats` | Query builder join/findAll | `prescriptions`, `patients` | High | No clinic filter; Evidence: `app/Models/PrescriptionModel.php:98`, `app/Models/PrescriptionModel.php:178`, `app/Models/PrescriptionModel.php:159` |
| `app/Models/FinanceModel.php` | `getFinanceStats`, `getMonthlyRevenue`, `getOverduePayments` | Query builder selectSum/findAll | `finances`, `patients` | High | No clinic filter; Evidence: `app/Models/FinanceModel.php:137`, `app/Models/FinanceModel.php:195`, `app/Models/FinanceModel.php:214` |
| `app/Controllers/Finance.php` | `getFinancesData` | DataTables query builder | `finances`, `patients` | High | Global list; Evidence: `app/Controllers/Finance.php:481`, `app/Controllers/Finance.php:483` |
| `app/Controllers/Reports.php` | report generators (`getOverviewReports`, `getPatientReports`, `getAppointmentReports`, `getFinanceReports`, `getTreatmentReports`) | Query builder counts/findAll/selectSum | `patients`, `examinations`, `appointments`, `finances`, `treatments` | High | Global aggregates; Evidence: `app/Controllers/Reports.php:214`, `app/Controllers/Reports.php:235`, `app/Controllers/Reports.php:255`, `app/Controllers/Reports.php:265`, `app/Controllers/Reports.php:275` |
| `app/Controllers/Api/Search.php` | `patients`, `users`, `examinations`, `treatments`, `inventory`, `roles` | Query builder find/like | `patients`, `users`, `examinations`, `treatments`, `inventory`, `roles` | High | Select2 results global; Evidence: `app/Controllers/Api/Search.php:35`, `app/Controllers/Api/Search.php:81`, `app/Controllers/Api/Search.php:130`, `app/Controllers/Api/Search.php:178`, `app/Controllers/Api/Search.php:229`, `app/Controllers/Api/Search.php:413` |
| `app/Controllers/Api/Patient.php` | `index`, `show` | Resource controller findAll/find | `patients` | High | No clinic filter; Evidence: `app/Controllers/Api/Patient.php:18`, `app/Controllers/Api/Patient.php:25` |
| `app/Controllers/Api/Examination.php` | `index`, `show` | Resource controller findAll/find | `examinations` | High | No clinic filter; Evidence: `app/Controllers/Api/Examination.php:17`, `app/Controllers/Api/Examination.php:24` |
| `app/Controllers/Api/Appointment.php` | `index`, `show` | Resource controller findAll/find | `appointments` | High | No clinic filter; Evidence: `app/Controllers/Api/Appointment.php:18`, `app/Controllers/Api/Appointment.php:25` |
| `app/Controllers/Api/Finance.php` | `index`, `show` | Resource controller findAll/find | `finances` | High | No clinic filter; Evidence: `app/Controllers/Api/Finance.php:18`, `app/Controllers/Api/Finance.php:25` |
| `app/Models/InventoryModel.php` | `getInventoryWithStats`, `getLowStockItems`, `getCategoryStats` | Query builder findAll/groupBy | `inventory` | Med | Shared inventory lists; Evidence: `app/Models/InventoryModel.php:101`, `app/Models/InventoryModel.php:114`, `app/Models/InventoryModel.php:162` |
| `app/Controllers/Inventory.php` | `getInventoryData` | DataTables query builder | `inventory` | Med | Global list; Evidence: `app/Controllers/Inventory.php:622`, `app/Controllers/Inventory.php:696` |
| `app/Models/InventoryUsageModel.php` | `getUsageByDateRange`, `getUsageStats` | Query builder findAll/aggregate | `inventory_usage` | Med | No clinic filter; Evidence: `app/Models/InventoryUsageModel.php:92`, `app/Models/InventoryUsageModel.php:132` |
| `app/Models/OdontogramModel.php` | `getOdontogramByPatient` | Query builder findAll | `odontograms` | High | Patient-specific data; Evidence: `app/Models/OdontogramModel.php:71`, `app/Models/OdontogramModel.php:73` |
| `app/Models/ActivityLogModel.php` | `getRecentActivities` | Query builder join | `activity_logs`, `users` | Med | Global activity stream; Evidence: `app/Models/ActivityLogModel.php:76`, `app/Models/ActivityLogModel.php:78` |
| `app/Models/UserModel.php` | `getUsersWithStats`, `searchUsers`, `getDoctors` | Query builder findAll/join | `users`, `user_roles`, `roles` | High | User data is global; Evidence: `app/Models/UserModel.php:123`, `app/Models/UserModel.php:184`, `app/Models/UserModel.php:315` |
| `app/Controllers/Settings.php` | `generateDatabaseBackup` | Raw SQL (`SHOW CREATE TABLE`, `SELECT *`) | all tables | Med | Backup exports all data; Evidence: `app/Controllers/Settings.php:655`, `app/Controllers/Settings.php:663` |
| `app/Controllers/RepairController.php` | `index` | Raw SQL (`ALTER TABLE`) | `finances`, `users` | Low | Maintenance-only; Evidence: `app/Controllers/RepairController.php:20`, `app/Controllers/RepairController.php:31` |
| `app/Database/Migrations/2026-01-04-000004_UpdateFinancesCurrencyEnum.php` | `up`, `down` | Raw SQL (`ALTER TABLE`) | `finances` | Low | Schema change; Evidence: `app/Database/Migrations/2026-01-04-000004_UpdateFinancesCurrencyEnum.php:12`, `app/Database/Migrations/2026-01-04-000004_UpdateFinancesCurrencyEnum.php:18` |

**5) Routes & Endpoint Inventory**
Route groups and filters:
| Group | Filter | Evidence |
| --- | --- | --- |
| `auth` | none | `app/Config/Routes.php:9` |
| `dashboard` | `auth` + per-route `permission` | `app/Config/Routes.php:43` |
| `patient`, `patients` | `auth` + per-route `permission` | `app/Config/Routes.php:50`, `app/Config/Routes.php:67` |
| `examination`, `examinations` | `auth` + per-route `permission` | `app/Config/Routes.php:83`, `app/Config/Routes.php:105` |
| `odontogram` | `auth` + per-route `permission` | `app/Config/Routes.php:127` |
| `finance` | `auth` + per-route `permission` | `app/Config/Routes.php:142` |
| `appointment`, `appointments` | `auth` + per-route `permission` | `app/Config/Routes.php:163`, `app/Config/Routes.php:181` |
| `treatment`, `treatments` | `auth` + per-route `permission` | `app/Config/Routes.php:198`, `app/Config/Routes.php:215` |
| `prescription`, `prescriptions` | `auth` + per-route `permission` | `app/Config/Routes.php:232`, `app/Config/Routes.php:250` |
| `reports` | `auth` + per-route `permission` | `app/Config/Routes.php:265` |
| `inventory` | `auth` + per-route `permission` | `app/Config/Routes.php:272` |
| `notifications`, `activity-log` | `auth` | `app/Config/Routes.php:307`, `app/Config/Routes.php:316` |
| `settings` | `admin` | `app/Config/Routes.php:322` |
| `profile` | `auth` | `app/Config/Routes.php:339` |
| `users` | `auth` + per-route `permission` | `app/Config/Routes.php:346` |
| `roles` | `auth` + per-route `permission` | `app/Config/Routes.php:369` |
| `doctors` | `auth` + per-route `permission` | `app/Config/Routes.php:384` |
| `rbac` | `auth` | `app/Config/Routes.php:395` |
| `api` | `auth` + per-route `permission` | `app/Config/Routes.php:406` |
| `api/v1` | inherits `auth` | `app/Config/Routes.php:407` |
| `api/search` | inherits `auth` | `app/Config/Routes.php:438` |

JSON endpoints (selected, high blast radius):
- DataTables endpoints: `patient/get-data`, `examination/getExaminationsData`, `finance/getFinancesData`, `inventory/getInventoryData` (all return JSON lists). Evidence: `app/Config/Routes.php:60`, `app/Config/Routes.php:99`, `app/Config/Routes.php:155`, `app/Config/Routes.php:281`, `app/Controllers/Patient.php:100`, `app/Controllers/Examination.php:291`, `app/Controllers/Finance.php:402`, `app/Controllers/Inventory.php:622`
- Select2 endpoints: `api/search/*` returns JSON results. Evidence: `app/Config/Routes.php:438`, `app/Controllers/Api/Search.php:35`
- FullCalendar endpoints: `appointment/calendar-events`, `examination/calendar-events`. Evidence: `app/Config/Routes.php:177`, `app/Config/Routes.php:88`, `app/Controllers/Appointment.php:510`, `app/Controllers/Examination.php:609`
- API v1 endpoints: `api/v1/patients|examinations|appointments|finances` (JSON lists) with permission filters. Evidence: `app/Config/Routes.php:409`, `app/Config/Routes.php:416`, `app/Config/Routes.php:423`, `app/Config/Routes.php:430`
- Activity log + notifications JSON: `activity-log/api`, `api/notifications`. Evidence: `app/Config/Routes.php:318`, `app/Config/Routes.php:309`

**6) Settings & Branding Audit**
- Settings are loaded globally via `SettingsModel::getAllSettings` and cached in SettingsService. Evidence: `app/Models/SettingsModel.php:103`, `app/Services/SettingsService.php:152`
- Clinic branding fields are stored as global settings keys (`clinic_name`, `clinic_logo_path`, `clinic_tagline`) in the `settings` table. Evidence: `democa_dental.sql:520`, `democa_dental.sql:533`
- Settings updates are performed by `Settings::updateClinic` and defaults are seeded in `initializeDefaultSettings`. Evidence: `app/Controllers/Settings.php:55`, `app/Controllers/Settings.php:525`
- Branding is injected into all views via BaseController and rendered in layouts (`$clinic['name']`, `$clinic['logo_path']`, `$clinic['tagline']`). Evidence: `app/Controllers/BaseController.php:72`, `app/Services/SettingsService.php:356`, `app/Views/layouts/main.php:25`, `app/Views/layouts/main_auth.php:49`
- Multi-tenant risk: a single settings rowset would overwrite branding for all clinics. Evidence: `app/Models/SettingsModel.php:105`, `democa_dental.sql:506`

**7) Uploads & File Isolation Audit**
- Clinic logo upload path is `FCPATH . 'uploads/clinic'` and saved as `uploads/clinic/clinic-logo.*` (public web root). Evidence: `app/Controllers/Settings.php:67`, `app/Controllers/Settings.php:88`
- Layouts render logo from the stored path via `base_url`, so files under `public/uploads` are directly web-accessible. Evidence: `app/Views/layouts/main.php:27`, `app/Views/layouts/main.php:31`
- No tenant-specific directory or naming exists for uploads. Evidence: `app/Controllers/Settings.php:86`, `app/Controllers/Settings.php:88`
- Backups are stored under `WRITEPATH/backups` and are global to the app instance. Evidence: `app/Controllers/Settings.php:321`, `app/Controllers/Settings.php:497`

**8) Database Reality Check (from democa_dental.sql)**
Table inventory:
| Table | Evidence |
| --- | --- |
| `activity_logs` | `democa_dental.sql:30` |
| `appointments` | `democa_dental.sql:59` |
| `appointment_summary` | `democa_dental.sql:81` |
| `examinations` | `democa_dental.sql:95` |
| `finances` | `democa_dental.sql:125` |
| `financial_summary` | `democa_dental.sql:156` |
| `groups` | `democa_dental.sql:171` |
| `inventory` | `democa_dental.sql:191` |
| `inventory_usage` | `democa_dental.sql:216` |
| `login_attempts` | `democa_dental.sql:241` |
| `odontograms` | `democa_dental.sql:254` |
| `patients` | `democa_dental.sql:277` |
| `patient_examination_summary` | `democa_dental.sql:310` |
| `permissions` | `democa_dental.sql:328` |
| `permission_audit_log` | `democa_dental.sql:375` |
| `prescriptions` | `democa_dental.sql:393` |
| `roles` | `democa_dental.sql:418` |
| `role_permissions` | `democa_dental.sql:444` |
| `settings` | `democa_dental.sql:506` |
| `treatments` | `democa_dental.sql:540` |
| `users` | `democa_dental.sql:570` |
| `users_groups` | `democa_dental.sql:616` |
| `user_permissions` | `democa_dental.sql:635` |
| `user_roles` | `democa_dental.sql:652` |

Implied relations (no FK constraints in SQL):
- `appointments.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:61`, `democa_dental.sql:277`
- `examinations.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:97`, `democa_dental.sql:277`
- `finances.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:127`, `democa_dental.sql:277`
- `prescriptions.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:393`, `democa_dental.sql:277`
- `treatments.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:540`, `democa_dental.sql:277`
- `odontograms.patient_id` -> `patients.id`. Evidence: `democa_dental.sql:256`, `democa_dental.sql:277`
- `inventory_usage.treatment_id` -> `treatments.id` (implied by column name/comment). Evidence: `democa_dental.sql:216`, `democa_dental.sql:540`
- `user_roles.user_id` -> `users.id`, `user_roles.role_id` -> `roles.id`. Evidence: `democa_dental.sql:653`, `democa_dental.sql:570`, `democa_dental.sql:418`
- `users_groups.user_id` -> `users.id`, `users_groups.group_id` -> `groups.id`. Evidence: `democa_dental.sql:616`, `democa_dental.sql:171`, `democa_dental.sql:570`

Candidate unique keys that would need composite tenant key:
- `appointments.appointment_id`, `examinations.examination_id`, `finances.transaction_id`, `patients.patient_id`, `patients.email`, `prescriptions.prescription_id`, `treatments.treatment_id`, `users.email`, `roles.slug`, `settings.setting_key`. Evidence: `democa_dental.sql:690`, `democa_dental.sql:700`, `democa_dental.sql:710`, `democa_dental.sql:758`, `democa_dental.sql:759`, `democa_dental.sql:786`, `democa_dental.sql:820`, `democa_dental.sql:830`, `democa_dental.sql:797`, `democa_dental.sql:813`

Summary tables:
- `appointment_summary`, `financial_summary`, `patient_examination_summary` exist but population is UNKNOWN; verify by searching for these table names in app code and checking DB triggers or scheduled jobs. Evidence: `democa_dental.sql:81`, `democa_dental.sql:156`, `democa_dental.sql:310`

**9) Multi-Tenancy Strategy Fit Check (repo-grounded)**
- Option A: Single DB + `clinic_id` columns. Impacted touchpoints include all models and report/list/search endpoints (global findAll/join counts) plus settings and upload paths; migration work would add `clinic_id` and composite unique keys and adjust settings to per-clinic; operational complexity is moderate but failure mode is data leakage if any query misses the tenant filter. Evidence: `app/Models/PatientModel.php:103`, `app/Controllers/Reports.php:214`, `app/Models/SettingsModel.php:103`, `democa_dental.sql:690`
- Option B: DB-per-tenant with connection switching. Impacted touchpoints include Database config and IonAuth (default group), and widespread `Database::connect()` usage; migrations would need to run across all tenant DBs using `app/Database/Migrations`; operational complexity is high with failure modes around misrouting to wrong DB or schema drift, but isolation is strong if routing is correct. Evidence: `app/Config/Database.php:17`, `app/Config/IonAuth.php:25`, `app/Controllers/Examination.php:27`, `app/Controllers/Finance.php:24`, `app/Config/Database.php:12`

**10) Verification Appendix**
- `rg -n "^CREATE TABLE" democa_dental.sql`
- `rg -n "UNIQUE KEY|PRIMARY KEY|FOREIGN KEY" democa_dental.sql`
- `rg -n "patient_id|appointment_id|examination_id|transaction_id|treatment_id" democa_dental.sql`
- `rg -n "HTTP_HOST|app.baseURL" app/Config/App.php`
- `rg -n "setSession" app/Models/IonAuthModel.php`
- `rg -n "\\$table" app/Models`
- `rg -n "Database::connect|->table\\(" app`
- `rg -n -- "->query\\(|db->query" app`
- `rg -n "setJSON|respond\\(" app/Controllers`
- `rg -n "getCalendarEvents|get.*Data" app/Controllers`
- `rg -n "api/search" app/Config/Routes.php`
- `rg -n "clinic_name|clinic_logo_path|clinic_tagline" app`
- `rg -n "uploads|FCPATH|WRITEPATH" app/Controllers/Settings.php`
- `Get-ChildItem app\\Database\\Migrations`
- `php spark routes`

**Occurrences**
- `$db->query(`  
```
.\scripts\init_database.php:30:    $db->query("SELECT 1");
.\scripts\init_database.php:51:            $db->query($statement);
.\scripts\repair_db.php:24:    $db->query("ALTER TABLE finances ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount");
.\scripts\repair_db.php:32:$db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'");
.\app\Controllers\Settings.php:276:            $db->query("SELECT 1"); // Simple test query
.\app\Controllers\Settings.php:655:                    $query = $db->query("SHOW CREATE TABLE `{$table}`");
.\app\Controllers\Settings.php:663:                        $dataQuery = $db->query("SELECT * FROM `{$table}`");
.\app\Controllers\RepairController.php:20:                $db->query("ALTER TABLE finances ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount");
.\app\Controllers\RepairController.php:31:            $db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'");
.\app\Controllers\RepairController.php:48:                        $db->query("ALTER TABLE users MODIFY COLUMN ip_address VARCHAR(45) NULL");
.\app\Controllers\RepairController.php:51:                        $db->query("ALTER TABLE users MODIFY COLUMN created_on INT(11) UNSIGNED NULL");
.\app\Controllers\RepairController.php:67:                        $db->query("ALTER TABLE users ADD COLUMN hire_date DATE NULL AFTER phone");
.\app\Controllers\RepairController.php:69:                        $db->query("ALTER TABLE users ADD COLUMN active TINYINT(1) DEFAULT 1 AFTER hire_date");
.\app\Controllers\RepairController.php:71:                        $db->query("ALTER TABLE users ADD COLUMN address TEXT NULL AFTER active");
```
- `"SELECT "` inside PHP strings  
```
.\scripts\init_database.php:30:    $db->query("SELECT 1");
.\app\Controllers\Settings.php:276:            $db->query("SELECT 1"); // Simple test query
.\app\Controllers\Settings.php:663:                        $dataQuery = $db->query("SELECT * FROM `{$table}`");
```
- `->join(`  
```
.\app\Controllers\ActivityLog.php:77:                ->join('users u', 'u.id = al.user_id', 'left')
.\app\Controllers\Api\Search.php:138:            ->join('patients', 'patients.id = examinations.patient_id')
.\app\Controllers\Api\Search.php:186:            ->join('patients', 'patients.id = treatments.patient_id');
.\app\Services\PermissionService.php:119:                         ->join('permissions p', 'p.id = up.permission_id')
.\app\Services\PermissionService.php:339:                     ->join('users u', 'u.id = pal.user_id')
.\app\Services\PermissionService.php:340:                     ->join('users p', 'p.id = pal.performed_by', 'left');
.\app\Controllers\Doctor.php:272:                 ->join('user_roles ur', 'ur.user_id = u.id')
.\app\Controllers\Doctor.php:273:                 ->join('roles r', 'r.id = ur.role_id')
.\app\Controllers\Doctor.php:291:                    ->join('user_roles ur', 'ur.user_id = u.id')
.\app\Controllers\Doctor.php:292:                    ->join('roles r', 'r.id = ur.role_id')
.\app\Controllers\Finance.php:484:                ->join('patients', 'patients.id = ' . $financeTableName . '.patient_id', 'left');
.\app\Controllers\Finance.php:565:                ->join('patients', 'patients.id = finances.patient_id', 'left');
.\app\Controllers\Inventory.php:408:        $treatments = $treatmentModel->join('patients', 'patients.id = treatments.patient_id')
.\app\Controllers\Inventory.php:562:        $usageHistory = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id')
.\app\Controllers\Inventory.php:563:            ->join('patients', 'patients.id = treatments.patient_id')
.\app\Controllers\Inventory.php:564:            ->join('users', 'users.id = inventory_usage.recorded_by')
.\app\Controllers\Inventory.php:580:        $usage = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id')
.\app\Controllers\Inventory.php:581:            ->join('patients', 'patients.id = treatments.patient_id')
.\app\Controllers\Inventory.php:582:            ->join('users', 'users.id = inventory_usage.recorded_by')
.\app\Controllers\Inventory.php:603:        $usage = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id')
.\app\Controllers\Inventory.php:604:            ->join('patients', 'patients.id = treatments.patient_id')
.\app\Controllers\Inventory.php:605:            ->join('users', 'users.id = inventory_usage.recorded_by')
.\app\Controllers\Inventory.php:904:        $query = $usageModel->join('treatments', 'treatments.id = inventory_usage.treatment_id')
.\app\Controllers\Inventory.php:905:            ->join('patients', 'patients.id = treatments.patient_id')
.\app\Controllers\Inventory.php:906:            ->join('users', 'users.id = inventory_usage.recorded_by')
.\app\Controllers\Examination.php:356:                ->join('patients', 'patients.id = examinations.patient_id', 'left');
.\app\Controllers\Examination.php:460:                    ->join('patients', 'patients.id = examinations.patient_id', 'left')
.\app\Controllers\InventoryUsage.php:31:                    ->join('inventory', 'inventory.id = inventory_usage.inventory_id')
.\app\Controllers\InventoryUsage.php:32:                    ->join('patients', 'patients.id = inventory_usage.patient_id')
.\app\Controllers\InventoryUsage.php:33:                    ->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
.\app\Controllers\InventoryUsage.php:146:            ->join('inventory', 'inventory.id = inventory_usage.inventory_id')
.\app\Controllers\InventoryUsage.php:147:            ->join('patients', 'patients.id = inventory_usage.patient_id')
.\app\Controllers\InventoryUsage.php:148:            ->join('treatments', 'treatments.id = inventory_usage.treatment_id', 'left')
.\app\Controllers\Prescription.php:159:                ->join('patients', 'patients.id = prescriptions.patient_id', 'left');
.\app\Controllers\Treatment.php:35:                ->join('patients', 'patients.id = treatments.patient_id', 'left');
.\app\Controllers\Treatment.php:159:                ->join('patients', 'patients.id = treatments.patient_id', 'left');
.\app\Models\UserRoleModel.php:77:                     ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserRoleModel.php:179:                    ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserRoleModel.php:199:                    ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserRoleModel.php:217:                     ->join('users u', 'u.id = ur.user_id')
.\app\Models\UserRoleModel.php:257:                 ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserRoleModel.php:258:                 ->join('users u', 'u.id = ur.assigned_by')
.\app\Models\UserRoleModel.php:274:                   ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserModel.php:316:                 ->join('user_roles ur', 'ur.user_id = u.id')
.\app\Models\UserModel.php:317:                 ->join('roles r', 'r.id = ur.role_id')
.\app\Models\UserModel.php:335:                    ->join('user_roles ur', 'ur.user_id = u.id')
.\app\Models\UserModel.php:336:                    ->join('roles r', 'r.id = ur.role_id')
.\app\Models\TreatmentModel.php:106:                    ->join('patients', 'patients.id = treatments.patient_id')
.\app\Models\TreatmentModel.php:114:                    ->join('patients', 'patients.id = treatments.patient_id')
.\app\Models\TreatmentModel.php:229:                    ->join('patients', 'patients.id = treatments.patient_id')
.\app\Models\RoleModel.php:110:                         ->join('permissions p', 'p.id = rp.permission_id')
.\app\Models\RoleModel.php:201:                         ->join('permissions p', 'p.id = rp.permission_id')
.\app\Models\RoleModel.php:225:                    ->join('permissions p', 'p.id = rp.permission_id')
.\app\Models\RoleModel.php:244:                 ->join('users u', 'u.id = ur.user_id')
.\app\Models\PrescriptionModel.php:101:                    ->join('patients', 'patients.id = prescriptions.patient_id')
.\app\Models\PrescriptionModel.php:109:                    ->join('patients', 'patients.id = prescriptions.patient_id')
.\app\Models\PrescriptionModel.php:181:                    ->join('patients', 'patients.id = prescriptions.patient_id')
.\app\Models\PatientModel.php:121:            ->join('examinations', 'examinations.patient_id = patients.id', 'left')
.\app\Models\IonAuthModel.php:1439:				$builder->join(
.\app\Models\IonAuthModel.php:1464:				$builder->join($this->tables['groups'], $this->tables['users_groups'] . '.' . $this->join['groups'] . ' = ' . $this->tables['groups'] . '.id', 'inner');
.\app\Models\IonAuthModel.php:1581:					   ->join($this->tables['groups'], $this->tables['users_groups'] . '.' . $this->join['groups'] . '=' . $this->tables['groups'] . '.id')
.\app\Models\FinanceModel.php:124:            ->join('patients', 'patients.id = finances.patient_id')
.\app\Models\FinanceModel.php:217:            ->join('patients', 'patients.id = finances.patient_id')
.\app\Models\ExaminationModel.php:86:            ->join('patients', 'patients.id = examinations.patient_id')
.\app\Models\ExaminationModel.php:102:                ->join('patients', 'patients.id = examinations.patient_id')
.\app\Models\ExaminationModel.php:115:            ->join('patients', 'patients.id = examinations.patient_id')
.\app\Models\ActivityLogModel.php:80:            ->join('users u', 'u.id = al.user_id', 'left')
.\app\Models\ActivityLogModel.php:345:            ->join('users u', 'u.id = al.user_id', 'left')
.\app\Models\ActivityLogModel.php:361:            ->join('users u', 'u.id = al.user_id', 'left')
.\app\Models\AppointmentModel.php:87:            ->join('patients', 'patients.id = appointments.patient_id')
.\app\Models\AppointmentModel.php:95:            ->join('patients', 'patients.id = appointments.patient_id')
.\app\Models\AppointmentModel.php:104:            ->join('patients', 'patients.id = appointments.patient_id');
.\app\Models\AppointmentModel.php:132:            ->join('patients', 'patients.id = appointments.patient_id');
.\app\Models\AppointmentModel.php:167:                ->join('patients', 'patients.id = appointments.patient_id')
.\app\Models\AppointmentModel.php:183:            ->join('patients', 'patients.id = appointments.patient_id')
```
- `->where(` without `patient_id`/`user_id` on the same line (line-level heuristic)  
```
.\tests\database\ExampleDatabaseTest.php:42:        $result = $model->builder()->where('id', $object->id)->get()->getResult();
.\app\Libraries\IonAuth.php:118:		$user = $this->where($this->ionAuthModel->identityColumn, $identity)
.\app\Libraries\IonAuth.php:119:					 ->where('active', 1)
.\app\Libraries\IonAuth.php:314:		$user = $this->where($this->ionAuthModel->identityColumn, $identity)
.\app\Services\PermissionService.php:67:                    ->where('up.permission_id', $permission['id'])
.\app\Services\PermissionService.php:68:                    ->where('up.granted', 1)
.\app\Services\PermissionService.php:69:                    ->where('(up.expires_at IS NULL OR up.expires_at > NOW())')
.\app\Services\PermissionService.php:121:                         ->where('up.granted', 1)
.\app\Services\PermissionService.php:122:                         ->where('(up.expires_at IS NULL OR up.expires_at > NOW())')
.\app\Services\PermissionService.php:245:                       ->where('permission_id', $permissionId)
.\app\Services\PermissionService.php:253:                     ->where('permission_id', $permissionId)
.\app\Services\PermissionService.php:284:                       ->where('permission_id', $permissionId)
.\app\Services\PermissionService.php:292:                     ->where('permission_id', $permissionId)
.\app\Controllers\Appointment.php:153:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Appointment.php:272:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Auth.php:320:			$identity = $this->ionAuth->where($identityColumn, $this->request->getPost('identity'))->users()->row();
.\app\Controllers\Examination.php:54:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Examination.php:159:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\InventoryUsage.php:59:            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:60:            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:150:            ->where('inventory_usage.id', $id)
.\app\Controllers\InventoryUsage.php:176:            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:177:            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
.\app\Controllers\Doctor.php:257:        return $this->roleModel->where('is_medical', 1)
.\app\Controllers\Doctor.php:258:                              ->where('is_active', 1)
.\app\Controllers\Doctor.php:274:                 ->where('r.is_medical', 1)
.\app\Controllers\Doctor.php:275:                 ->where('ur.is_active', 1)
.\app\Controllers\Doctor.php:276:                 ->where('u.active', 1)
.\app\Controllers\Doctor.php:293:                    ->where('u.id', $id)
.\app\Controllers\Doctor.php:294:                    ->where('r.is_medical', 1)
.\app\Controllers\Doctor.php:295:                    ->where('ur.is_active', 1)
.\app\Models\InventoryModel.php:116:        return $this->where('quantity <=', 'min_quantity')
.\app\Models\InventoryModel.php:117:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:124:        return $this->where('quantity', 0)
.\app\Models\InventoryModel.php:125:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:132:        return $this->where('expiry_date <', date('Y-m-d'))
.\app\Models\InventoryModel.php:133:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:141:        return $this->where('expiry_date <=', $expiryDate)
.\app\Models\InventoryModel.php:142:                    ->where('expiry_date >=', date('Y-m-d'))
.\app\Models\InventoryModel.php:143:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:152:            'active_items' => $this->where('status', 'active')->countAllResults(),
.\app\Models\InventoryModel.php:153:            'low_stock_items' => $this->where('quantity <=', 'min_quantity')->where('status', 'active')->countAllResults(),
.\app\Models\InventoryModel.php:154:            'out_of_stock_items' => $this->where('quantity', 0)->where('status', 'active')->countAllResults(),
.\app\Models\InventoryModel.php:155:            'expired_items' => $this->where('expiry_date <', date('Y-m-d'))->where('status', 'active')->countAllResults(),
.\app\Models\InventoryModel.php:156:            'total_value' => $this->selectSum('quantity * unit_price')->where('status', 'active')->first()['quantity * unit_price'] ?? 0,
.\app\Models\InventoryModel.php:163:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:172:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:173:                    ->where('supplier IS NOT NULL')
.\app\Models\InventoryModel.php:194:        return $this->where('category', $category)
.\app\Models\InventoryModel.php:195:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:202:        return $this->where('location', $location)
.\app\Models\InventoryModel.php:203:                    ->where('status', 'active')
.\app\Models\InventoryModel.php:236:            'value' => $this->selectSum('quantity * unit_price')->where('status', 'active')->first()['quantity * unit_price'] ?? 0
.\app\Models\InventoryModel.php:244:        return $this->where('status', 'active')
.\app\Models\InventoryModel.php:255:        $lowStock = $this->where('quantity <=', 'min_quantity')
.\app\Models\InventoryModel.php:256:                         ->where('status', 'active')
.\app\Models\InventoryModel.php:270:        $expired = $this->where('expiry_date <', date('Y-m-d'))
.\app\Models\InventoryModel.php:271:                        ->where('status', 'active')
.\app\Models\InventoryModel.php:284:        $expiringSoon = $this->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))
.\app\Models\InventoryModel.php:285:                             ->where('expiry_date >=', date('Y-m-d'))
.\app\Models\InventoryModel.php:286:                             ->where('status', 'active')
.\app\Controllers\Inventory.php:49:                    ->where('category IS NOT NULL')
.\app\Controllers\Inventory.php:55:                $data['low_stock_items'] = $this->inventoryModel->where('quantity <=', 'min_quantity')->countAllResults();
.\app\Controllers\Inventory.php:56:                $data['out_of_stock_items'] = $this->inventoryModel->where('quantity', 0)->countAllResults();
.\app\Controllers\Inventory.php:330:            'items' => $this->inventoryModel->where('quantity <=', 'min_quantity')->findAll(),
.\app\Controllers\Inventory.php:340:            'items' => $this->inventoryModel->where('expiry_date <', date('Y-m-d'))->findAll(),
.\app\Controllers\Inventory.php:348:        $totalValueData = $this->inventoryModel->select('SUM(quantity * unit_price) as total_value')->where('status', 'active')->first();
.\app\Controllers\Inventory.php:353:            'active_items' => $this->inventoryModel->where('status', 'active')->countAllResults(),
.\app\Controllers\Inventory.php:354:            'low_stock_items' => $this->inventoryModel->where('quantity <=', 'min_quantity')->where('status', 'active')->countAllResults(),
.\app\Controllers\Inventory.php:355:            'out_of_stock_items' => $this->inventoryModel->where('quantity', 0)->where('status', 'active')->countAllResults(),
.\app\Controllers\Inventory.php:356:            'expired_items' => $this->inventoryModel->where('expiry_date <', date('Y-m-d'))->where('status', 'active')->countAllResults(),
.\app\Controllers\Inventory.php:388:        $inventoryItems = $this->inventoryModel->where('status', 'active')
.\app\Controllers\Inventory.php:389:            ->where('quantity >', 0)
.\app\Controllers\Inventory.php:401:        $activeTreatments = $treatmentModel->where('status', 'active')->findAll();
.\app\Controllers\Inventory.php:404:        $completedTreatments = $treatmentModel->where('status', 'completed')->findAll();
.\app\Controllers\Inventory.php:584:            ->where('inventory_usage.id', $id)
.\app\Controllers\Inventory.php:607:            ->where('inventory_usage.id', $id)
.\app\Controllers\Inventory.php:1027:            $totalRecords = $this->db->table('inventory')->where('quantity <=', 'min_quantity')->countAllResults();
.\app\Controllers\Inventory.php:1040:            $query = $this->db->table('inventory')->where('quantity <=', 'min_quantity');
.\app\Controllers\Api\Search.php:43:                ->where('status', 'active')
.\app\Controllers\Api\Search.php:57:                ->where('status', 'active')
.\app\Controllers\Api\Search.php:87:        $builder = $this->userModel->where('status', 'active');
.\app\Controllers\Api\Search.php:90:            $builder->where('role', $role);
.\app\Controllers\Api\Search.php:139:            ->where('examinations.status', $status);
.\app\Controllers\Api\Search.php:189:            $builder->where('treatments.status', $status);
.\app\Controllers\Api\Search.php:235:        $builder = $this->inventoryModel->where('quantity >', 0);
.\app\Controllers\Api\Search.php:238:            $builder->where('category', $category);
.\app\Controllers\Api\Search.php:419:        $builder = $this->roleModel->where('is_active', 1);
.\app\Models\FinanceModel.php:132:        return $this->where('examination_id', $examinationId)
.\app\Models\FinanceModel.php:143:                $builder->where('created_at >=', $startDate);
.\app\Models\FinanceModel.php:146:                $builder->where('created_at <=', $endDate);
.\app\Models\FinanceModel.php:151:            $totalRevenue = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0;
.\app\Models\FinanceModel.php:152:            $totalInvoices = $builder->selectSum($sumField, 'total_amount')->where('transaction_type', 'invoice')->get()->getRow()->total_amount ?? 0;
.\app\Models\FinanceModel.php:153:            $pendingPayments = $builder->selectSum($sumField, 'total_amount')->where('payment_status', 'pending')->get()->getRow()->total_amount ?? 0;
.\app\Models\FinanceModel.php:154:            $overduePayments = $builder->selectSum($sumField, 'total_amount')->where('payment_status', 'overdue')->get()->getRow()->total_amount ?? 0;
.\app\Models\FinanceModel.php:180:            $builder->where('created_at >=', $startDate);
.\app\Models\FinanceModel.php:183:            $builder->where('created_at <=', $endDate);
.\app\Models\FinanceModel.php:189:            ->where('transaction_type', 'payment')
.\app\Models\FinanceModel.php:203:                ->where('YEAR(created_at)', $year)
.\app\Models\FinanceModel.php:204:                ->where('transaction_type', 'payment')
.\app\Models\FinanceModel.php:218:            ->where('finances.payment_status', 'overdue')
.\app\Models\FinanceModel.php:219:            ->where('finances.due_date <', date('Y-m-d'))
.\app\Models\FinanceModel.php:229:            $builder->where('created_at >=', $startDate);
.\app\Models\FinanceModel.php:232:            $builder->where('created_at <=', $endDate);
.\app\Models\FinanceModel.php:238:            ->where('transaction_type', 'payment')
.\app\Controllers\Users.php:159:        $role = $roleModel->where('id', $roleId)->where('is_active', 1)->first();
.\app\Controllers\Users.php:309:        $role = $roleModel->where('id', $roleId)->where('is_active', 1)->first();
.\app\Models\ExaminationModel.php:87:            ->where('examinations.id', $examinationId)
.\app\Models\ExaminationModel.php:116:            ->where('examination_date >=', $startDate)
.\app\Models\ExaminationModel.php:117:            ->where('examination_date <=', $endDate)
.\app\Models\ExaminationModel.php:129:                'pending_examinations' => $builder->where('status', 'pending')->countAllResults(false),
.\app\Models\ExaminationModel.php:130:                'completed_examinations' => $builder->where('status', 'completed')->countAllResults(false),
.\app\Models\ExaminationModel.php:131:                'today_examinations' => $builder->where('status', 'completed')->where('DATE(examination_date)', date('Y-m-d'))->countAllResults(false),
.\app\Models\ExaminationModel.php:132:                'emergency_examinations' => $builder->where('examination_type', 'emergency')->countAllResults(false)
.\app\Controllers\Finance.php:45:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Finance.php:46:            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
.\app\Controllers\Finance.php:147:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Finance.php:148:            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
.\app\Controllers\Finance.php:568:                $query->where('finances.created_at >=', $startDate . ' 00:00:00')
.\app\Controllers\Finance.php:569:                    ->where('finances.created_at <=', $endDate . ' 23:59:59');
.\app\Controllers\Finance.php:573:                $query->where('finances.payment_status', $status);
.\app\Controllers\Api\Appointment.php:16:                            ->where('appointment_date >=', date('Y-m-d'))
.\app\Models\UserRoleModel.php:62:            $builder->where('is_active', 1);
.\app\Models\UserRoleModel.php:81:            $builder->where('ur.is_active', 1);
.\app\Models\UserRoleModel.php:96:                        ->where('role_id', $roleId)
.\app\Models\UserRoleModel.php:125:                   ->where('role_id', $roleId)
.\app\Models\UserRoleModel.php:135:                   ->where('role_id', $roleId)
.\app\Models\UserRoleModel.php:181:                    ->where('r.slug', $roleSlug)
.\app\Models\UserRoleModel.php:182:                    ->where('ur.is_active', 1)
.\app\Models\UserRoleModel.php:202:                    ->where('ur.is_active', 1)
.\app\Models\UserRoleModel.php:218:                     ->where('ur.role_id', $roleId);
.\app\Models\UserRoleModel.php:221:            $builder->where('ur.is_active', 1);
.\app\Models\UserRoleModel.php:234:        return $this->where('expires_at <', date('Y-m-d H:i:s'))
.\app\Models\UserRoleModel.php:235:                   ->where('is_active', 1)
.\app\Models\UserRoleModel.php:244:        return $this->where('expires_at <', date('Y-m-d H:i:s'))
.\app\Models\UserRoleModel.php:245:                   ->where('is_active', 1)
.\app\Models\UserRoleModel.php:275:                   ->where('ur.is_active', 1)
.\app\Controllers\Treatment.php:50:                $query->where('treatments.status', $statusFilter);
.\app\Controllers\Treatment.php:54:                $query->where('treatments.treatment_type', $typeFilter);
.\app\Controllers\Treatment.php:66:                'active_treatments' => $this->treatmentModel->where('status', 'active')->countAllResults(),
.\app\Controllers\Treatment.php:67:                'completed_treatments' => $this->treatmentModel->where('status', 'completed')->countAllResults(),
.\app\Controllers\Treatment.php:175:                $query->where('treatments.status', $statusFilter);
.\app\Controllers\Treatment.php:180:                $query->where('treatments.treatment_type', $typeFilter);
.\app\Controllers\Treatment.php:470:            'active' => $this->treatmentModel->where('status', 'active')->countAllResults(),
.\app\Controllers\Treatment.php:471:            'completed' => $this->treatmentModel->where('status', 'completed')->countAllResults(),
.\app\Controllers\Treatment.php:472:            'cancelled' => $this->treatmentModel->where('status', 'cancelled')->countAllResults(),
.\app\Controllers\Treatment.php:473:            'on_hold' => $this->treatmentModel->where('status', 'on_hold')->countAllResults(),
.\app\Controllers\ActivityLog.php:83:                $builder->where('al.entity_type', $entityType);
.\app\Controllers\ActivityLog.php:86:                $builder->where('al.action', $action);
.\app\Controllers\ActivityLog.php:109:                $builder->where('al.entity_type', $entityType);
.\app\Controllers\ActivityLog.php:112:                $builder->where('al.action', $action);
.\app\Models\UserModel.php:137:        return $this->where('active', 1)
.\app\Models\UserModel.php:144:        return $this->where('role', $role)
.\app\Models\UserModel.php:145:                    ->where('active', 1)
.\app\Models\UserModel.php:152:        return $this->where('department', $department)
.\app\Models\UserModel.php:153:                    ->where('active', 1)
.\app\Models\UserModel.php:162:            'active' => $this->where('active', 1)->countAllResults(),
.\app\Models\UserModel.php:163:            'inactive' => $this->where('active', 0)->countAllResults(),
.\app\Models\UserModel.php:178:                    ->where('department IS NOT NULL')
.\app\Models\UserModel.php:208:        return $this->where('last_login <', $cutoffDate)
.\app\Models\UserModel.php:210:                    ->where('status', 'active')
.\app\Models\UserModel.php:222:        $user = $this->where('username', $username)
.\app\Models\UserModel.php:224:                     ->where('status', 'active')
.\app\Models\UserModel.php:318:                 ->where('r.is_medical', 1)
.\app\Models\UserModel.php:319:                 ->where('ur.is_active', 1)
.\app\Models\UserModel.php:320:                 ->where('u.active', 1)
.\app\Models\UserModel.php:337:                    ->where('u.id', $id)
.\app\Models\UserModel.php:338:                    ->where('r.is_medical', 1)
.\app\Models\UserModel.php:339:                    ->where('ur.is_active', 1)
.\app\Models\AppointmentModel.php:88:            ->where('appointments.id', $appointmentId)
.\app\Models\AppointmentModel.php:96:            ->where('DATE(appointment_date)', $date)
.\app\Models\AppointmentModel.php:120:            $query->where('appointments.status', $status);
.\app\Models\AppointmentModel.php:148:            $query->where('appointments.status', $status);
.\app\Models\AppointmentModel.php:168:                ->where('appointments.appointment_date >=', date('Y-m-d'))
.\app\Models\AppointmentModel.php:169:                ->where('appointments.status', 'scheduled')
.\app\Models\AppointmentModel.php:184:            ->where('appointment_date >=', $startDate)
.\app\Models\AppointmentModel.php:185:            ->where('appointment_date <=', $endDate)
.\app\Models\AppointmentModel.php:198:                'today_appointments' => $builder->where('DATE(appointment_date)', date('Y-m-d'))->countAllResults(false),
.\app\Models\AppointmentModel.php:199:                'upcoming_appointments' => $builder->where('appointment_date >=', date('Y-m-d'))->where('status', 'scheduled')->countAllResults(false),
.\app\Models\AppointmentModel.php:200:                'completed_appointments' => $builder->where('status', 'completed')->countAllResults(false),
.\app\Models\AppointmentModel.php:201:                'cancelled_appointments' => $builder->where('status', 'cancelled')->countAllResults(false)
.\app\Models\AppointmentModel.php:218:        $builder->where('DATE(appointment_date)', $date);
.\app\Models\AppointmentModel.php:219:        $builder->where('status !=', 'cancelled');
.\app\Models\AppointmentModel.php:222:            $builder->where('id !=', $excludeId);
.\app\Models\ActivityLogModel.php:346:            ->where('al.entity_type', $entityType)
.\app\Models\ActivityLogModel.php:347:            ->where('al.entity_id', $entityId)
.\app\Controllers\Reports.php:215:                'new_patients' => $this->patientModel->where('created_at >=', $startDate)->countAll(),
.\app\Controllers\Reports.php:217:                'examinations_this_period' => $this->examinationModel->where('examination_date >=', $startDate)->countAll(),
.\app\Controllers\Reports.php:219:                'appointments_this_period' => $this->appointmentModel->where('appointment_date >=', $startDate)->countAll(),
.\app\Controllers\Reports.php:220:                'total_revenue' => $this->financeModel->selectSum('total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0,
.\app\Controllers\Reports.php:221:                'revenue_this_period' => $this->financeModel->selectSum('total_amount')->where('transaction_type', 'payment')->where('created_at >=', $startDate)->get()->getRow()->total_amount ?? 0,
.\app\Controllers\Reports.php:235:            'patients' => $this->patientModel->where('created_at >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:245:            'examinations' => $this->examinationModel->where('examination_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:255:            'appointments' => $this->appointmentModel->where('appointment_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:265:            'transactions' => $this->financeModel->where('created_at >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:275:            'treatments' => $this->treatmentModel->where('start_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:288:            $count = $this->patientModel->where('MONTH(created_at)', date('n', strtotime($date)))
.\app\Controllers\Reports.php:289:                                      ->where('YEAR(created_at)', date('Y', strtotime($date)))
.\app\Controllers\Reports.php:305:                                        ->where('transaction_type', 'payment')
.\app\Controllers\Reports.php:306:                                        ->where('MONTH(created_at)', date('n', strtotime($date)))
.\app\Controllers\Reports.php:307:                                        ->where('YEAR(created_at)', date('Y', strtotime($date)))
.\app\Controllers\Reports.php:385:        $new = $this->patientModel->where('created_at >=', $startDate)->countAll();
.\app\Controllers\Reports.php:386:        $returning = $this->patientModel->where('created_at <', $startDate)->countAll();
.\app\Controllers\Reports.php:399:            $count = $this->examinationModel->where('MONTH(examination_date)', date('n', strtotime($date)))
.\app\Controllers\Reports.php:400:                                           ->where('YEAR(examination_date)', date('Y', strtotime($date)))
.\app\Controllers\Reports.php:428:            $count = $this->appointmentModel->where('MONTH(appointment_date)', date('n', strtotime($date)))
.\app\Controllers\Reports.php:429:                                           ->where('YEAR(appointment_date)', date('Y', strtotime($date)))
.\app\Controllers\Reports.php:448:        return $this->financeModel->where('payment_status', 'pending')->findAll();
.\app\Controllers\Reports.php:463:            $count = $this->treatmentModel->where('MONTH(start_date)', date('n', strtotime($date)))
.\app\Controllers\Reports.php:464:                                         ->where('YEAR(start_date)', date('Y', strtotime($date)))
.\app\Controllers\Prescription.php:32:                'active_prescriptions' => $this->prescriptionModel->where('status', 'active')->countAllResults(),
.\app\Controllers\Prescription.php:33:                'expired_prescriptions' => $this->prescriptionModel->where('status', 'expired')->countAllResults(),
.\app\Controllers\Prescription.php:477:            'active' => $this->prescriptionModel->where('status', 'active')->countAllResults(),
.\app\Controllers\Prescription.php:478:            'expired' => $this->prescriptionModel->where('status', 'expired')->countAllResults(),
.\app\Controllers\Prescription.php:479:            'cancelled' => $this->prescriptionModel->where('status', 'cancelled')->countAllResults(),
.\app\Controllers\Patient.php:385:            $existingPatient = $this->patientModel->where('email', $email)->where('id !=', $id)->first();
.\app\Controllers\Patient.php:603:                ->where('deleted_at', null)
.\app\Controllers\Patient.php:638:            $activePatients = $this->patientModel->where('status', 'active')->countAllResults(false);
.\app\Controllers\Patient.php:642:            $newPatients = $this->patientModel->where('DATE_FORMAT(created_at, "%Y-%m")', $currentMonth)->countAllResults(false);
.\app\Models\TreatmentModel.php:115:                    ->where('treatments.id', $id)
.\app\Models\TreatmentModel.php:128:        return $this->where('status', 'active')
.\app\Models\TreatmentModel.php:135:        $query = $this->where('status', 'completed');
.\app\Models\TreatmentModel.php:138:            $query->where('completion_date >=', $startDate);
.\app\Models\TreatmentModel.php:142:            $query->where('completion_date <=', $endDate);
.\app\Models\TreatmentModel.php:153:                'active' => $this->where('status', 'active')->countAllResults(),
.\app\Models\TreatmentModel.php:154:                'completed' => $this->where('status', 'completed')->countAllResults(),
.\app\Models\TreatmentModel.php:155:                'cancelled' => $this->where('status', 'cancelled')->countAllResults(),
.\app\Models\TreatmentModel.php:156:                'on_hold' => $this->where('status', 'on_hold')->countAllResults(),
.\app\Models\TreatmentModel.php:157:                'active_treatments' => $this->where('status', 'active')->countAllResults(),
.\app\Models\TreatmentModel.php:158:                'completed_treatments' => $this->where('status', 'completed')->countAllResults(),
.\app\Models\TreatmentModel.php:195:                    ->where('YEAR(start_date)', $year)
.\app\Models\TreatmentModel.php:204:                     ->where('status', 'completed');
.\app\Models\TreatmentModel.php:207:            $query->where('completion_date >=', $startDate);
.\app\Models\TreatmentModel.php:211:            $query->where('completion_date <=', $endDate);
.\app\Models\TreatmentModel.php:220:        return $this->where('status', 'active')
.\app\Models\TreatmentModel.php:221:                    ->where('start_date <', $today)
.\app\Models\TreatmentModel.php:222:                    ->where('estimated_duration >', 0)
.\app\Models\TreatmentModel.php:242:        return $this->where('examination_id', $examinationId)
.\app\Models\PatientModel.php:122:            ->where('patients.deleted_at', null)
.\app\Models\IonAuthModel.php:379:		$user = $this->where('activation_selector', $token->selector)->users()->row();
.\app\Models\IonAuthModel.php:505:		return $this->db->table($this->tables['users'])->where($this->identityColumn, $identity)->update($data);
.\app\Models\IonAuthModel.php:527:		return $this->db->table($this->tables['users'])->where($this->identityColumn, $identity)->update($data);
.\app\Models\IonAuthModel.php:584:					   ->where($this->identityColumn, $identity)
.\app\Models\IonAuthModel.php:639:			->where('username', $username)
.\app\Models\IonAuthModel.php:664:						->where('email', $email)
.\app\Models\IonAuthModel.php:687:		return $builder->where($this->identityColumn, $identity)
.\app\Models\IonAuthModel.php:708:						 ->where($this->identityColumn, $identity)
.\app\Models\IonAuthModel.php:779:		$user = $this->where('forgotten_password_selector', $token->selector)->users()->row();
.\app\Models\IonAuthModel.php:823:		$query = $this->db->table($this->tables['groups'])->where(['name' => $this->config->defaultGroup], 1)->get()->getRow();
.\app\Models\IonAuthModel.php:909:						  ->where($this->identityColumn, $identity)
.\app\Models\IonAuthModel.php:1000:								  ->where([
.\app\Models\IonAuthModel.php:1070:			$builder->where('login', $identity);
.\app\Models\IonAuthModel.php:1077:				$builder->where('ip_address', $ipAddress);
.\app\Models\IonAuthModel.php:1079:			$builder->where('time >', time() - $this->config->lockoutTime, false);
.\app\Models\IonAuthModel.php:1102:			$builder->where('login', $identity);
.\app\Models\IonAuthModel.php:1109:				$builder->where('ip_address', $ipAddress);
.\app\Models\IonAuthModel.php:1136:			$builder->where('login', $identity);
.\app\Models\IonAuthModel.php:1197:			$builder->where('login', $identity);
.\app\Models\IonAuthModel.php:1204:				$builder->where('ip_address', $ipAddress);
.\app\Models\IonAuthModel.php:1480:				$builder->where($where);
.\app\Models\IonAuthModel.php:1543:			$this->where('1', '0');
.\app\Models\IonAuthModel.php:1549:		$this->where($this->tables['users'] . '.id', $id);
.\app\Models\IonAuthModel.php:1575:			return $this->db->table($this->tables['users_groups'])->where('1', '0')->get();
.\app\Models\IonAuthModel.php:1580:					   ->where($this->tables['users_groups'] . '.' . $this->join['users'], $id)
.\app\Models\IonAuthModel.php:1779:				$builder->where($where);
.\app\Models\IonAuthModel.php:1823:			$this->where($this->tables['groups'] . '.id', $id);
.\app\Models\IonAuthModel.php:2103:						  ->where('remember_selector', $token->selector)
.\app\Models\IonAuthModel.php:2104:						  ->where('active', 1)
.\app\Models\IonAuthModel.php:2162:		$existingGroup = $this->db->table($this->tables['groups'])->where(['name' => $groupName])->countAllResults();
.\app\Models\OdontogramModel.php:80:        return $this->where('examination_id', $examinationId)
.\app\Models\OdontogramModel.php:88:            ->where('tooth_number', $toothNumber)
.\app\Models\OdontogramModel.php:115:            ->where('tooth_number', $toothNumber)
.\app\Models\OdontogramModel.php:125:            ->where('condition_type !=', 'healthy')
.\app\Models\OdontogramModel.php:138:            'cavities' => $builder->where('patient_id', $patientId)->where('condition_type', 'cavity')->countAllResults(),
.\app\Models\OdontogramModel.php:139:            'fillings' => $builder->where('patient_id', $patientId)->where('condition_type', 'filling')->countAllResults(),
.\app\Models\OdontogramModel.php:140:            'crowns' => $builder->where('patient_id', $patientId)->where('condition_type', 'crown')->countAllResults(),
.\app\Models\OdontogramModel.php:141:            'extracted' => $builder->where('patient_id', $patientId)->where('condition_type', 'extracted')->countAllResults()
.\app\Models\InventoryUsageModel.php:87:        return $this->where('treatment_id', $treatmentId)
.\app\Models\InventoryUsageModel.php:94:        return $this->where('usage_date >=', $startDate)
.\app\Models\InventoryUsageModel.php:95:            ->where('usage_date <=', $endDate)
.\app\Models\InventoryUsageModel.php:102:        return $this->where('JSON_CONTAINS(items_used, \'{"item_id":' . $itemId . '}\')')
.\app\Models\InventoryUsageModel.php:112:            $query->where('usage_date >=', $startDate);
.\app\Models\InventoryUsageModel.php:116:            $query->where('usage_date <=', $endDate);
.\app\Models\InventoryUsageModel.php:137:            $query->where('usage_date >=', $startDate);
.\app\Models\InventoryUsageModel.php:141:            $query->where('usage_date <=', $endDate);
.\app\Models\PrescriptionModel.php:110:                    ->where('prescriptions.id', $id)
.\app\Models\PrescriptionModel.php:123:        return $this->where('status', 'active')
.\app\Models\PrescriptionModel.php:124:                    ->where('expiry_date >', date('Y-m-d'))
.\app\Models\PrescriptionModel.php:131:        return $this->where('expiry_date <', date('Y-m-d'))
.\app\Models\PrescriptionModel.php:132:                    ->where('status', 'active')
.\app\Models\PrescriptionModel.php:140:        return $this->where('expiry_date <=', $expiryDate)
.\app\Models\PrescriptionModel.php:141:                    ->where('expiry_date >=', date('Y-m-d'))
.\app\Models\PrescriptionModel.php:142:                    ->where('status', 'active')
.\app\Models\PrescriptionModel.php:151:            'active' => $this->where('status', 'active')->countAllResults(),
.\app\Models\PrescriptionModel.php:152:            'expired' => $this->where('status', 'expired')->countAllResults(),
.\app\Models\PrescriptionModel.php:153:            'cancelled' => $this->where('status', 'cancelled')->countAllResults(),
.\app\Models\PrescriptionModel.php:172:                    ->where('YEAR(prescribed_date)', $year)
.\app\Models\PrescriptionModel.php:194:        return $this->where('prescribed_date >=', $startDate)
.\app\Models\PrescriptionModel.php:195:                    ->where('prescribed_date <=', $endDate)
.\app\Models\PrescriptionModel.php:203:        return $this->where('expiry_date <', $today)
.\app\Models\PrescriptionModel.php:204:                    ->where('status', 'active')
.\app\Models\RoleModel.php:64:        return $this->where('slug', $slug)->first();
.\app\Models\RoleModel.php:72:        return $this->where('is_active', 1)
.\app\Models\RoleModel.php:82:        return $this->where('is_system', 1)
.\app\Models\RoleModel.php:92:        return $this->where('is_system', 0)
.\app\Models\RoleModel.php:111:                         ->where('rp.role_id', $roleId)
.\app\Models\RoleModel.php:112:                         ->where('rp.granted', 1)
.\app\Models\RoleModel.php:129:                    ->where('role_id', $roleId)
.\app\Models\RoleModel.php:130:                    ->where('permission_id', $permissionId)
.\app\Models\RoleModel.php:136:                     ->where('role_id', $roleId)
.\app\Models\RoleModel.php:137:                     ->where('permission_id', $permissionId)
.\app\Models\RoleModel.php:157:                 ->where('role_id', $roleId)
.\app\Models\RoleModel.php:158:                 ->where('permission_id', $permissionId)
.\app\Models\RoleModel.php:171:           ->where('role_id', $roleId)
.\app\Models\RoleModel.php:202:                         ->where('rp.role_id', $roleId)
.\app\Models\RoleModel.php:203:                         ->where('rp.granted', 1)
.\app\Models\RoleModel.php:226:                    ->where('rp.role_id', $roleId)
.\app\Models\RoleModel.php:227:                    ->where('p.module', $module)
.\app\Models\RoleModel.php:228:                    ->where('p.action', $action)
.\app\Models\RoleModel.php:229:                    ->where('rp.granted', 1)
.\app\Models\RoleModel.php:245:                 ->where('ur.role_id', $roleId)
.\app\Models\RoleModel.php:246:                 ->where('ur.is_active', 1)
.\app\Models\RoleModel.php:258:                 ->where('role_id', $roleId)
.\app\Models\RoleModel.php:259:                 ->where('is_active', 1)
.\app\Models\SettingsModel.php:57:        $setting = $this->where('setting_key', $key)->first();
.\app\Models\SettingsModel.php:66:        $existing = $this->where('setting_key', $key)->first();
.\app\Models\PermissionModel.php:62:        return $this->where('module', $module)
.\app\Models\PermissionModel.php:72:        return $this->where('category', $category)
.\app\Models\PermissionModel.php:116:        return $this->where('module', $module)
.\app\Models\PermissionModel.php:117:                   ->where('action', $action)
.\app\Models\PermissionModel.php:126:        return $this->where('module', $module)
.\app\Models\PermissionModel.php:127:                   ->where('action', $action)
.\app\Models\PermissionModel.php:136:        return $this->where('is_system', 1)
.\app\Models\PermissionModel.php:147:        return $this->where('is_system', 0)
```
- `->like(`  
```
.\app\Controllers\Examination.php:361:                    ->like('examinations.examination_id', $searchValue)
.\app\Controllers\Api\Search.php:51:                    ->like('first_name', $query)
.\app\Controllers\Api\Search.php:101:                    ->like('first_name', $query)
.\app\Controllers\Api\Search.php:149:                    ->like('examinations.examination_id', $query)
.\app\Controllers\Api\Search.php:200:                    ->like('treatments.treatment_type', $query)
.\app\Controllers\Api\Search.php:249:                    ->like('name', $query)
.\app\Controllers\Api\Search.php:423:                    ->like('name', $query)
.\app\Controllers\Patient.php:148:                    ->like('first_name', $searchValue)
.\app\Controllers\Inventory.php:701:                    ->like('item_name', $searchValue)
.\app\Controllers\Inventory.php:912:                ->like('treatments.treatment_id', $searchValue)
.\app\Controllers\Inventory.php:1045:                    ->like('item_name', $searchValue)
.\app\Controllers\Finance.php:489:                    ->like($financeTableName . '.id', $searchValue)
.\app\Controllers\Treatment.php:40:                    ->like('patients.first_name', $search)
.\app\Controllers\Treatment.php:164:                    ->like('patients.first_name', $searchValue)
.\app\Models\AppointmentModel.php:109:                ->like('patients.first_name', $search)
.\app\Models\AppointmentModel.php:137:                ->like('patients.first_name', $search)
.\app\Controllers\Prescription.php:164:                    ->like('prescriptions.id', $searchValue)
.\app\Models\InventoryModel.php:182:                    ->like('item_name', $searchTerm)
.\app\Models\PatientModel.php:106:            ->like('first_name', $searchTerm)
.\app\Models\PrescriptionModel.php:183:                        ->like('prescriptions.medication_name', $searchTerm)
.\app\Models\TreatmentModel.php:231:                        ->like('treatments.description', $searchTerm)
.\app\Models\UserModel.php:187:                    ->like('first_name', $searchTerm)
```
- `findAll()/paginate()/getResult()`  
```
.\tests\database\ExampleDatabaseTest.php:22:        $objects = $model->findAll();
.\tests\database\ExampleDatabaseTest.php:42:        $result = $model->builder()->where('id', $object->id)->get()->getResult();
.\app\Controllers\Dashboard.php:196:            return $this->patientModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
.\app\Controllers\BaseController.php:117:        return $this->ionAuth->getUsersGroups($userId)->getResult();
.\app\Controllers\Auth.php:99:				$this->data['users'][$k]->groups = $this->ionAuth->getUsersGroups($user->id)->getResult();
.\app\Controllers\Auth.php:680:		$currentGroups = $this->ionAuth->getUsersGroups($id)->getResult();
.\app\Controllers\Appointment.php:153:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Appointment.php:272:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Prescription.php:229:            'patients' => $this->patientModel->findAll(),
.\app\Controllers\Prescription.php:338:            'patients' => $this->patientModel->findAll(),
.\app\Controllers\Users.php:49:                        $user->display_roles = $this->ionAuth->getUsersGroups($user->id)->getResult();
.\app\Controllers\Users.php:54:                    $user->display_roles = $this->ionAuth->getUsersGroups($user->id)->getResult();
.\app\Controllers\Users.php:215:                $user->display_roles = $this->ionAuth->getUsersGroups($id)->getResult();
.\app\Controllers\Users.php:218:            $user->display_roles = $this->ionAuth->getUsersGroups($id)->getResult();
.\app\Controllers\Patient.php:66:            $patients = $this->patientModel->findAll();
.\app\Controllers\Api\Finance.php:18:                            ->findAll(100);
.\app\Controllers\UserManagementController.php:429:        $users = $this->userModel->findAll();
.\app\Controllers\Api\Appointment.php:18:                            ->findAll(100);
.\app\Controllers\Api\Patient.php:18:                            ->findAll(100); // Limit to 100 for safety
.\app\Controllers\Api\Examination.php:17:                            ->findAll(100);
.\app\Controllers\InventoryUsage.php:36:                    ->findAll(),
.\app\Controllers\InventoryUsage.php:59:            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:60:            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:61:            'patients' => $this->patientModel->orderBy('first_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:176:            'inventory_items' => $this->inventoryModel->where('status', 'active')->orderBy('item_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:177:            'treatments' => $this->treatmentModel->where('status', 'active')->orderBy('treatment_name', 'ASC')->findAll(),
.\app\Controllers\InventoryUsage.php:178:            'patients' => $this->patientModel->orderBy('first_name', 'ASC')->findAll(),
.\app\Controllers\Treatment.php:57:            $treatments = $query->orderBy('treatments.created_at', 'DESC')->findAll();
.\app\Controllers\Treatment.php:238:            'patients' => $this->patientModel->findAll(),
.\app\Controllers\Treatment.php:261:                'patients' => $this->patientModel->findAll(),
.\app\Controllers\Treatment.php:329:            'patients' => $this->patientModel->findAll(),
.\app\Controllers\Treatment.php:359:                'patients' => $this->patientModel->findAll(),
.\app\Controllers\Finance.php:45:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Finance.php:46:            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
.\app\Controllers\Finance.php:147:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Finance.php:148:            'examinations' => $this->examinationModel->where('status', 'completed')->findAll(),
.\app\Controllers\Finance.php:581:            $data = $query->findAll();
.\app\Controllers\Doctor.php:260:                              ->findAll();
.\app\Controllers\RoleController.php:73:        $roles = $this->roleModel->findAll();
.\app\Controllers\DebugController.php:89:                $groups = $this->ionAuth->getUsersGroups($user->id)->getResult();
.\app\Controllers\DebugController.php:163:            $roles = $this->roleModel->findAll();
.\app\Controllers\DebugController.php:194:            $allPermissions = $this->permissionModel->findAll();
.\app\Controllers\DebugController.php:199:            $allUserRoles = $this->userRoleModel->findAll();
.\app\Controllers\Examination.php:54:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Examination.php:159:            'patients' => $this->patientModel->where('status', 'active')->findAll(),
.\app\Controllers\Reports.php:235:            'patients' => $this->patientModel->where('created_at >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:245:            'examinations' => $this->examinationModel->where('examination_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:255:            'appointments' => $this->appointmentModel->where('appointment_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:265:            'transactions' => $this->financeModel->where('created_at >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:275:            'treatments' => $this->treatmentModel->where('start_date >=', $startDate)->findAll(),
.\app\Controllers\Reports.php:321:                                       ->findAll();
.\app\Controllers\Reports.php:329:                                          ->findAll();
.\app\Controllers\Reports.php:336:            $patients = $this->patientModel->select('date_of_birth')->findAll();
.\app\Controllers\Reports.php:380:                                 ->findAll();
.\app\Controllers\Reports.php:420:                                     ->findAll();
.\app\Controllers\Reports.php:443:                                 ->findAll();
.\app\Controllers\Reports.php:448:        return $this->financeModel->where('payment_status', 'pending')->findAll();
.\app\Controllers\Reports.php:455:                                   ->findAll();
.\app\Controllers\Reports.php:478:                                   ->findAll();
.\app\Controllers\Inventory.php:44:                $inventory = $this->inventoryModel->findAll();
.\app\Controllers\Inventory.php:50:                    ->findAll();
.\app\Controllers\Inventory.php:330:            'items' => $this->inventoryModel->where('quantity <=', 'min_quantity')->findAll(),
.\app\Controllers\Inventory.php:340:            'items' => $this->inventoryModel->where('expiry_date <', date('Y-m-d'))->findAll(),
.\app\Controllers\Inventory.php:391:            ->findAll();
.\app\Controllers\Inventory.php:397:        $allTreatments = $treatmentModel->findAll();
.\app\Controllers\Inventory.php:401:        $activeTreatments = $treatmentModel->where('status', 'active')->findAll();
.\app\Controllers\Inventory.php:404:        $completedTreatments = $treatmentModel->where('status', 'completed')->findAll();
.\app\Controllers\Inventory.php:411:            ->findAll();
.\app\Controllers\Inventory.php:567:            ->findAll();
.\app\Controllers\Inventory.php:789:            $items = $this->inventoryModel->findAll();
.\app\Controllers\Inventory.php:820:            $items = $this->inventoryModel->findAll();
.\app\Controllers\Inventory.php:929:            ->findAll();
.\app\Services\PermissionSyncService.php:94:            $allPermissions = $this->permissionModel->findAll();
.\app\Services\PermissionSyncService.php:211:            $allPermissions = $this->permissionModel->findAll();
.\app\Helpers\auth_helper.php:51:        return $ionAuth->getUsersGroups()->getResult();
.\app\Helpers\auth_helper.php:94:            $userGroups = $ionAuth->getUsersGroups($user->id)->getResult();
.\app\Models\AppointmentModel.php:98:            ->findAll();
.\app\Models\AppointmentModel.php:126:            ->findAll();
.\app\Models\AppointmentModel.php:160:            ->findAll();
.\app\Models\AppointmentModel.php:173:                ->findAll();
.\app\Models\AppointmentModel.php:188:            ->findAll();
.\app\Models\ExaminationModel.php:95:            ->findAll();
.\app\Models\ExaminationModel.php:105:                ->findAll();
.\app\Models\ExaminationModel.php:119:            ->findAll();
.\app\Models\FinanceModel.php:127:            ->findAll();
.\app\Models\FinanceModel.php:134:            ->findAll();
.\app\Models\FinanceModel.php:207:                ->findAll();
.\app\Models\FinanceModel.php:221:            ->findAll();
.\app\Models\InventoryModel.php:111:                    ->findAll();
.\app\Models\InventoryModel.php:119:                    ->findAll();
.\app\Models\InventoryModel.php:127:                    ->findAll();
.\app\Models\InventoryModel.php:135:                    ->findAll();
.\app\Models\InventoryModel.php:145:                    ->findAll();
.\app\Models\InventoryModel.php:166:                    ->findAll();
.\app\Models\InventoryModel.php:176:                    ->findAll();
.\app\Models\InventoryModel.php:189:                    ->findAll();
.\app\Models\InventoryModel.php:197:                    ->findAll();
.\app\Models\InventoryModel.php:205:                    ->findAll();
.\app\Models\InventoryModel.php:247:                    ->findAll();
.\app\Models\InventoryModel.php:257:                         ->findAll();
.\app\Models\InventoryModel.php:272:                        ->findAll();
.\app\Models\InventoryModel.php:287:                             ->findAll();
.\app\Models\InventoryUsageModel.php:89:            ->findAll();
.\app\Models\InventoryUsageModel.php:97:            ->findAll();
.\app\Models\InventoryUsageModel.php:104:            ->findAll();
.\app\Models\InventoryUsageModel.php:129:            ->findAll();
.\app\Models\IonAuthModel.php:586:					   ->get()->getResult();
.\app\Models\IonAuthModel.php:1362:		return $this->response->getResult();
.\app\Models\IonAuthModel.php:1619:			$usersGroups = $this->getUsersGroups($id)->getResult();
.\app\Models\OdontogramModel.php:75:            ->findAll();
.\app\Models\OdontogramModel.php:82:            ->findAll();
.\app\Models\PermissionModel.php:64:                   ->findAll();
.\app\Models\PermissionModel.php:74:                   ->findAll();
.\app\Models\PermissionModel.php:84:                          ->findAll();
.\app\Models\PermissionModel.php:101:                          ->findAll();
.\app\Models\PermissionModel.php:139:                   ->findAll();
.\app\Models\PermissionModel.php:150:                   ->findAll();
.\app\Models\RoleModel.php:74:                   ->findAll();
.\app\Models\RoleModel.php:84:                   ->findAll();
.\app\Models\RoleModel.php:94:                   ->findAll();
.\app\Models\PatientModel.php:112:            ->findAll();
.\app\Models\PatientModel.php:124:            ->paginate(10);
.\app\Models\PrescriptionModel.php:103:                    ->findAll();
.\app\Models\PrescriptionModel.php:118:                    ->findAll();
.\app\Models\PrescriptionModel.php:126:                    ->findAll();
.\app\Models\PrescriptionModel.php:134:                    ->findAll();
.\app\Models\PrescriptionModel.php:144:                    ->findAll();
.\app\Models\PrescriptionModel.php:162:                    ->findAll();
.\app\Models\PrescriptionModel.php:175:                    ->findAll();
.\app\Models\PrescriptionModel.php:189:                    ->findAll();
.\app\Models\PrescriptionModel.php:197:                    ->findAll();
.\app\Models\PrescriptionModel.php:214:                    ->findAll();
.\app\Models\TreatmentModel.php:108:                    ->findAll();
.\app\Models\TreatmentModel.php:123:                    ->findAll();
.\app\Models\TreatmentModel.php:130:                    ->findAll();
.\app\Models\TreatmentModel.php:145:        return $query->orderBy('completion_date', 'DESC')->findAll();
.\app\Models\TreatmentModel.php:178:                    ->findAll();
.\app\Models\TreatmentModel.php:185:                    ->findAll();
.\app\Models\TreatmentModel.php:198:                    ->findAll();
.\app\Models\TreatmentModel.php:214:        return $query->groupBy('treatment_type')->findAll();
.\app\Models\TreatmentModel.php:223:                    ->findAll();
.\app\Models\TreatmentModel.php:237:                    ->findAll();
.\app\Models\TreatmentModel.php:244:                    ->findAll();
.\app\Models\UserModel.php:132:                    ->findAll();
.\app\Models\UserModel.php:139:                    ->findAll();
.\app\Models\UserModel.php:147:                    ->findAll();
.\app\Models\UserModel.php:155:                    ->findAll();
.\app\Models\UserModel.php:172:                    ->findAll();
.\app\Models\UserModel.php:181:                    ->findAll();
.\app\Models\UserModel.php:195:                    ->findAll();
.\app\Models\UserModel.php:202:                    ->findAll();
.\app\Models\UserModel.php:212:                    ->findAll();
.\app\Models\SettingsModel.php:105:        $settings = $this->findAll();
.\app\Models\UserRoleModel.php:65:        return $builder->findAll();
.\app\Models\UserRoleModel.php:236:                   ->findAll();
```

**Top 30 highest-risk results**
- #1 `app/Controllers/Api/Patient.php:18` `->findAll(100)`; tables: `patients`; risk: API list returns cross-clinic patients without tenant scoping.
- #2 `app/Controllers/Api/Appointment.php:18` `->findAll(100)`; tables: `appointments`; risk: API list exposes appointments across clinics.
- #3 `app/Controllers/Api/Finance.php:18` `->findAll(100)`; tables: `finances`; risk: API list exposes financial records across clinics.
- #4 `app/Controllers/Api/Examination.php:17` `->findAll(100)`; tables: `examinations`; risk: API list exposes exams across clinics.
- #5 `app/Controllers/Api/Search.php:51` `->like('first_name', $query)`; tables: `patients`; risk: patient search has no clinic filter.
- #6 `app/Controllers/Api/Search.php:101` `->like('first_name', $query)`; tables: `users`; risk: user search has no clinic filter.
- #7 `app/Controllers/Api/Search.php:138` `->join('patients', 'patients.id = examinations.patient_id')`; tables: `examinations`, `patients`; risk: joined search can leak cross-clinic exam/patient data.
- #8 `app/Controllers/Api/Search.php:186` `->join('patients', 'patients.id = treatments.patient_id')`; tables: `treatments`, `patients`; risk: joined search can leak cross-clinic treatment/patient data.
- #9 `app/Controllers/Api/Search.php:235` `$builder = $this->inventoryModel->where('quantity >', 0);`; tables: `inventory`; risk: inventory search is unscoped to clinic.
- #10 `app/Controllers/Api/Search.php:249` `->like('name', $query)`; tables: `inventory`; risk: inventory search can reveal other clinics' stock.
- #11 `app/Controllers/Reports.php:235` `->where('created_at >=', $startDate)->findAll()`; tables: `patients`; risk: report lists patients across clinics.
- #12 `app/Controllers/Reports.php:245` `->where('examination_date >=', $startDate)->findAll()`; tables: `examinations`; risk: report lists exams across clinics.
- #13 `app/Controllers/Reports.php:255` `->where('appointment_date >=', $startDate)->findAll()`; tables: `appointments`; risk: report lists appointments across clinics.
- #14 `app/Controllers/Reports.php:265` `->where('created_at >=', $startDate)->findAll()`; tables: `finances`; risk: report lists financial transactions across clinics.
- #15 `app/Controllers/Reports.php:275` `->where('start_date >=', $startDate)->findAll()`; tables: `treatments`; risk: report lists treatments across clinics.
- #16 `app/Controllers/Reports.php:336` `select('date_of_birth')->findAll()`; tables: `patients`; risk: pulls sensitive patient demographics across clinics.
- #17 `app/Controllers/Reports.php:448` `->where('payment_status', 'pending')->findAll()`; tables: `finances`; risk: pending payments across clinics.
- #18 `app/Controllers/Reports.php:220` `selectSum('total_amount')->where('transaction_type', 'payment')`; tables: `finances`; risk: revenue aggregates across clinics.
- #19 `app/Controllers/Reports.php:215` `->where('created_at >=', $startDate)->countAll()`; tables: `patients`; risk: patient counts aggregated across clinics.
- #20 `app/Controllers/Dashboard.php:196` `orderBy('created_at', 'DESC')->limit(5)->findAll()`; tables: `patients`; risk: dashboard shows latest patients across clinics.
- #21 `app/Controllers/Patient.php:66` `$this->patientModel->findAll()`; tables: `patients`; risk: full patient list unscoped.
- #22 `app/Controllers/Appointment.php:153` `->where('status', 'active')->findAll()`; tables: `patients`; risk: appointment form sees all active patients across clinics.
- #23 `app/Controllers/Examination.php:54` `->where('status', 'active')->findAll()`; tables: `patients`; risk: examination form sees all active patients across clinics.
- #24 `app/Controllers/Finance.php:45` `->where('status', 'active')->findAll()`; tables: `patients`; risk: finance screens list all active patients across clinics.
- #25 `app/Controllers/Finance.php:46` `->where('status', 'completed')->findAll()`; tables: `examinations`; risk: finance screens list all completed exams across clinics.
- #26 `app/Controllers/Prescription.php:229` `->findAll()`; tables: `patients`; risk: prescription form lists all patients across clinics.
- #27 `app/Controllers/Treatment.php:238` `->findAll()`; tables: `patients`; risk: treatment form lists all patients across clinics.
- #28 `app/Controllers/InventoryUsage.php:61` `orderBy('first_name', 'ASC')->findAll()`; tables: `patients`; risk: inventory usage UI lists patients across clinics.
- #29 `app/Controllers/Inventory.php:44` `$this->inventoryModel->findAll()`; tables: `inventory`; risk: inventory list unscoped to clinic.
- #30 `app/Controllers/UserManagementController.php:429` `$this->userModel->findAll()`; tables: `users`; risk: user list unscoped to clinic.
