# Laravel Backend Migration Study - Codex Assessment

Last updated: 2026-05-04

## 1. Executive Decision Summary

Migrating the backend from CodeIgniter 4 to Laravel is possible, but it is not a frontend refactor. It is a full backend platform migration involving authentication, tenant isolation, RBAC, finance calculations, inventory accounting, provider payouts, supplier payables, notifications, file storage, APIs, background jobs, control-plane operations, and deployment tooling.

The current application is not a simple CRUD app. It is a multi-tenant SaaS with strict fail-closed tenant isolation and several business-critical accounting/clinical workflows. A Laravel migration would provide a stronger long-term framework ecosystem, better conventions, better testing ergonomics, better AI/code-generation alignment, and more maintainable architecture. But the migration has high risk if attempted as a "rewrite everything" project.

Recommended position:

- Do not migrate to Laravel just to fix frontend/workflow UX.
- Do consider Laravel if the goal is a multi-year platform rebuild with cleaner backend architecture, better API contracts, better tests, and stronger ecosystem alignment.
- If done, migrate incrementally with a strangler strategy and contract tests, not a big-bang rewrite.
- Keep the current MySQL database initially; do not redesign the schema during the first Laravel cut.
- Build a complete behavioral test harness around the current CI4 backend before replacing any module.

Bottom-line recommendation:

**Frontend rebuild first, Laravel backend migration later only if justified by long-term platform maintenance.**

If Laravel migration is approved, the safest plan is:

1. Freeze current business rules in documentation and tests.
2. Build Laravel as a parallel backend.
3. Share the existing MySQL database initially.
4. Port auth/tenant/RBAC foundations first.
5. Port one bounded module at a time.
6. Keep CodeIgniter active until Laravel passes module-level parity tests.
7. Cut over only when finance, tenant isolation, file access, and control-plane behavior are proven.

## 2. External Compatibility Findings

### Hostinger

Hostinger currently documents support for Laravel 12 through its Auto Installer, with PHP 8.2 required. It also documents a cron template for `artisan schedule:run`, which matters for Laravel scheduler-based background tasks.

Important Hostinger implications:

- Laravel can run on Hostinger.
- The document root must point to Laravel's `public` directory or equivalent safe routing must be configured.
- `storage` and `bootstrap/cache` must be writable.
- Scheduled tasks can run via Hostinger cron.
- Long-running queue workers may be difficult on shared hosting unless the plan supports persistent processes. Database queues plus cron-driven `queue:work --stop-when-empty` may be more shared-hosting friendly than Redis/Horizon.

### Laravel Runtime

Laravel 12 requires PHP >= 8.2 and common PHP extensions including PDO, mbstring, OpenSSL, tokenizer, XML, fileinfo, cURL, DOM, and others.

Laravel deployment docs emphasize:

- Web server should route requests to `public/index.php`.
- Do not expose the project root to the public web.
- Production should run optimization/cache commands such as config/route/view caching.
- `APP_DEBUG` must be false in production.
- Long-running services need restart/reload handling after deploy.

### Laravel Features Relevant To This App

Laravel provides strong first-party support for:

- Routing and middleware
- Request validation through Form Requests
- Eloquent ORM and query builder
- Database migrations and seeders
- Gates and policies for authorization
- Queues with database/Redis/SQS/etc.
- Task scheduling
- Events and listeners
- Notifications
- File storage abstraction
- Localization
- HTTP/API tests
- Sanctum for SPA/session/API token auth patterns
- Vite frontend bundling

## 3. Current Application Context

Current backend:

- Framework: CodeIgniter 4
- Database: MySQL
- Auth: IonAuth
- Authorization: custom RBAC permissions, not group-based runtime checks
- Tenancy: shared database, tenant-scoped tables with `clinic_id`
- Tenant enforcement: filters + `TenantAwareModel` + guardrails
- Frontend: PHP views + Tailwind CSS + jQuery/vanilla JS
- Current phase: pre-alpha, active tracks include finance, inventory, provider payouts, supplier payables, WhatsApp operations

Major modules:

