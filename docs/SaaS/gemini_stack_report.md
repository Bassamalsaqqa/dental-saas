# DentaCare Pro - Technical Handoff Dossier

**Date:** 2026-01-11
**Subject:** Technical Architecture, Security & Operational Handoff
**Target Audience:** Senior Engineering / AI Model Successor

---

## A) Executive Overview

**DentaCare Pro** is a monolithic **Dental Practice Management System (DPMS)** built on **CodeIgniter 4 (PHP)** and **Tailwind CSS**. It is designed as a single-tenant application for managing patients, clinical records, appointments, inventory, and finance.

**Core Modules:**
1.  **Clinical:** Patients, Odontograms, Examinations, Treatments, Prescriptions.
2.  **Operational:** Appointments (Calendar), Inventory (Stock & Usage).
3.  **Financial:** Invoices, Payments, Expense Tracking (Unified Ledger).
4.  **Administrative:** User Management (Doctors/Staff), RBAC, System Settings.

**High-Level Data Flow:**
Request (`public/index.php`) -> Routing (`Config/Routes.php`) -> Global Filters (`CSRF`, `SecureHeaders`) -> Route Filters (`Auth`, `Permission`) -> Controller -> Service (Business Logic) -> Model (DB Abstraction) -> View (HTML) or JSON Response.

---

## B) Stack & Versions

| Component | Technology | Version | Evidence |
| :--- | :--- | :--- | :--- |
| **Language** | PHP | `^8.1` | `composer.json` |
| **Framework** | CodeIgniter | `4.5.x` | `composer.json` |
| **Auth Lib** | IonAuth | `4.x` | `app/Config/IonAuth.php` |
| **Frontend** | Tailwind CSS | `^3.4.1` | `package.json` |
| **Build Tool** | Tailwind CLI | `^3.4` | `package.json` |
| **JS Libs** | FullCalendar | `6.1.8` | `package.json` / `app/Views/appointment/calendar.php` |
| **JS Libs** | Chart.js | N/A | `app/Views/dashboard/index.php` (CDN usage) |
| **Database** | MySQL/MariaDB | N/A | `app/Config/Database.php` |

**Required PHP Extensions:**
- `intl`, `mbstring` (Framework requirement).
- `mysqli` (Database driver).
- `gd` or `imagick` (Image manipulation for uploads).

---

## C) Repository Map

```text
/
├── .env                  # Environment config (gitignored)
├── app/
│   ├── Config/           # App configuration (Routes, Filters, Database)
│   ├── Controllers/      # Request handlers (Web & API)
│   ├── Database/         # Migrations and Seeds
│   ├── Filters/          # Middleware (Auth, CSRF, RBAC)
│   ├── Models/           # Database abstraction
│   ├── Services/         # Business logic (Settings, Permissions)
│   └── Views/            # UI Templates
├── docs/                 # Documentation (Security, Logs, Guides)
├── public/               # Web root
│   ├── index.php         # Entry point
│   ├── uploads/          # User content (logos, etc.)
│   └── assets/           # Static assets (JS/CSS)
├── tests/                # PHPUnit tests
└── writable/             # Logs, cache, sessions, temp files
```

---

## D) Backend Architecture Deep Dive

### 1. Routing (`app/Config/Routes.php`)
- **Web Routes:** Define standard MVC paths.
- **API Routes:** Grouped under `api/` (Line 406).
- **Auth Filter:** Applied to `api` group via `['filter' => 'auth']`.

### 2. Controllers (`app/Controllers/`)
- **BaseController:** Parent class; handles helper loading and global view data injection (e.g., Clinic Branding).
- **Core Domain Controllers:**
    - `Patients.php`, `Appointments.php`: CRUD and business logic.
    - `Finance.php`: Handles invoices/expenses.
    - `Settings.php`: System configuration and branding.
- **Auth Controllers:** `Auth.php` (Login/Logout via IonAuth).

### 3. Services (`app/Services/`)
- **`PermissionService`:** Encapsulates RBAC logic (`hasPermission`, `userHasRole`).
- **`SettingsService`:** Fetches cached configuration from `settings` table.

### 4. Filters (`app/Config/Filters.php`)
- **Global:** `csrf`, `secureheaders` (Enabled in `$globals`).
- **Aliases:**
    - `auth`: `App\Filters\AuthFilter` (Enforces login).
    - `permission`: `App\Filters\PermissionFilter` (Enforces RBAC).
    - `csrfjson`: `App\Filters\CsrfJson` (JSON-specific CSRF handling).

---

## E) Auth & RBAC (Exact)

**Authentication:**
- **Library:** IonAuth 4.
- **Config:** `app/Config/IonAuth.php`.
- **Identity:** `email` (defined in config).
- **Tables:** `users`, `groups`, `users_groups` (standard IonAuth schema).

**Authorization (RBAC):**
- **Model:** Role-Based with Permission Overrides.
- **Tables:**
    - `roles` (Custom definitions).
    - `permissions` (Granular actions, e.g., `patients.view`).
    - `roles_permissions` (Linking table).
    - `users_permissions` (Direct user overrides).
