# SaaS Control Plane Design - DentaCare Pro

## 1. Database Schema (Control Plane)

These tables form the "Super Admin" layer, managing the lifecycle of tenants (clinics).

### A. `plans`
Defines the service tiers available for subscription.

*   `id` (INT, PK, Auto)
*   `name` (VARCHAR 100) - e.g., "Starter", "Professional", "Enterprise"
*   `slug` (VARCHAR 50, Unique) - for code references (e.g., `pro_tier`)
*   `description` (TEXT)
*   `price_monthly` (DECIMAL 10,2)
*   `price_yearly` (DECIMAL 10,2)
*   `features` (JSON) - Toggleable feature flags (e.g., `{"sms_reminders": true, "max_doctors": 5}`)
*   `is_active` (TINYINT 1) - Soft delete/hide plan
*   `created_at`, `updated_at`

### B. `clinics` (Tenant Entity)
The central registry of all tenants.

*   `id` (INT, PK, Auto)
*   `name` (VARCHAR 100)
*   `subdomain` (VARCHAR 50, Unique) - The tenant identifier (e.g., `dental-plus`)
*   `owner_user_id` (INT) - Link to the primary admin in `users` table
*   `status` (ENUM) - `active`, `suspended`, `pending_setup`, `archived`
*   `settings` (JSON) - Tenant-specific overrides or config
*   `created_at`, `updated_at`

### C. `subscriptions`
Links a clinic to a plan and tracks validity.

*   `id` (INT, PK, Auto)
*   `clinic_id` (INT, FK -> clinics.id)
*   `plan_id` (INT, FK -> plans.id)
*   `status` (ENUM) - `active`, `past_due`, `cancelled`, `trial`
*   `start_date` (DATETIME)
*   `end_date` (DATETIME) - The critical access check field
*   `trial_ends_at` (DATETIME, Nullable)
*   `payment_method_token` (VARCHAR) - Stripe/Provider reference
*   `created_at`, `updated_at`

### D. `invoices` (Audit Trail)
*   `id` (INT, PK)
*   `clinic_id` (INT)
*   `amount` (DECIMAL)
*   `status` (paid, failed)
*   `invoice_pdf_path` (VARCHAR)
*   `created_at`

## 2. RBAC Integration

We utilize the existing `IonAuth` groups and `PermissionService`.

### Global Super Admin (The SaaS Operator)
*   **Identification:** Member of IonAuth `admin` group.
*   **Role:** `super_admin` (in `roles` table).
*   **New Permissions:**
    *   `saas.clinics.manage`: Create/Edit/Suspend clinics.
    *   `saas.plans.manage`: Create/Edit pricing tiers.
    *   `saas.financials.view`: View system-wide MRR/Revenue.
    *   `saas.impersonate`: Login as any tenant admin (for support).

### Tenant Admin (The Clinic Owner)
*   **Identification:** Member of IonAuth `members` group (NOT `admin` group).
*   **Role:** `clinic_admin` (in `roles` table).
*   **Context:** Has `clinic_id` set in session via `TenantFilter`.
*   **Restrictions:** Explicitly **DENIED** access to `saas.*` permissions. They cannot see `plans` or `subscriptions` tables directly, only their own status via a dedicated "My Subscription" view.

## 3. Enforcement Strategy

### A. Route Filters
We leverage the existing `AdminFilter` which checks `$ionAuth->isAdmin()`.

*   **Control Plane Routes (`/saas/*`)**: Protected by `['filter' => 'admin']`.
    *   Since Tenant Admins are NOT in the IonAuth `admin` group, they are hard-blocked from these routes.
*   **Tenant Routes (`/dashboard`, `/patients`)**: Protected by `['filter' => 'auth']` + New `TenantFilter`.
    *   `TenantFilter` ensures `active_clinic_id` is set and validity checks (is subscription active?) pass.

### B. Subscription Expiry Enforcement
The `TenantFilter` must include a subscription check:

```php
// Pseudo-code for TenantFilter
if ($subscription->status !== 'active' && $subscription->end_date < now()) {
    return redirect()->to('/billing/payment-required');
}
```

## 4. Minimal UI Routes (Super Admin Dashboard)

These routes are strictly for the Global Super Admin to manage the platform.

**Group:** `saas`
**Filter:** `admin`

| Method | Route | Controller::Method | Description |
| :--- | :--- | :--- | :--- |
| GET | `/saas/dashboard` | `Saas\Dashboard::index` | High-level metrics (Active Clinics, MRR, Recent Signups). |
| GET | `/saas/clinics` | `Saas\Clinics::index` | DataTables view of all `clinics`. |
| GET | `/saas/clinics/create` | `Saas\Clinics::create` | Wizard to provision new tenant (DB row + Owner User). |
| POST | `/saas/clinics/(:num)/suspend` | `Saas\Clinics::suspend` | Emergency "kill switch" for a tenant. |
| POST | `/saas/clinics/(:num)/impersonate` | `Saas\Clinics::impersonate` | Sets session context to this clinic + redirects to their dashboard. |
| GET | `/saas/plans` | `Saas\Plans::index` | Manage subscription tiers. |
| GET | `/saas/subscriptions` | `Saas\Subscriptions::index` | Global view of all active/churned subscriptions. |