- Patient management
- Appointments
- Examinations
- Odontogram
- Treatments
- Treatment contracts and signatures
- Prescriptions
- Finance
- Supplier payables
- Provider payouts
- Treasury transfers
- Inventory
- Providers directory
- Reports
- WhatsApp / notifications
- Settings
- Users / roles / permissions
- Activity logs
- File attachments
- Kiosk
- SaaS control plane

Critical invariants:

- CSRF and secure headers globally enforced for web.
- `/api/v1/*` must never return redirects or HTML.
- Tenant routes require active clinic.
- Control plane requires global mode.
- Normal tenant users cannot access cross-clinic records.
- Tenant models must scope by `clinic_id`.
- File storage must remain private and streamed through authorized controllers.
- Finance formulas and provider/supplier distinctions must not regress.

## 4. Why Consider Laravel

### 4.1 Ecosystem And Hiring

Laravel has a much larger modern ecosystem than CodeIgniter for:

- AI-assisted code generation
- package availability
- common SaaS patterns
- testing examples
- queues/jobs
- mail/notifications
- API resources
- frontend integration
- developer onboarding

This matters because the project is increasingly complex. AI tools and future developers will generally produce better Laravel architecture than CodeIgniter 4 architecture because Laravel conventions are better known and more documented.

### 4.2 Better Application Structure

Laravel naturally supports a cleaner separation:

- Controllers
- Form Requests
- Models
- Policies
- Services
- Actions
- Jobs
- Events
- Notifications
- API Resources
- Middleware

The current CI4 app has grown large server-rendered views and controller-heavy workflows. Laravel would make it easier to enforce a domain/service layer and keep business logic out of views/controllers.

### 4.3 Stronger Testing Story

Laravel's HTTP tests, database testing, factories, seeders, and test helpers make it easier to write:

- tenant isolation tests
- permission tests
- finance calculation tests
- API contract tests
- regression tests for critical workflows

This is one of the strongest reasons to migrate. Without tests, the app will keep depending on manual UI verification.

### 4.4 Better Auth/API Options

Laravel can support:

- session-based SPA auth
- API token auth
- Sanctum-style first-party SPA auth
- custom guards
- policies/gates

This maps well to a future React frontend and mobile/API clients.

### 4.5 Better Queue/Scheduler Model

The app has WhatsApp, notifications, reports, backups, exports, and possible future recurring billing. Laravel's queue and scheduler model is a better long-term fit than ad hoc controller/CLI logic.

### 4.6 Better Long-Term Frontend Integration

Laravel works cleanly with:

- Blade
- Livewire
- Inertia
- React/Vue/Svelte
- Vite
- API-first frontend

If the long-term frontend is React, Laravel can be either:

- a JSON API backend only, or
- an Inertia/React app in one repository.

## 5. Why Not Migrate Yet

### 5.1 It Does Not Directly Fix UX

The user pain is primarily workflow/UI fragmentation. Laravel will not automatically fix that. A bad Laravel frontend can be just as bad as a bad CodeIgniter frontend.

If the immediate goal is better UI/UX, Lovable + React frontend over existing backend is faster and lower risk.

### 5.2 High Risk Of Business Logic Regression

The app includes sensitive logic:

- tenant isolation
- subscription standing
- RBAC
- provider payouts
- supplier payables
- treasury custody
- inventory COGS
- receivables/payables reports
- WhatsApp opt-out/delivery policy
- private files
- control-plane impersonation

A rewrite can easily change behavior even when screens look correct.

### 5.3 Existing Database Is Not Laravel-Native

Laravel's Eloquent conventions prefer:

- `id` primary keys
- `created_at` / `updated_at`
- conventional table names
- single-column primary keys
- explicit model relationships

The current schema mostly can work, but every model must be audited for:

- table name
- primary key
- timestamps
- casts
- fillable/guarded
- relationships
- tenant scope
- soft delete behavior
- enum/string status values

Laravel does not support composite primary keys in Eloquent models. If any existing table relies on composite identity rather than a single `id`, that table needs special handling with query builder, pivot models, or schema adjustment.

### 5.4 Auth Migration Is Not Trivial

