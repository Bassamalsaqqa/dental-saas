**1) Role System Consolidation Audit**
- IonAuth groups are used for admin decisions (`isAdmin`, `inGroup`) in filters and controllers. Evidence: `app/Filters/AdminFilter.php:42`, `app/Controllers/Auth.php:83`, `app/Controllers/Users.php:367`, `app/Helpers/auth_helper.php:25`, `app/Helpers/auth_helper.php:66`, `app/Config/IonAuth.php:149`, `democa_dental.sql:170-178`.
- PermissionFilter enforces RBAC via PermissionService on most routes, while admin-only routes use AdminFilter (IonAuth), creating dual authorization paths. Evidence: `app/Filters/PermissionFilter.php:48`, `app/Filters/PermissionFilter.php:65`, `app/Config/Routes.php:44`, `app/Config/Routes.php:322`.
- Custom RBAC is implemented via roles/permissions tables and `PermissionService` (`super_admin` role slug). Evidence: `app/Services/PermissionService.php:26`, `app/Services/PermissionService.php:167`, `app/Services/PermissionService.php:189`, `democa_dental.sql:418`, `democa_dental.sql:430-438`.
- Hardcoded permission maps exist in both `UserModel` and `auth_helper`, conflicting with DB-driven RBAC. Evidence: `app/Models/UserModel.php:235`, `app/Models/UserModel.php:237`, `app/Helpers/auth_helper.php:96`, `app/Config/Permissions.php:44`, `app/Config/Permissions.php:104`.
- Conflict/overlap summary: IonAuth uses `groups`/`users_groups` with admin group `admin`, while RBAC uses `roles`/`user_roles` with slug `super_admin`; both are used at runtime, plus hardcoded permission arrays in code. Evidence: `democa_dental.sql:170-178`, `democa_dental.sql:418-438`, `app/Controllers/BaseController.php:304`, `app/Services/PermissionService.php:167`, `app/Controllers/UserManagementController.php:57`.
- Conclusion (source of truth for SaaS): consolidate on RBAC (`PermissionService` + `PermissionFilter` + `roles/permissions` tables) because it is already the route-level gate and defines `super_admin` in the DB; IonAuth groups should become legacy/compat only. Evidence: `app/Config/Routes.php:44`, `app/Filters/PermissionFilter.php:65`, `democa_dental.sql:430-438`.

**2) Tenant Context Lifecycle (exact touchpoints)**
- Post-login clinic selection is not implemented: `Auth::login()` calls `IonAuth->login()` and redirects straight to `/dashboard`. Evidence: `app/Controllers/Auth.php:110`, `app/Controllers/Auth.php:142`, `app/Controllers/Auth.php:146`.
- Current session keys set by auth: `identity`, `email`, `user_id`, `old_last_login`, `last_check`. Evidence: `app/Models/IonAuthModel.php:2004`, `app/Models/IonAuthModel.php:2013`.
- Existing session usage patterns: `redirect_url` is stored by AuthFilter; multiple controllers directly check `session()->get('user_id')`. Evidence: `app/Filters/AuthFilter.php:53`, `app/Controllers/ActivityLog.php:20`, `app/Controllers/Notifications.php:20`.
- Per-request enforcement candidates: AuthFilter already gates auth; PermissionFilter enforces RBAC; BaseController `initController()` injects global clinic info from settings (currently single-clinic). Evidence: `app/Filters/AuthFilter.php:34`, `app/Filters/PermissionFilter.php:65`, `app/Controllers/BaseController.php:69-72`.
- UNKNOWN: there is no clinic membership table in SQL, so multi-clinic selection rules and default clinic cannot be derived from schema. Verify by searching for a `clinics` table or join table in `democa_dental.sql` (no `CREATE TABLE clinics` found in current scan). Evidence: `democa_dental.sql:506`, `democa_dental.sql:521-524`.