- **Logic:** `App\Services\PermissionService`.
- **Enforcement:**
    - **Filter:** `PermissionFilter` checks permissions on routes.
    - **In-Code:** `if (!service('permission')->hasPermission('manage_settings')) ...`

**Super Admin Bypass:**
- **Status:** **CONFIRMED**.
- **Evidence:** `App\Services\PermissionService.php` contains logic that returns `true` if user group is 'admin' or 'superadmin'.

---

## F) Database Schema & Relations

**Source:** Migrations in `app/Database/Migrations` are the source of truth.

### Core Tables & Relations (ERD)

| Table | PK | Key Columns | Relationships |
| :--- | :--- | :--- | :--- |
| **users** | `id` | `email`, `password` | 1-to-Many -> `appointments` (`doctor_id`) / `examinations` (`doctor_id`) |
| **patients** | `id` | `first_name`, `phone` | 1-to-Many -> `appointments`, `prescriptions`, `finances` |
| **appointments** | `id` | `start_time`, `end_time` | Many-to-1 -> `users`, `patients` |
| **treatments** | `id` | `treatment_name`, `cost` | Many-to-1 -> `examinations` |
| **finances** | `id` | `amount`, `type`, `status` | Many-to-1 -> `patients`, 1-to-1 -> `treatments` (contextual) |
| **inventory** | `id` | `item_name`, `stock` | 1-to-Many -> `inventory_usage` |
| **settings** | `id` | `key`, `value` | Key-Value store or column-based. |

### Core Transactions
1.  **Appointment Creation:**
    - `AppointmentsController::create` validates input.
    - `AppointmentModel::save` inserts record.
2.  **Billing (Finance):**
    - `FinanceModel` uses a **Unified Ledger** approach (`type` column: 'income'/'expense').
3.  **Inventory Usage:**
    - `InventoryUsageModel` tracks consumption.
    - Controller logic decrements `inventory.quantity` transactionally.

---

## G) Frontend (Views) + JS Data Flow

- **Layouts:** `app/Views/layouts/main_auth.php` is the primary authenticated wrapper.
- **CSRF Injection:**
    - Meta tags: `<meta name="csrf-token" ...>` in `main_auth.php`.
    - JS Helper: `window.getCsrfToken()` defined in layout.
    - AJAX: `headers: { 'X-CSRF-TOKEN': window.getCsrfToken() }`.
- **UI Libraries:**
    - **DataTables:** Used in `index.php` views (Patients, Inventory). Hits JSON endpoints.
    - **Select2:** Used for patient/doctor selection.
    - **FullCalendar:** `app/Views/appointment/calendar.php`. Fetches events from `api/appointments`.

---

## H) Settings/Branding

- **Schema:** `settings` table.
- **Injection:** `BaseController::initController` fetches settings via `SettingsService` and shares `clinic` array to all views.
- **Usage:**
    - Views access `$clinic['name']`, `$clinic['logo_path']`.
    - Print views render logo via `<img src="<?= esc($logoSrc) ?>">`.
- **Storage:** Logos stored in `public/uploads/clinic/`.

---

## I) API Surface

**Base Path:** `/api`
**Authentication:** `AuthFilter` (Session-based).

**Key Endpoints:**
- `GET /api/appointments`: Returns JSON for Calendar.
- `GET /api/patients/search`: JSON for Select2.
- `GET /api/inventory/low_stock`: Alerts.

---

## J) Deployment & Environment

- **Config:** `.env` file (Database, Base URL, CI_ENVIRONMENT).
- **Storage:**
    - `writable/session`: Session files.
    - `writable/logs`: Error logs.
    - `public/uploads`: User generated content.
- **Hard Dependencies:**
    - **RewriteBase:** Critical in `.htaccess`.
    - **Permissions:** Web server user must have write access to `writable/` and `public/uploads/`.

---

## K) SaaS / Multi-Tenancy Readiness Findings

**Current State:** **Single-Tenant Monolith.**
**Assessment:** The application implicitly assumes one clinic owner. There is no concept of "Tenant ID".

**Isolation Gaps (Evidence):**
1.  **Global Settings:** `SettingsModel` fetches a single configuration set.
2.  **Data Models:** Models lack a `clinic_id` or `tenant_id` where clause.
3.  **Authentication:** `users` table has no tenant association.

**Candidate Strategies (Impact Analysis):**

1.  **Row-Level Tenancy (Shared DB):** Add `clinic_id` to ALL tables. Update ALL Models to auto-scope queries.
2.  **Database-Per-Tenant:** Middleware to swap DB connection based on Subdomain/URL.
3.  **Schema-Per-Tenant:** Similar to DB-per-tenant but swaps schema search path.

---

## L) Verification Appendix

**Regenerate Findings:**

1.  **Check CSRF Config:** `grep -r "csrf" app/Config/Filters.php`
2.  **List Routes & Filters:** `php spark routes`
3.  **Inspect Schema:** `cat app/Database/Migrations/*`
4.  **Verify RBAC Logic:** `grep -r "hasPermission" app/Services/`
5.  **Check Auth Lib:** `grep "identity" app/Config/IonAuth.php`
