# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DentaCare SaaS — multi-tenant dental clinic management on CodeIgniter 4 (PHP 8.1+) with MySQL 8.
Single codebase, path-based multi-tenancy: Control Plane at `/controlplane/*`, Tenant Plane
at all other authenticated routes. Tenant isolation via `clinic_id` on all tenant tables.

## Common Commands

```bash
# Development
php spark serve                         # Start dev server
php spark migrate                       # Run migrations
php spark migrate:rollback              # Rollback last migration
php spark key:generate                  # Generate encryption key
php spark rbac:sync                     # Sync permissions/roles from config to DB

# Testing
composer test                           # Run PHPUnit (all tests)
php vendor/bin/phpunit --filter=TestName # Run a single test

# Linting
vendor/bin/php-cs-fixer fix --diff      # PHP code style fix

# Frontend (Tailwind CSS)
npm install                             # Install npm deps
npm run build                           # Build Tailwind → public/assets/css/tailwind.css

# CI guardrails
bash scripts/ci/saas_guardrails.sh      # DOM sinks, auth helpers, raw queries, fonts

# WhatsApp ops
php spark whatsapp:ops-check            # Phase 6 readiness
php spark whatsapp:queue-product-flows  # Queue product reminder notifications
php spark notifications:dispatch --channel whatsapp --clinic-id <ID>

cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas > democa_dental_saas_schema_superadmin.sql"
```

## Architecture: Request Gating Chain

Every tenant-plane request passes through this filter chain in order:

```
AuthFilter → TenantFilter → SubscriptionFilter → PermissionFilter → Controller
```

1. **AuthFilter** (`app/Filters/AuthFilter.php`) — must be logged in via IonAuth. Redirects to
   `/clinic/select` if no `active_clinic_id` or `global_mode` is set.
2. **TenantFilter** (`app/Filters/TenantFilter.php`) — enforces `active_clinic_id` in session.
   If `global_mode=true`, tenant routes get **404** (the "airlock"). Validates user membership
   in `clinic_users`. Supports impersonation bypass for super admins.
3. **SubscriptionFilter** (`app/Filters/SubscriptionFilter.php`) — loads clinic subscription
   via `ClinicSubscriptionModel` (using `withoutTenantScope()` to escape tenant scoping for
   this cross-cutting check). Auto-expires past-end-date subscriptions. Invalid standing → 404.
4. **PermissionFilter** (`app/Filters/PermissionFilter.php`) — applied per-route as
   `['filter' => 'permission:module:action']`. Delegates to `PermissionService::hasPermission()`.

For API (`/api/v1/*`): `ApiV1AuthFilter` replaces the web chain. Bearer token + mandatory
`X-Clinic-Id` header. Sets `ApiRuntimeContext` static context for downstream tenant scoping.
No cookies, no redirects, no HTML — fail-closed JSON errors only.

## Tenant Isolation: TenantAwareModel

`app/Models/TenantAwareModel.php` is the base for all tenant-scoped models. It auto-appends
`WHERE clinic_id = ?` to every `find`, `update`, and `delete` query via model event callbacks.
The clinic_id value comes from `ApiRuntimeContext::getClinicId()` (API) or
`session()->get('active_clinic_id')` (web). Missing clinic_id → `PageNotFoundException` (404).

Insertions auto-set `clinic_id` server-side — never trust client payload.
Use `withoutTenantScope()` only in control-plane or cross-cutting code (subscription checks,
global settings, impersonation). This is logged and auditable.

## Session Model

Five session keys define the user's plane and context:

| Key | Purpose |
|-----|---------|
| `active_clinic_id` | Current clinic (tenant context). Required for tenant plane. |
| `global_mode` | Boolean. When `true`, user is in Control Plane. Tenant routes 404. |
| `is_impersonating` | Super admin impersonating a clinic. Bypasses membership checks. |
| `impersonated_clinic_id` | The clinic being impersonated. |
| `active_clinic_role_id` | User's role within the active clinic. Synced by TenantFilter. |

Plane separation rule: `global_mode=true` and `active_clinic_id` must never coexist on a
request. Control plane clears tenant context on entry. Tenant plane rejects global_mode
with 404. Session regenerates on clinic switch or impersonation changes.

## Permissions (RBAC)

Permissions are defined in `app/Config/Permissions.php` as `Module → [action → label]`.
11 modules with standard CRUD + module-specific actions. 8 default roles from `super_admin`
to `read_only`.

`PermissionService::hasPermission($userId, $module, $action)` resolves permissions by:
1. Super admin always passes
2. Check each "effective role" (global `user_roles` + clinic-specific `clinic_users.role_id`)
3. Check user-specific overrides in `user_permissions` table

Action aliasing: `view` ↔ `read`, `edit` ↔ `update` are interchangeable.

The `has_permission($module, $action)` helper (`app/Helpers/rbac_helper.php`) is the
view-layer function used in templates for conditional UI rendering.

## PlanGuard: Feature & Quota Gating

`app/Services/PlanGuard.php` enforces plan-based access in three dimensions:

- **Subscription standing** (`assertSubscriptionActive`): active status + within date window
- **Feature gates** (`assertFeature`): nested JSON keys in `plans.features_json`, e.g.
  `notifications.whatsapp.enabled`, `attachments.enabled`
- **Quota limits** (`assertQuota`): live-count (patients/users), storage (attachment size sum),
  monthly usage (notifications, exports). `-1` = unlimited. Exceeded → 429 for API.

Every enforcement writes to `plan_audit` for forensic traceability.

## Key Services

- **NotificationService** (`app/Services/NotificationService.php`): Governance layer — validates
  plan features, channel registry status, recipient ownership. Writes to `notifications` ledger.
  Does NOT send. Delegates dispatch to `DispatchService`.
- **DispatchService** (`app/Services/DispatchService.php`): Lock-based claim system for
  concurrent workers. Picks pending notifications, drives through channel-specific drivers
  (`EmailDriver`, `WhatsAppDriver`). Records attempts for retry/audit.
- **StorageService** (`app/Services/StorageService.php`): Tenant-isolated file storage under
  `WRITEPATH/uploads/clinic_{$clinicId}/`. Gated by plan features and storage quotas.
  Streaming access only — no public file URLs.

## Views & BaseController

All controllers extend `BaseController` (`app/Controllers/BaseController.php`). Its overridden
`view()` method auto-injects `user`, `user_groups`, `user_memberships`, `clinic`, `locale`,
and `is_rtl` into every rendered view. Always call `$this->view('path', $data)` rather than
the global `view()` helper.

The sidebar in `app/Views/layouts/main_auth.php` uses a `$menu` array + `$permissionMap`
array + render loop. Menu items are hidden if the user lacks the mapped permission. Active
state uses `strpos(uri_string(), $item['url']) === 0`. The `$permissionMap` supports OR
conditions via array-of-arrays format (used by the Providers menu item).

## Documentation

Active docs are listed in `docs/START_HERE.md` (13 files under `docs/`). Everything in
`docs/_archive/` is historical evidence — not source of truth. See `docs/OPERATIONS_RUNBOOK.md`
for the Docs QA commands (stale-reference scan, active-count check, replacement-character scan).