**3) Query Scoping Strategy (how to stop leaks)**
- Models that must be tenant-scoped (business data): `activity_logs`, `appointments`, `examinations`, `finances`, `inventory`, `inventory_usage`, `odontograms`, `patients`, `prescriptions`, `treatments`, `settings`. Evidence: `app/Models/ActivityLogModel.php:9`, `app/Models/AppointmentModel.php:9`, `app/Models/ExaminationModel.php:9`, `app/Models/FinanceModel.php:9`, `app/Models/InventoryModel.php:9`, `app/Models/InventoryUsageModel.php:9`, `app/Models/OdontogramModel.php:9`, `app/Models/PatientModel.php:9`, `app/Models/PrescriptionModel.php:9`, `app/Models/TreatmentModel.php:9`, `app/Models/SettingsModel.php:9`.
- No shared base model exists; every model extends `CodeIgniter\Model` directly, so there is no central scoping hook today. Evidence: `app/Models/ActivityLogModel.php:7`, `app/Models/PatientModel.php:7`, `app/Models/FinanceModel.php:7`.
- High-risk query sites outside models (raw builder in controllers/services): ActivityLog, Doctor, Examination, Finance, Inventory, Prescription, Treatment, Settings backup. Evidence: `app/Controllers/ActivityLog.php:75`, `app/Controllers/Doctor.php:270`, `app/Controllers/Examination.php:354`, `app/Controllers/Finance.php:482`, `app/Controllers/Inventory.php:696`, `app/Controllers/Prescription.php:157`, `app/Controllers/Treatment.php:157`, `app/Controllers/Settings.php:655`.
- Top 50 fix list (file:line — scoping approach):
```
1) app/Controllers/ActivityLog.php:75 — local (add clinic_id filter on activity_logs)
2) app/Controllers/ActivityLog.php:105 — local
3) app/Controllers/ActivityLog.php:222 — local
4) app/Controllers/ActivityLog.php:228 — local
5) app/Controllers/Doctor.php:270 — local (scope users by active clinic membership)
6) app/Controllers/Doctor.php:289 — local
7) app/Controllers/Examination.php:341 — local
8) app/Controllers/Examination.php:354 — local
9) app/Controllers/Examination.php:446 — local
10) app/Controllers/Examination.php:452 — local
11) app/Controllers/Examination.php:458 — local
12) app/Controllers/Examination.php:491 — local
13) app/Controllers/Examination.php:500 — local
14) app/Controllers/Examination.php:518 — local
15) app/Controllers/Finance.php:380 — local
16) app/Controllers/Finance.php:383 — local
17) app/Controllers/Finance.php:469 — local
18) app/Controllers/Finance.php:482 — local
19) app/Controllers/Inventory.php:680 — local
20) app/Controllers/Inventory.php:696 — local
21) app/Controllers/Inventory.php:792 — local
22) app/Controllers/Inventory.php:1027 — local
23) app/Controllers/Inventory.php:1040 — local
24) app/Controllers/Prescription.php:64 — local
25) app/Controllers/Prescription.php:67 — local
26) app/Controllers/Prescription.php:144 — local
27) app/Controllers/Prescription.php:157 — local
28) app/Controllers/Treatment.php:144 — local
29) app/Controllers/Treatment.php:157 — local
30) app/Controllers/Settings.php:663 — local (backup must be clinic-scoped or admin-only global)
31) app/Controllers/Settings.php:655 — local
32) app/Models/PatientModel.php:112 — central (BaseTenantModel / global scope)
33) app/Models/PatientModel.php:124 — central
34) app/Models/PatientModel.php:106 — central
35) app/Models/AppointmentModel.php:98 — central
36) app/Models/AppointmentModel.php:126 — central
37) app/Models/AppointmentModel.php:160 — central
38) app/Models/AppointmentModel.php:87 — central
39) app/Models/ExaminationModel.php:86 — central
40) app/Models/ExaminationModel.php:95 — central
41) app/Models/ExaminationModel.php:105 — central
42) app/Models/FinanceModel.php:124 — central
43) app/Models/FinanceModel.php:127 — central
44) app/Models/FinanceModel.php:134 — central
45) app/Models/TreatmentModel.php:106 — central
46) app/Models/TreatmentModel.php:108 — central
47) app/Models/PrescriptionModel.php:101 — central
48) app/Models/PrescriptionModel.php:103 — central
49) app/Models/InventoryModel.php:111 — central
50) app/Models/InventoryUsageModel.php:89 — central
```

