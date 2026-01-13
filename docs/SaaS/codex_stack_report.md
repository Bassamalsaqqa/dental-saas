# DentaCare Pro Technical Dossier

A) Executive Overview
- What it is: CodeIgniter 4 dental clinic management system with modules for patients, appointments, examinations, treatments, prescriptions, finance, inventory, reports, users/RBAC, notifications, settings, and activity logs.
  Evidence: app/Config/Routes.php:43-395
- Primary data flow: request -> global filters (CSRF, secure headers, CSRF JSON) -> controller action -> model/service -> view or JSON.
  Evidence: app/Config/Filters.php:79-84, app/Filters/AuthFilter.php:42-56, app/Filters/PermissionFilter.php:28-87
- Views are layout-based (main_auth for authenticated pages; main for public/demo pages) with section rendering.
  Evidence: app/Views/appointment/index.php:1-3, app/Views/layouts/main_auth.php:456, app/Views/layouts/main.php:561

B) Stack and Versions (with evidence)
- PHP: minimum 8.1 (runtime requirement).
  Evidence: composer.json:13, index_public.php:12
- CodeIgniter: 4.6.3.
  Evidence: system/CodeIgniter.php:58
- Composer requirements: ext-intl, ext-mbstring, laminas/laminas-escaper, psr/log.
  Evidence: composer.json:14-17
- Frontend build: Tailwind CSS via npm scripts building src/input.css -> public/assets/css/tailwind.css.
  Evidence: package.json:10-13, src/input.css:1, public/assets/css/tailwind.css
- PostCSS: tailwindcss + autoprefixer.
  Evidence: postcss.config.js:1-4
- Frontend libs (CDN in main_auth layout): Font Awesome 6.4.0, Select2 4.1.0-rc.0, DataTables 1.13.7 + Buttons 2.4.2, Chart.js, FullCalendar 6.1.8, jQuery 3.7.1.
  Evidence: app/Views/layouts/main_auth.php:9-25
- Intl polyfill for missing extension is loaded at runtime.
  Evidence: public/index.php:2-22, app/Config/Polyfills.php:1-50

C) Repository Map
- Key directories: app/, public/, writable/, scripts/, tests/, assets/, src/, system/.
  Evidence: app/Config/Paths.php:29-77
- Entry points:
  - Root index.php proxies to public/index.php.
    Evidence: index.php:7-10
  - public/index.php is the real front controller.
    Evidence: public/index.php:1-33
- Rewrite and hardening:
  - Root .htaccess blocks sensitive dirs/files, rewrites /assets and /uploads to public/.
    Evidence: .htaccess:59-74
  - public/.htaccess sets RewriteBase /dev/dental/ (environment-specific).
    Evidence: public/.htaccess:55

D) Backend Architecture Deep Dive
- Routes overview (groups): auth, dashboard, patient(s), examinations, odontogram, finance, appointment(s), treatment(s), prescription(s), reports, inventory, notifications, settings, profile, users, roles, doctors, rbac, api.
  Evidence: app/Config/Routes.php:9-406