The current app uses IonAuth tables and behaviors. Laravel's auth stack is different.

Options:

1. Keep existing `users` table and password hashes, implement custom Laravel auth provider.
2. Migrate passwords/users into Laravel-native auth tables.
3. Force password reset for all users.

Option 1 is least disruptive but requires careful implementation. Option 2 is cleaner but higher risk. Option 3 is operationally simple but bad UX.

### 5.5 Shared Hosting Limits

Laravel itself can run on Hostinger, but production-grade Laravel often benefits from:

- queue workers
- Redis
- process monitoring
- deployment hooks
- worker restarts
- object storage
- cache store

On shared hosting, these can be limited. The app can still run with database queues and cron, but that is less robust than a VPS/Forge/Laravel Cloud style environment.

## 6. Migration Benefits By Area

| Area | Benefit |
|---|---|
| Architecture | Strong conventions for services, requests, policies, jobs, resources |
| Testing | Better test ergonomics and factories |
| API | Cleaner resource serialization and error normalization |
| Auth | Sanctum/custom guards/policies ecosystem |
| Authorization | Gates and policies map well to resource authorization |
| Jobs | First-class queues and scheduler |
| Notifications | Strong notification/mail abstractions |
| File storage | Storage facade supports local/S3-like abstraction |
| Localization | Built-in file/JSON translation patterns |
| AI tooling | AI agents generally know Laravel better |
| Maintainability | Easier for future developers to join |
| Hiring | Laravel talent pool larger |
| Frontend | Strong Vite/Inertia/React integration |

## 7. Migration Disadvantages By Area

| Area | Risk / Cost |
|---|---|
| Timeline | Large rewrite measured in months, not days |
| Regression | Finance/tenant/security behavior can subtly break |
| Auth | IonAuth to Laravel auth requires careful bridge |
| Tenancy | TenantAwareModel must be recreated with global scopes/middleware |
| Database | Existing schema may not match Eloquent conventions |
| Hosting | Shared hosting limits queue workers/process monitoring |
| Team focus | Diverts from current product/UI work |
| Documentation | Every invariant must be ported and re-proven |
| Deployment | New directory structure, cache, storage permissions, cron |
| Parallel systems | Temporary CI4+Laravel coexistence increases complexity |

## 8. Mapping Current Concepts To Laravel

| Current CI4 Concept | Laravel Equivalent |
|---|---|
| Routes.php groups/filters | `routes/web.php`, `routes/api.php`, middleware groups |
| Filters | Middleware |
| BaseController | Base controller + middleware + service injection |
| TenantAwareModel | Eloquent base model + global scope + observers |
| PermissionService | Gates, policies, custom permission service |
| IonAuth | Laravel auth/custom provider/Sanctum/Fortify |
| CI validation | Form Request validation |
| CI Models | Eloquent models / query builder repositories |
| Views | Blade, Inertia/React, or API-only frontend |
| Spark commands | Artisan commands |
| Notifications controllers | Laravel Notifications + jobs |
| FileController streaming | Storage facade + signed/authorized download routes |
| Activity logs | Model observers + events + activity service |
| PlanGuard | Middleware + subscription service |
| API v1 filter | API middleware stack |

## 9. Tenant Isolation Design In Laravel

This is the most important part of the migration.

Required Laravel components:

- `TenantContext` service
- `SetTenantContext` middleware
- `RejectTenantWithoutClinic` middleware
- `RejectGlobalModeInTenantPlane` middleware
- `RequireGlobalModeForControlPlane` middleware
- `TenantScopedModel` base model
- `ClinicScope` global Eloquent scope
- `BelongsToClinic` trait
- `TenantViolationException`
- tenant-aware route model binding
- tests for every tenant-owned model

Rules:

- Tenant context must be server-controlled.
- Tenant route must fail if clinic context is missing.
- Control-plane route must fail if clinic context is active.
- Inserts must force `clinic_id` server-side.
- Select/update/delete must include `clinic_id`.
- Route model binding must never resolve a model from another clinic.
- Cross-tenant HTML access returns 404.
- API v1 tenant failures follow API contract.