**4) High-Risk Admin Tools Review**
- Settings backup/export is admin-only by `AdminFilter`, but that filter is IonAuth-based (`admin` group) and not RBAC; backup reads every table via `SHOW CREATE TABLE` and `SELECT *` (full data exposure). Evidence: `app/Config/Routes.php:322`, `app/Filters/AdminFilter.php:42`, `app/Controllers/Settings.php:263`, `app/Controllers/Settings.php:572`, `app/Controllers/Settings.php:655`, `app/Controllers/Settings.php:663`.
- `RepairController` runs schema-altering SQL on `finances` and `users`, and is not routed in `Routes.php`. Evidence: `app/Controllers/RepairController.php:11`, `app/Controllers/RepairController.php:20`, `app/Controllers/RepairController.php:31`, `app/Controllers/RepairController.php:48`; `app/Config/Routes.php` has no `RepairController` entries.
- CLI scripts (`scripts/repair_db.php`, `scripts/init_database.php`) are guarded by `php_sapi_name()` but still contain schema/data operations that would be unsafe in SaaS if exposed. Evidence: `scripts/repair_db.php:2`, `scripts/repair_db.php:24`, `scripts/init_database.php:2`, `scripts/init_database.php:30`.

**5) Schema Migration Impact Map (based on democa_dental.sql)**
- Tables that need `clinic_id` (tenant data): `patients`, `appointments`, `examinations`, `treatments`, `prescriptions`, `finances`, `inventory`, `inventory_usage`, `odontograms`, `activity_logs`, summary tables (`appointment_summary`, `financial_summary`, `patient_examination_summary`), and likely `settings` (clinic branding/config). Evidence: `democa_dental.sql:30`, `democa_dental.sql:59`, `democa_dental.sql:95`, `democa_dental.sql:125`, `democa_dental.sql:191`, `democa_dental.sql:216`, `democa_dental.sql:254`, `democa_dental.sql:277`, `democa_dental.sql:393`, `democa_dental.sql:540`, `democa_dental.sql:81`, `democa_dental.sql:156`, `democa_dental.sql:310`, `democa_dental.sql:506`.
- Likely global tables (shared across clinics): `users`, `groups`, `users_groups`, `roles`, `permissions`, `role_permissions`, `user_roles`, `user_permissions`, `permission_audit_log`, `login_attempts`. Evidence: `democa_dental.sql:171`, `democa_dental.sql:328`, `democa_dental.sql:375`, `democa_dental.sql:418`, `democa_dental.sql:444`, `democa_dental.sql:616`, `democa_dental.sql:635`, `democa_dental.sql:652`, `democa_dental.sql:241`.
- Unique keys that likely become composite with `clinic_id`: `appointments.appointment_id`, `examinations.examination_id`, `finances.transaction_id`, `patients.patient_id`, `patients.email`, `prescriptions.prescription_id`, `treatments.treatment_id`, `settings.setting_key` (if settings become per-clinic). Evidence: `democa_dental.sql:690`, `democa_dental.sql:700`, `democa_dental.sql:710`, `democa_dental.sql:758`, `democa_dental.sql:759`, `democa_dental.sql:786`, `democa_dental.sql:820`, `democa_dental.sql:813`.
- Summary tables exist but have no code references; population mechanism is UNKNOWN. Verify by searching for table names in PHP code. Evidence: `democa_dental.sql:81`, `democa_dental.sql:156`, `democa_dental.sql:310` (no matches in current repo scan).

**6) Minimal Clinic Picker UI Touchpoints**
- Authenticated layout and sidebar are built in `main_auth.php` and already consume `$clinic` for branding; this is the most direct place to add a “Switch clinic” UI and show active clinic. Evidence: `app/Views/layouts/main_auth.php:6`, `app/Views/layouts/main_auth.php:62`, `app/Views/layouts/main_auth.php:65`.
- BaseController injects `clinic` into all views via `settings()->getClinicInfo()`, which is currently global (single clinic). This is the hook that would need to read `active_clinic_id` instead of global settings. Evidence: `app/Controllers/BaseController.php:69-72`, `app/Controllers/BaseController.php:151-153`, `app/Services/SettingsService.php:356-366`.
- Redirect when `active_clinic_id` is missing should occur before controller logic; AuthFilter is the current per-request gate for logged-in routes, and routes are already grouped under `auth`. Evidence: `app/Filters/AuthFilter.php:34`, `app/Config/Routes.php:36`, `app/Config/Routes.php:41`.
- Unauthenticated layout `main.php` also renders `$clinic` branding; if a clinic picker is required pre-login, that layout is the minimal touchpoint. Evidence: `app/Views/layouts/main.php:6`, `app/Views/layouts/main.php:38`, `app/Views/layouts/main.php:41`.