- Controllers (domains): ActivityLog, Appointment, Auth, Dashboard, Doctor, Examination, Finance, Inventory, Notifications, Odontogram, Patient, Prescription, Profile, Reports, RoleController, Settings, Users, Treatment.
  Evidence: app/Controllers/*.php (file list)
- Services:
  - ActivityLogger (activity log entries).
    Evidence: app/Services/ActivityLogger.php:8-79
  - PermissionService (RBAC checks).
    Evidence: app/Services/PermissionService.php:10-73
  - PermissionSyncService (sync config permissions and roles to DB).
    Evidence: app/Services/PermissionSyncService.php:21-130
  - SettingsService (clinic info and currency).
    Evidence: app/Services/SettingsService.php:356-365
- Filters:
  - AuthFilter: redirects or JSON 401 for API.
    Evidence: app/Filters/AuthFilter.php:42-56
  - PermissionFilter: fail-closed if no permission args; JSON 403 for API.
    Evidence: app/Filters/PermissionFilter.php:28-87
  - AdminFilter: admin-only redirects.
    Evidence: app/Filters/AdminFilter.php:38-44
  - CsrfJson: injects csrf_token in JSON responses.
    Evidence: app/Filters/CsrfJson.php:27-29

E) Auth and RBAC (Exact)
- IonAuth config:
  - identity = email, admin group = admin.
    Evidence: app/Config/IonAuth.php:149-150
  - tables: users, groups, users_groups, login_attempts.
    Evidence: app/Config/IonAuth.php:49-53
- Auth controller uses IonAuth for login/logout and admin checks.
  Evidence: app/Controllers/Auth.php:59-83, app/Controllers/Auth.php:142-192
- RBAC schema (migrations): permissions, roles, role_permissions, user_roles, user_permissions (with FK constraints).
  Evidence: app/Database/Migrations/2025-01-19-000001_CreateRBACTables.php:11-200
- RBAC seed data and permissions list.
  Evidence: app/Database/Seeds/RBACSeeder.php:11-231
- PermissionService super admin bypass: role slug super_admin.
  Evidence: app/Services/PermissionService.php:30-33, app/Services/PermissionService.php:203-209
- Permission checks enforced via route filters.
  Evidence: app/Config/Routes.php:44-365

F) Database Schema and Relations (Most Important)
1) Authoritative schema source
- Migrations are incomplete for core domain tables (patients, appointments, etc.). Only RBAC and activity log plus a few alters are defined.
  Evidence: app/Database/Migrations/2024-01-01-000000_CreateInventoryUsageTable.php (empty), app/Database/Migrations/2025-01-19-000001_CreateRBACTables.php:11-200, app/Database/Migrations/2025-01-19-000002_CreateActivityLogsTable.php:7-86
- SQL dump present: democa_dental.sql. UNKNOWN if authoritative.
  Evidence: democa_dental.sql
  Verify: inspect democa_dental.sql or run SHOW CREATE TABLE in the actual database.

2) Table inventory (from models; schema details inferred)
- patients: PatientModel expects fields including patient_id, name, contact, demographics, insurance, status, timestamps.
  Evidence: app/Models/PatientModel.php:9-43
- appointments: AppointmentModel expects patient_id, appointment_id, date/time, duration, type, status, notes, created_by, timestamps.
  Evidence: app/Models/AppointmentModel.php:9-32
- examinations: ExaminationModel expects patient_id, examination_id, clinical fields, status, created_by, timestamps.
  Evidence: app/Models/ExaminationModel.php:9-40
- treatments: TreatmentModel expects patient_id, examination_id, treatment fields, cost, status, timestamps, deleted_at.
  Evidence: app/Models/TreatmentModel.php:9-35
- prescriptions: PrescriptionModel expects patient_id, medication fields, status, timestamps.
  Evidence: app/Models/PrescriptionModel.php:9-35
- finances: FinanceModel expects patient_id, examination_id, amounts, currency, payment fields, total_amount, timestamps.
  Evidence: app/Models/FinanceModel.php:9-41
- inventory: InventoryModel expects item fields, quantity, unit_price, status, timestamps.
  Evidence: app/Models/InventoryModel.php:9-38
- inventory_usage: InventoryUsageModel expects treatment_id, usage_date, items_used JSON, total_cost, recorded_by, timestamps.
  Evidence: app/Models/InventoryUsageModel.php:9-30
- odontograms: OdontogramModel expects patient_id, examination_id, tooth condition fields, timestamps.
  Evidence: app/Models/OdontogramModel.php:9-33
- settings: SettingsModel uses key/value entries with type.
  Evidence: app/Models/SettingsModel.php:9-27
- users: UserModel expects user profile fields, doctor fields, hire_date, active.
  Evidence: app/Models/UserModel.php:9-37
- permissions, roles, user_roles, user_permissions, activity_logs.
  Evidence: app/Models/PermissionModel.php:9, app/Models/RoleModel.php:9, app/Models/UserRoleModel.php:9, app/Models/ActivityLogModel.php:9

3) Foreign keys and constraints
- RBAC tables have FK constraints defined in migrations.
  Evidence: app/Database/Migrations/2025-01-19-000001_CreateRBACTables.php:117-200
- activity_logs.user_id FK to users.
  Evidence: app/Database/Migrations/2025-01-19-000002_CreateActivityLogsTable.php:84
- For core domain tables, FK constraints are UNKNOWN without DB schema or SQL dump inspection.
  Verify: inspect democa_dental.sql or run SHOW CREATE TABLE for each table.

4) ERD (inferred from model fields and join usage)
- patients 1-* appointments (appointments.patient_id).
  Evidence: app/Models/AppointmentModel.php:16
- patients 1-* examinations (examinations.patient_id).
  Evidence: app/Models/ExaminationModel.php:16
- examinations 1-* treatments (treatments.examination_id).
  Evidence: app/Models/TreatmentModel.php:17
- patients 1-* prescriptions (prescriptions.patient_id).
  Evidence: app/Models/PrescriptionModel.php:17
- patients 1-* finances (finances.patient_id).
  Evidence: app/Models/FinanceModel.php:16
- examinations 0..1-* finances (finances.examination_id).
  Evidence: app/Models/FinanceModel.php:17
- patients 1-* odontograms (odontograms.patient_id).
  Evidence: app/Models/OdontogramModel.php:16
- examinations 1-* odontograms (odontograms.examination_id).
  Evidence: app/Models/OdontogramModel.php:17
- treatments 1-* inventory_usage (inventory_usage.treatment_id).
  Evidence: app/Models/InventoryUsageModel.php:16
- users 1-* inventory_usage (inventory_usage.recorded_by).
  Evidence: app/Models/InventoryUsageModel.php:21

5) Core transactions (controller evidence)
- Appointment creation: validates availability then inserts into appointments.
  Evidence: app/Controllers/Appointment.php:164-215
- Examination creation: inserts into examinations; completion updates status.
  Evidence: app/Controllers/Examination.php:67-105, app/Controllers/Examination.php:271-284
- Treatment creation: inserts into treatments.
  Evidence: app/Controllers/Treatment.php:246-283
- Finance creation: inserts into finances; ties to patient and optional examination.
  Evidence: app/Controllers/Finance.php:53-96, app/Controllers/Finance.php:123-124
- Inventory usage: writes inventory_usage and decrements inventory quantities.
  Evidence: app/Controllers/Inventory.php:497-549

G) Frontend (Views) and JS Data Flow
- Layouts and sections:
  - main_auth includes libs, CSRF meta, and renderSection('content').
    Evidence: app/Views/layouts/main_auth.php:6-33, app/Views/layouts/main_auth.php:456
  - main layout for public/demo pages.
    Evidence: app/Views/layouts/main.php:6-14, app/Views/layouts/main.php:561
- CSRF in JS: global CSRF config, cookie-based refresh, token refresh on AJAX JSON responses.
  Evidence: app/Views/layouts/main_auth.php:481-519, app/Views/layouts/main_auth.php:677-678
- DataTables: server-side usage in multiple views (example in patients list).
  Evidence: app/Views/patient/index_new.php:125
- Select2: initializer reads data-search-url and processes JSON results.
  Evidence: public/assets/js/select2-init.js:12-100
- FullCalendar:
  - Appointment calendar uses FullCalendar 5.11.3.
    Evidence: app/Views/appointment/calendar.php:76-86
  - Examination calendar uses FullCalendar (version loaded in layout is 6.1.8).
    Evidence: app/Views/examination/calendar.php:107, app/Views/layouts/main_auth.php:16
- Charting: dashboard uses Chart.js for revenue and treatment charts.
  Evidence: app/Views/dashboard/index.php:167-474

H) Settings and Branding
- Settings storage: settings table (key/value/type). Model parses types (string/number/boolean/json).
  Evidence: app/Models/SettingsModel.php:9-32, app/Models/SettingsModel.php:103-109
- Clinic info source: SettingsService::getClinicInfo returns name/address/phone/email/website/logo_path/tagline defaults.
  Evidence: app/Services/SettingsService.php:356-365
- Global injection: BaseController injects clinic into renderer and view data.
  Evidence: app/Controllers/BaseController.php:71-73, app/Controllers/BaseController.php:152-156
- Logo upload pipeline:
  - Upload to FCPATH/uploads/clinic with deterministic filename clinic-logo.<ext>, validated by is_image, mime_in, ext_in, max_size.
    Evidence: app/Controllers/Settings.php:48-88
- View usage:
  - main_auth and main layouts show logo (default icon if none), name, tagline.
    Evidence: app/Views/layouts/main_auth.php:49-65, app/Views/layouts/main.php:25-41
  - login view uses clinic name/tagline.
    Evidence: app/Views/auth/login.php:42-45
  - print views use clinic name/logo/website where relevant.
    Evidence: app/Views/appointment/print.php:183-198, app/Views/finance/invoice.php:25-45, app/Views/prescription/print.php:8-25, app/Views/odontogram/export.php:40-46, app/Views/odontogram/pdf.php:64-70

I) API Surface
- API group: /api/v1 with patients, examinations, appointments, finances (permission-protected).
  Evidence: app/Config/Routes.php:406-434
- Search API: /api/search with multiple entities.
  Evidence: app/Config/Routes.php:438-447
- JSON endpoints outside /api (DataTables/stats): patients/get-data, finance/getFinancesData, inventory/getInventoryData, treatments/getTreatmentsData, prescriptions/getPrescriptionsData.
  Evidence: app/Config/Routes.php:60-79, app/Config/Routes.php:155-156, app/Config/Routes.php:281-288, app/Config/Routes.php:209-211, app/Config/Routes.php:245-246
- Notifications JSON endpoint: /api/notifications (non-/api group).
  Evidence: app/Config/Routes.php:309

J) Deployment and Environment
- baseURL from env app.baseURL.
  Evidence: app/Config/App.php:31-33
- HTTPS enforcement: forceGlobalSecureRequests = true.
  Evidence: app/Config/App.php:191
- public/.htaccess RewriteBase is /dev/dental/ (environment-specific).
  Evidence: public/.htaccess:55
- DB config sourced from env database.default.*; DBDebug only in development.
  Evidence: app/Config/Database.php:36, app/Config/Database.php:66-70
- Sessions stored in writable/session (file handler).
  Evidence: app/Config/Session.php:24-35
- Cache uses dummy handler by default.
  Evidence: app/Config/Cache.php:24-35
- Root .htaccess rewrites /uploads to public/uploads; uploads stored under public/uploads/clinic.
  Evidence: .htaccess:66-69, app/Controllers/Settings.php:67

K) SaaS / Multi-Tenancy Readiness Findings (Facts + Options)
- Single-tenant assumptions: settings are global key/value; no clinic_id in model fields; logo stored as single clinic-logo file.
  Evidence: app/Models/SettingsModel.php:16-27, app/Services/SettingsService.php:356-365, app/Controllers/Settings.php:85
- No tenant discriminator in routes/controllers/services.
  Evidence: app/Config/Routes.php:43-395, app/Services/PermissionService.php:28
- Options (impact analysis only):
  1) Single DB with clinic_id columns
     - Would require clinic_id on core tables (patients, appointments, examinations, treatments, prescriptions, finances, inventory, inventory_usage, odontograms, activity_logs) and every model query/join.
       Evidence touchpoints: app/Models/PatientModel.php:15-37, app/Models/AppointmentModel.php:15-26, app/Models/ExaminationModel.php:15-33, app/Models/TreatmentModel.php:15-34, app/Models/PrescriptionModel.php:15-28, app/Models/FinanceModel.php:15-35, app/Models/InventoryModel.php:15-30, app/Models/InventoryUsageModel.php:15-23, app/Models/OdontogramModel.php:15-27, app/Models/ActivityLogModel.php:15-24
  2) DB per tenant (connection switching)
     - Would require dynamic DB connection selection in BaseController or a filter and in SettingsService usage.
       Evidence touchpoints: app/Controllers/BaseController.php:71-156, app/Services/SettingsService.php:356-365
  3) Schema per tenant (same DB, different schema)
     - Would require table name prefixing or runtime schema selection in all models.
       Evidence touchpoints: app/Models/*:9 (table definitions)

L) Verification Appendix (commands)
- Routes and filters
  - rg -n "group\(" app/Config/Routes.php
  - rg -n "permission:" app/Config/Routes.php
  - rg -n "aliases|globals" app/Config/Filters.php
- Stack and versions
  - rg -n "CI_VERSION" system/CodeIgniter.php
  - rg -n "php" composer.json
  - rg -n "tailwind" package.json
  - rg -n "content" tailwind.config.js
- Layouts and CSRF
  - rg -n "csrf" app/Views/layouts/main_auth.php
- DataTables / FullCalendar / Chart.js
  - rg -n "DataTable|FullCalendar|Chart" app/Views
- Migrations and schema
  - Get-ChildItem app/Database/Migrations
  - php spark migrate:status
  - mysql -e "SHOW TABLES;"
  - mysql -e "SHOW CREATE TABLE patients;"
- Security sinks in views
  - rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views

UNKNOWN items (with verification steps)
- Full schema for core domain tables (patients, appointments, etc.) is UNKNOWN from migrations alone.
  Verify: inspect democa_dental.sql or run SHOW CREATE TABLE for each table in the actual DB.
- Existence of permission_audit_log table is UNKNOWN (referenced by PermissionService only).
  Evidence: app/Services/PermissionService.php:318-337
  Verify: SHOW TABLES LIKE 'permission_audit_log' in DB.
- Production use of public/index.php with display_errors enabled is UNKNOWN.
  Evidence: public/index.php:18-19
  Verify: deployment entrypoint and PHP ini configuration.
