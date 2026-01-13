# Tenant Enforcement Blueprint - DentaCare Pro

## 1) Role System Consolidation Audit

**Current State Analysis:**
*   **IonAuth Groups:** `IonAuth->isAdmin()` (Auth.php L45) checks if a user is in the 'admin' group. This is the primary gatekeeper for high-level admin functions.
*   **PermissionFilter:** `app/Filters/PermissionFilter.php` uses `PermissionService` to check granular permissions (`hasPermission`).
*   **PermissionService:** (L35) Hardcodes `isSuperAdmin` check by looking for 'super_admin' role slug. It mixes IonAuth groups with a custom RBAC implementation (`user_roles`, `user_permissions` tables).
*   **UserModel:** Has hardcoded role-to-permission array `getUserPermissions` (L158), which seems redundant or partially overlapping with `PermissionService`.

**Consolidation Decision (Source of Truth):**
*   **Source of Truth:** **PermissionService + IonAuth Groups**.
*   **Mechanism:**
    *   **Global Super Admin:** Must be in IonAuth 'admin' group AND have 'super_admin' role slug.
    *   **Tenant Admin:** In IonAuth 'members' group (or new 'clinic_admin' group) + has 'clinic_admin' role in `user_roles` table.
    *   **Standard User:** In IonAuth 'members' group + specific roles (doctor, nurse) in `user_roles`.
*   **Action:** Deprecate `UserModel::getUserPermissions` hardcoded array. Move all logic to `PermissionService` backed by database tables.

## 2) Tenant Context Lifecycle

**A. Storage (Session):**
*   **Key:** `active_clinic_id`
*   **Location:** `app/Controllers/Auth.php` (login) and `app/Controllers/BaseController.php` (init).

**B. Initialization (Login Flow):**
*   **File:** `app/Controllers/Auth.php` -> `login()`
*   **Logic:** After successful IonAuth login:
    1.  Fetch user's associated clinics (from new `user_clinics` table).
    2.  If Count == 1: Set `active_clinic_id` = `clinic_id`.
    3.  If Count > 1: Set `active_clinic_id` = null (force selection screen).
    4.  If Super Admin: Set `active_clinic_id` = null (allow global view or force pick).

**C. Enforcement (Filter):**
*   **New Filter:** `app/Filters/TenantFilter.php`
*   **Logic:**
    *   Run `before` every request (except `auth/*`, `clinic/switch`).
    *   Check `session('active_clinic_id')`.
    *   If null: Redirect to `/clinic/select` (Clinic Picker).
    *   If set:
        *   Inject into `BaseModel` scope.
        *   Inject into `Services`.

## 3) Query Scoping Strategy

**A. Model Inventory (Must be Scoped):**
*   `PatientModel`
*   `AppointmentModel`
*   `ExaminationModel`
*   `FinanceModel`
*   `InventoryModel`
*   `InventoryUsageModel`
*   `OdontogramModel`
*   `PrescriptionModel`
*   `TreatmentModel`
*   `ActivityLogModel`

**B. Base Model Strategy:**
*   **Pattern:** Create `app/Models/TenantBaseModel.php` extending `CodeIgniter\Model`.
*   **Logic:**
    *   Override `findAll()`, `find()`, `first()`.
    *   Inject `$builder->where('clinic_id', session('active_clinic_id'))` automatically.
    *   Allow bypass for Super Admin via `withoutTenantScope()`.

**C. Top 50 High-Risk Fix List (Raw Queries & Global Access):**
*   `app/Controllers/Settings.php:663` - `SELECT * FROM {$table}` -> **Restrict to Super Admin Only**
*   `app/Controllers/Settings.php:655` - `SHOW CREATE TABLE` -> **Restrict to Super Admin Only**
*   `app/Controllers/RepairController.php:20` - `ALTER TABLE` -> **Disable in Production / Super Admin CLI Only**
*   `app/Controllers/Patient.php:66` - `$this->patientModel->findAll()` -> **Refactor to use TenantBaseModel**
*   `app/Controllers/Finance.php:581` - `$query->findAll()` -> **Refactor to use TenantBaseModel**
*   `app/Controllers/Appointment.php:153` - `where('status', 'active')->findAll()` -> **Refactor to use TenantBaseModel**
*   `app/Models/PatientModel.php:112` - Global Search -> **Add where('clinic_id', ...)**
*   `app/Models/InventoryModel.php:111` - `findAll()` -> **Refactor to use TenantBaseModel**
*   `app/Controllers/Api/Patient.php:18` - API Dump -> **Add TenantFilter to API Group**
*   `app/Services/PermissionSyncService.php` - Permissions Sync -> **Scope to Tenant Roles if applicable**

## 4) High-Risk Admin Tools Review

*   **Backup/Restore (`Settings::createBackup`, `performBackup`):**
    *   **Current:** Dumps entire DB.
    *   **SaaS Fix:** Disable for Tenant Admins. Only Super Admin can backup full DB. Tenant Admins get "Export Data" (CSV/JSON) scoped to their IDs.
*   **Repair Tools (`RepairController`, `scripts/*.php`):**
    *   **Current:** Global schema modifiers.
    *   **SaaS Fix:**
        *   Delete `public/scripts/*.php` (already planned in hardening).
        *   `RepairController`: Lock behind `SuperAdminFilter` (new).
*   **User Management (`Users::index`):**
    *   **Current:** Lists all users.
    *   **SaaS Fix:** `where('clinic_id', active_clinic)` or join `user_clinics`.

## 5) Schema Migration Impact Map

**A. New Tables:**
*   `clinics` (id, name, subdomain, logo, settings_json, created_at)
*   `user_clinics` (user_id, clinic_id, role_id) - **Pivot Table for Multi-Clinic Users**

**B. Tables requiring `clinic_id` (Composite Unique Keys):**
*   `patients` -> Unique(`clinic_id`, `patient_id`) (Patient ID string must be unique per clinic, not global)
*   `appointments` -> Unique(`clinic_id`, `appointment_id`)
*   `inventory` -> Unique(`clinic_id`, `item_code`)
*   `treatments`
*   `prescriptions`
*   `finances` -> Unique(`clinic_id`, `invoice_number`)
*   `examinations`
*   `odontograms`
*   `activity_logs`
*   `settings` -> Rename current `settings` to `global_settings` or add `clinic_id` (nullable for global).

**C. Global Tables (No `clinic_id`):**
*   `users` (User identity is global, access is scoped via pivot)
*   `groups` (IonAuth groups are system-wide)
*   `permissions` (System-defined capabilities)
*   `roles` (System-defined roles, e.g., "Doctor", "Nurse")

## 6) Minimal Clinic Picker UI Touchpoints

**A. Navigation Bar (`app/Views/layouts/main.php`):**
*   **Location:** Inside the "User Profile" dropdown or a dedicated top-bar dropdown.
*   **Implementation:**
    ```html
    <!-- If user has > 1 clinic -->
    <div class="clinic-switcher">
        <span>Current: <?= session('clinic_name') ?></span>
        <a href="/clinic/switch">Switch Clinic</a>
    </div>
    ```

**B. Redirect Logic:**
*   **Controller:** `app/Controllers/ClinicController.php` (New)
*   **Methods:**
    *   `select()`: Show list of available clinics for user.
    *   `switch($id)`: Validate user access to `$id`, set `active_clinic_id`, redirect to Dashboard.

**C. BaseController Injection:**
*   Ensure `view()` always passes `$current_clinic` to layouts so the UI can verify which context is active.