Example Laravel design:

```php
abstract class TenantScopedModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new ClinicScope);

        static::creating(function (Model $model) {
            $model->clinic_id = app(TenantContext::class)->clinicIdOrFail();
        });
    }
}
```

This is conceptually strong, but it must be tested aggressively because Eloquent global scopes can be bypassed intentionally with `withoutGlobalScope()`.

## 10. Auth And RBAC Migration Plan

### Current Requirements

- IonAuth handles identity.
- Runtime authorization must use permissions, not groups.
- Admin is a permission, not a group.
- Clinic membership is separate from identity.

### Laravel Target

- `User` model implements Laravel `Authenticatable`.
- Existing `users` table may be reused.
- Password hash compatibility must be verified.
- Clinic memberships use `clinic_users`.
- Roles and permissions remain explicit tables.
- Policies call a central permission service.

Recommended first version:

- Preserve existing `users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `user_permissions`, `clinic_users`.
- Implement custom Laravel auth provider if needed.
- Add `PermissionService` equivalent.
- Use policies/gates as wrappers around the permission service.
- Keep group tables only for legacy compatibility; do not use them for runtime permissions.

Avoid:

- Replacing RBAC with default Laravel roles without mapping.
- Treating super admin as a group shortcut.
- Removing `clinic_users` membership logic.

## 11. API Strategy

If migrating backend, API-first is preferred.

Target endpoint behavior:

- `/api/v1/*` remains stateless.
- Requires `Authorization: Bearer <token>`.
- Requires `X-Clinic-Id`.
- No redirects or HTML.
- JSON error format preserved.
- Backend remains source of truth for validation, permissions, and finance calculations.

Laravel implementation:

- `routes/api.php`
- `ApiV1Auth` middleware
- `TenantHeaderMiddleware`
- `SubscriptionStandingMiddleware`
- `PermissionMiddleware`
- API Resources for serialization
- Form Requests for validation
- custom exception renderer for canonical error JSON

Important:

Do not let Laravel's default exception pages leak into API responses.

## 12. Module Migration Assessment

### 12.1 Dashboard

Risk: low-medium.

Mostly read-side aggregation, but must preserve permission and clinic scope.

Good first tenant module after foundation.

### 12.2 Patients

Risk: medium.

Includes patient profile, attachments, WhatsApp identity, odontogram links, finance links, and clinical timeline.

Migration requirements:

- Patient model
- tenant scope
- file attachment authorization
- DataTables replacement/API pagination
- medical alerts
- WhatsApp identity

### 12.3 Appointments

Risk: medium.

Requires calendar UX, duration rules, working hours, staff/chair context.

Migration requirements:

- appointment validation
- working-hours integration
- conflict detection if implemented
- duration options up to 180 minutes
- calendar feed API

### 12.4 Examinations / Odontogram

Risk: high.

Clinical state is complex and currently has multiple charting concepts.

Migration should wait until clinical product concept is clarified:

- patient baseline odontogram
- examination-scoped odontogram
- contract snapshot
- future longitudinal chart history

### 12.5 Treatments / Contracts / Signatures

Risk: high.

Contracts freeze financial/clinical state. Signatures and snapshots must remain legally/audit meaningful.

Migration requirements:

- immutable contract snapshots
- signature capture/storage
- itemized treatment lists
- print/export parity
- no accidental recomputation of frozen contract data

### 12.6 Prescriptions

Risk: medium.

Simpler than odontogram/contracts but still clinical and printable.

### 12.7 Finance

Risk: very high.

This is the most dangerous module to migrate.

Must preserve:

- income/payment/expense distinction
- partial payments
- refunds
- exchange rates
- tax/discount
- supplier payable balances
- provider payout accruals
- provider payout payments
- treasury custody buckets
- reports formulas
- date basis
- inventory COGS source references

Do not migrate finance until extensive characterization tests exist.

### 12.8 Inventory

Risk: high.

Inventory is tied to finance through COGS. Supplier links, batch tracking, movement ledger, usage, reorder list, and receive-stock workflow must remain consistent.

### 12.9 Providers

Risk: medium-high.

Providers combine:

- suppliers/vendors
- clinic users/clinical providers
- guest providers

Must avoid confusing supplier payables with clinical provider payouts.

### 12.10 Reports

Risk: high.

Reports are only as correct as underlying finance/clinical/inventory semantics. Migrate after source modules.

### 12.11 WhatsApp / Notifications

Risk: high.

Background jobs, opt-outs, provider facts, templates, audit, rate limits, and webhook ingestion matter. Laravel queues would help, but behavior must be ported carefully.

### 12.12 Settings

Risk: medium-high.

Settings affect global behavior: working hours, channels, security, kiosk, retention, defaults, language.

### 12.13 Users / Roles

Risk: high.

Auth/RBAC mistakes are security issues. Migrate early only as part of foundation, not as casual CRUD.

### 12.14 Control Plane

Risk: high.

Control plane has strict airlock separation. It should be migrated after tenant foundation is proven.

## 13. Database Migration Strategy

### Option A - Keep Existing MySQL Schema Initially

Recommended.

Benefits:

- No data migration at the same time as code migration.
- Easier parallel CI4/Laravel operation.
- Lower deployment risk.
- Existing Hostinger database remains usable.

Costs:

- Eloquent models need explicit table/cast/timestamp config.
- Legacy column names remain.
- Some schema awkwardness persists.

### Option B - Redesign Schema During Laravel Migration

Not recommended for first migration.

Benefits:

- Cleaner Laravel-native schema.
- Chance to fix old design decisions.

Costs:

- Massive data migration.
- Harder parity testing.
- Higher risk of accounting/clinical data corruption.
- Harder rollback.

Recommendation:

Use Option A. After Laravel is stable, consider schema refactors one bounded area at a time.

## 14. Deployment Strategy

### Hostinger Shared/Business Hosting

Can run Laravel if PHP/ext requirements are met.

Required:

- PHP 8.2 for Laravel 12 per Hostinger documentation.
- Correct document root or `.htaccess` routing to Laravel `public`.
- Writable `storage` and `bootstrap/cache`.
- Composer install/build performed locally or on server.
- Cron entry for `php artisan schedule:run`.
- Production commands: `php artisan optimize`, `config:cache`, `route:cache`, `view:cache` where compatible.

Queue strategy on shared hosting:

- Prefer `database` queue initially.
- Use cron to run `php artisan queue:work --stop-when-empty` frequently if persistent workers are not supported.
- Avoid Redis/Horizon unless hosting supports long-running workers/Redis.

### VPS / Forge / Laravel Cloud

Better long-term for:

- persistent queue workers
- Redis
- Horizon
- supervisor/process monitoring
- zero-downtime deploys
- object storage
- observability

If the app becomes production-grade SaaS, VPS/Forge/Laravel Cloud is operationally cleaner than shared hosting.

## 15. Testing Plan Before Migration

Before porting modules, create characterization tests against current behavior.

Required test categories:

- Tenant isolation
- Control-plane airlock
- Auth and clinic selection
- RBAC permissions
- Patient CRUD and attachments
- Appointment creation/duration/working hours
- Examination creation/update
- Odontogram state persistence
- Treatment plan/contract snapshot
- Prescription lifecycle
- Finance income/payment/refund
- Expense partial payment
- Supplier payable aging
- Provider payout accrual/payment
- Treasury transfer custody effects
- Inventory receive/usage/COGS
- Reports formulas
- WhatsApp opt-out and webhook behavior
- File access authorization

Minimum acceptable migration rule:

No module is considered migrated until Laravel passes the same contract tests that CI4 passes.

## 16. Recommended Migration Phases

### Phase 0 - Decision Gate

Decide whether Laravel migration is actually needed.

Proceed only if at least two are true:

- Need long-term backend maintainability beyond CI4.
- Need stronger automated testing and domain architecture.
- Need better Laravel ecosystem/packages.
- Need API-first platform for React/mobile.
- Team is willing to spend months on migration.

If only UI/UX is the problem, do not proceed.

### Phase 1 - Documentation And Contract Freeze

Outputs:

- API contract freeze
- finance formula freeze
- tenant isolation contract freeze
- RBAC contract freeze
- database table inventory
- route inventory
- file storage contract
- current behavior test checklist

### Phase 2 - Laravel Skeleton

Create new Laravel app in parallel, not inside current CI4 runtime.

Set up:

- PHP version
- Composer dependencies
- env config
- MySQL connection
- base health route
- logging
- test framework
- Pint/static analysis
- CI commands

### Phase 3 - Foundation Port

Implement:

- User auth bridge
- clinic membership
- tenant context
- control-plane context
- permission service
- middleware
- base tenant model
- API error contract
- audit context
- file storage service

No business modules yet.

### Phase 4 - Read-Only Shadow APIs

Build read-only endpoints for:

- dashboard
- patients list/profile
- appointments list
- finance summary
- inventory list

Compare responses with CI4.

### Phase 5 - Low-Risk Write Modules

Migrate bounded writes:

- profile/settings subset
- simple patient fields
- simple appointment create/edit

Keep CI4 fallback.

### Phase 6 - Clinical Modules

Migrate:

- examinations
- odontogram
- treatments
- contracts
- prescriptions

Only after clinical concept cleanup.

### Phase 7 - Finance And Inventory

Migrate last among tenant modules.

Order:

1. finance read models
2. finance transaction detail
3. payment/refund writes
4. provider payout reads
5. provider payout payments
6. supplier payable reads
7. supplier payable payments
8. inventory receive/usage
9. COGS posting
10. reports

### Phase 8 - Notifications / WhatsApp

Migrate jobs, webhooks, notification ledger, provider policies, opt-outs.

### Phase 9 - Control Plane

Migrate after tenant plane is stable.

### Phase 10 - Cutover And Decommission

Cut over module by module.

Do not delete CI4 until:

- Laravel has full route parity or accepted replacement routes.
- finance reports match.
- tenant isolation tests pass.
- backup/restore process is proven.
- deployment rollback is tested.

## 17. Parallel Operation Options

### Option 1 - Laravel As New API Backend Beside CI4

Use subdomain or path:

- CI4: `/dental-saas`
- Laravel: `/api2` or `laravel-api.palsync.net`

Pros:

- safest
- easy rollback
- module-by-module testing

Cons:

- two apps during migration
- shared auth/session complexity

### Option 2 - Laravel Replaces CI4 All At Once

Not recommended.

Pros:

- cleaner final architecture quickly

Cons:

- high risk
- hard rollback
- finance/security regression risk

### Option 3 - Laravel Shell Proxying To CI4

Laravel becomes the entrypoint and proxies unmigrated routes to CI4.

Pros:

- one public surface
- gradual migration

Cons:

- complex routing/proxy behavior
- session/auth coupling
- Hostinger shared hosting may make this awkward

Recommendation:

Use Option 1 for safety.

## 18. AI / Lovable / Claude Implementation Strategy

AI tools should not be asked to "rewrite backend from CI4 to Laravel" in one prompt.

Use bounded tasks:

- Create Laravel project skeleton.
- Create tenant context middleware.
- Port Patient model read-only.
- Create API resource for patient profile.
- Write parity test for finance formula X.
- Port provider payout payment endpoint.

Every task must include:

- source CI4 files
- target Laravel files
- expected behavior
- tests to run
- forbidden changes

Prompt shape:

```text
Port only this bounded behavior from CodeIgniter to Laravel.
Do not redesign the database.
Do not change business rules.
Use the existing MySQL table names and columns.
Preserve tenant isolation.
Write tests proving parity with the documented behavior.
List all assumptions and gaps.
```

## 19. Decision Matrix

| Scenario | Recommendation |
|---|---|
| Need better UI/workflow now | Build React/Lovable frontend, keep CI4 backend |
| Need mobile/API clients soon | Expand CI4 `/api/v1` first; consider Laravel later |
| Need long-term backend maintainability | Plan Laravel migration |
| Need production SaaS hardening | Laravel helps, but tests/ops matter more |
| Need quick launch | Do not migrate backend |
| Need clean architecture over 6-12 months | Laravel migration can be justified |
| Hosting must remain low-cost shared hosting | CI4 or Laravel both possible, but Laravel queues are limited |
| Finance correctness is not fully tested | Do not migrate finance yet |

## 20. Cost / Timeline Estimate

Rough order-of-magnitude if done properly:

- Study + contracts + tests: 2-4 weeks
- Laravel skeleton + foundation: 2-4 weeks
- Patients/appointments: 2-4 weeks
- Clinical modules: 4-8 weeks
- Finance/inventory: 6-12 weeks
- WhatsApp/notifications: 3-6 weeks
- Control plane: 3-6 weeks
- Cutover/hardening: 2-4 weeks

Realistic total:

- Small team / AI-assisted but carefully reviewed: 4-8 months.
- Aggressive prototype without full parity: 6-10 weeks, but not safe for production.

## 21. What Would Make Migration Worth It

Laravel migration becomes worth it if:

- The product is expected to live and grow for years.
- More developers/AI agents will work on it.
- You want cleaner API-first architecture.
- You want proper test coverage.
- You want queues/jobs/notifications/reporting to become first-class.
- You want easier package integration.
- You are willing to delay feature work for platform cleanup.

It is not worth it if:

- The main pain is only current UI.
- You need fast launch.
- Finance and tenant behavior are not yet documented/tested.
- Shared hosting limitations cannot be changed and background jobs are important.

## 22. Required Deliverables Before Approval

Before approving actual migration, create:

- `docs/refactor/LARAVEL_MIGRATION_DECISION.md`
- route inventory
- table/model inventory
- auth migration plan
- tenant isolation test plan
- finance parity test plan
- deployment plan for Hostinger
- rollback plan
- module-by-module cutover checklist

Approval should explicitly choose:

- Laravel version target
- hosting target
- auth strategy
- API-first vs Inertia/Blade
- shared database vs migrated schema
- queue strategy
- cutover style

## 23. Recommended Final Architecture If Migrated

Preferred long-term:

- Laravel 12 or current Hostinger-supported Laravel version
- MySQL
- React frontend generated/refined through Lovable
- API-first backend
- Sanctum/session auth for first-party SPA or custom token auth for API clients
- Laravel policies backed by explicit permission service
- Eloquent tenant global scopes plus middleware
- database queue initially, Redis queue later if hosting allows
- private file storage through authorized routes
- Laravel scheduler for periodic tasks
- extensive HTTP and database tests

Avoid:

- Big-bang rewrite.
- Schema redesign during first migration.
- Finance rewrite without tests.
- Supabase replacement.
- Group-based authorization.
- Frontend-only permission checks.

## 24. Final Recommendation

For this project, Laravel is a strong long-term backend target but a poor short-term fix.

Recommended sequence:

1. Rebuild frontend/workflows with Lovable/React against current backend or mock API.
2. Expand CodeIgniter JSON APIs as needed.
3. Add characterization tests around current critical behavior.
4. Reassess Laravel migration after frontend direction stabilizes.
5. If still desired, migrate backend incrementally using the phases in this report.

This avoids the worst failure mode: spending months on a backend rewrite while the user-facing workflow remains unresolved.

## 25. References Checked

- Hostinger Laravel support: `https://www.hostinger.com/support/1583301-which-laravel-versions-are-supported-at-hostinger/`
- Laravel deployment: `https://laravel.com/docs/12.x/deployment`
- Laravel frontend/Vite: `https://laravel.com/docs/12.x/frontend`
- Laravel Eloquent: `https://laravel.com/docs/12.x/eloquent`
- Laravel migrations: `https://laravel.com/docs/12.x/migrations`
- Laravel authorization: `https://laravel.com/docs/12.x/authorization`
- Laravel queues: `https://laravel.com/docs/12.x/queues`
- Laravel scheduling: `https://laravel.com/docs/12.x/scheduling`
- Laravel localization: `https://laravel.com/docs/12.x/localization`
- Laravel Sanctum reference: `https://readouble.com/laravel/12.x/en/sanctum.html`

