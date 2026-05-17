# Changelog

## 2026-05-17 - Appointment Calendar Workspace Phase 1A

- Documented the phased appointment calendar workspace plan in `docs/refactor/APPOINTMENT_CALENDAR_WORKSPACE_PLAN.md`.
- Kept `/appointment` as the current appointment workspace/default route for now because `/appointment/calendar` has a separate `appointments:calendar` permission.
- Started Phase 1 with `/appointment/calendar` readability improvements only:
  - Desktop defaults to week schedule view.
  - Mobile defaults to day schedule view.
  - Month view remains available for overview.
  - Calendar events render time, patient/type title, duration, status, and chair/provider context more clearly.
  - Calendar uses explicit RTL direction and locale configuration.
  - Business hours, now indicator, 15-minute slots, and 30-minute labels are configured.
  - Debug console logging was removed.
- Continued Phase 1 with calendar-local filtering:
  - Added patient/search, status, and appointment-type controls to `/appointment/calendar`.
  - Filtering uses the existing calendar feed client-side and does not change routes, controller contracts, database queries, or appointment write behavior.
- Started Phase 2 calendar create-flow integration:
  - Clicking an empty date/time slot now opens the existing appointment quick-create modal instead of leaving the calendar.
  - The existing appointment form, store endpoint, Select2/date conventions, CSRF, and `return_to` behavior remain authoritative.
  - Calendar slot date/time values are passed into the existing modal and appointment slot loader without adding new routes or duplicate forms.

## 2026-05-10 - Patient Drawer RTL and Select2 Polish

- Hardened Select2 field spacing in LTR/RTL so dropdown arrows and clear controls no longer overlap values such as `ILS` or Arabic placeholders.
- Kept drawer Select2 menus stacked above drawer content so dropdown menus do not render under later fields/helper text.
- Refined the drawer patient context badge to mirror cleanly in Arabic while keeping patient numbers and phone values isolated as LTR data.
- Removed redundant drawer context helper copy where the focused patient card already provides the context.
- Added male/female-aware patient badge tones, a reusable drawer toggle style for contextual treatment inventory usage, and a corrected treatment inventory remove button label/style.
- Documented the drawer RTL/Select2 rules in `docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` so future drawer work does not repeat this issue.

## 2026-05-09 - Unified Workflow Navigation Contract

- Milestone: unified workflow navigation contract
- Implementation commits:
  - `65067bb` Unify workflow navigation behavior
  - `b8f1ca5` Harden unified workflow navigation contract
- Problem fixed:
  - The app previously had competing navigation systems: session stack Back behavior and `return_to` post-submit redirects
  - Completed create/edit pages could remain in `nav_trail` and become stale Back targets
- Final contract:
  - `NavigationService` owns visible Back behavior
  - `return_to` remains post-submit/context redirect metadata
  - `create`, `edit`, `store`, `update`, `delete`, and `destroy` workflow URLs are rejected as Back targets
  - Workflow GET pages are skipped from visible nav stack tracking
  - Successful workflow POSTs go through `BaseController::redirectAfterWorkflow()`
  - `completeWorkflow()` is only called inside `BaseController::redirectAfterWorkflow()`
  - Views use `workflow_back()` / `workflow_cancel()`, not `service('navigation')->previous()`
- Verification summary:
  - `git status` clean
  - No direct `service('navigation')->previous()` calls
  - `completeWorkflow()` only inside `BaseController::redirectAfterWorkflow()`
  - Hidden `return_to` inputs preserved
  - Known out-of-scope visible `return_to` remains only in `app/Views/odontogram/index.php`
  - PHP syntax checks passed
  - `php spark routes` passed
  - `npm.cmd run build` passed with existing Browserslist warning only
  - `AppApiFoundationTest.php` passed
  - `AppApiPatientsTest.php` passed
  - Authenticated local HTTPS workflow smoke passed for appointment create, examination create, treatment create, finance create, prescription create, direct appointment create, edit cancel/link targets, and document upload endpoint
  - Created smoke rows were cleaned up with zero leftovers
- Caveat:
  - Playwright visual smoke could not run because the browser context closed
  - HTTP/session/rendered-link smoke passed and directly verified the workflow contract
  - Full visual browser smoke remains optional future QA, not a blocker for this architecture milestone
- Follow-up:
  - Create a separate future mini-slice for `app/Views/odontogram/index.php` workflow cleanup

## 2026-05-08 - Patient Contextual Drawer Milestone

- Completed the current-PHP Patient Profile contextual drawer milestone for:
  - Add examination drawer
  - New appointment drawer
  - Add treatment drawer
  - Record payment drawer
  - Add patient charge drawer
  - Add document drawer
- Implementation commits:
  - `58cdb2d0` Harden patient contextual drawers
  - `859fdcb6` Harden contextual drawer interactions
  - `d6c16441` Fix contextual appointment slot parsing
- Verification summary:
  - `node --check public/assets/js/contextual-workspace.js` passed
  - `npm.cmd run build` passed
  - `AppApiFoundationTest.php` passed
  - `AppApiPatientsTest.php` passed
  - All six drawer POST smoke tests passed on local/dev DB for patient `148` / clinic `19`
  - Duplicate drawer ID scan passed
  - Full create pages loaded cleanly: `/examination/create`, `/appointment/create`, `/treatment/create`, `/finance/create`
  - All smoke test rows, attachment row, uploaded smoke file, and activity logs were cleaned up
- Important implementation notes:
  - Select2 drawer initialization is deferred until drawer open
  - Drawer Select2 obtains the UI search Bearer token before `/api/v1/search/*` initialization
  - Appointment drawer supports numeric-key slot payloads from `/appointment/available-time-slots`
  - Pregnancy-only examination fields are hidden, unchecked, and disabled for non-female locked patients
  - Finance drawers use prefixed IDs and scoped calculators to prevent payment/charge collisions
  - Document drawer includes `return_to`
- Remaining non-blocking notes:
  - No app-wide drawer rollout is approved yet
  - This pattern must not be copied globally without a separate scoped plan
  - Future drawer conversions must include real POST smoke tests, not only visual checks

## 2026-05-06 - Patient Contextual Quick Actions

- Added a current-PHP Patient Profile command workspace with contextual drawers for examination, appointment, treatment, patient payment, patient charge, and document upload
- Reused existing canonical web routes/controllers for writes so CSRF, tenant checks, permissions, validation, activity logging, and redirects remain backend-owned
- Added implementation plan at `docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md`
- Kept `/app-api/v1/*`, `/api/v1/*`, React/Lovable frontend work, and module route ownership unchanged

## 2026-05-05 - Separate Lovable Repo Workflow

- Documented `dentacare-shell` as the separate Lovable UI/UX source lab
- Documented `frontend/` as a reviewed snapshot inside the CI4 backend repo, not a nested Git repo, submodule, or subtree
- Added import checklist, Lovable project instructions, dependency guardrails, and local build/deploy notes
- Kept App API wiring, backend routes/controllers/filters/models, `/api/v1/*`, and `public/.htaccess` unchanged

## 2026-05-05 - Lovable React Shell Import Stabilization

- Imported Lovable React/Vite shell under `frontend/` and kept source outside CodeIgniter `app/`, `public/`, and root `src/`
- Built static React output to `public/app/` with Vite base `/app/`
- Scoped Apache SPA fallback to `/app` and `/app/*` only, with real static files served directly
- Kept real App API wiring, Patients UI wiring, `/api/v1/*`, and existing PHP web controllers unchanged

## 2026-05-05 - App API Patients Slice 2

- Added `POST /app-api/v1/patients` and `PATCH /app-api/v1/patients/{id}` for first-party patient create/update
- Kept writes tenant-scoped, CSRF-protected, permission-gated, and serialized through explicit safe patient fields
- Kept `/api/v1/*`, existing web patient routes, frontend assets, and delete/workspace/finance/clinical patient endpoints unchanged
- Verified with `AppApiPatientsTest.php`: 25 tests, 111 assertions

## 2026-05-05 - App API Patients Slice 1

- Added read-only `/app-api/v1/patients` and `/app-api/v1/patients/{id}` endpoints
- Added App API scoped tenant, permission, and subscription filters for tenant resource routes
- Kept `/api/v1/*`, existing web patient routes, frontend assets, and patient write behavior unchanged
- Verified with `AppApiPatientsTest.php`: read/write suite currently 25 tests, 111 assertions

## 2026-05-05 - App API Foundation Hardening

- Added App API scoped session-auth and HTTPS JSON filters for `/app-api/v1/*`
- Removed App API-specific behavior from the shared web auth filter
- Kept `/api/v1/*` stateless API and existing web route filters unchanged
- Verified with `AppApiFoundationTest.php`: 19 tests, 66 assertions

## 2026-05-05 - App API Foundation

- Added first-party `/app-api/v1/*` foundation routes for CSRF, JSON login/logout, bootstrap, and clinic switch
- Kept `/api/v1/*` public/stateless contract unchanged
- Added App API scoped CSRF JSON failure handling for unsafe methods
- Bootstrap reports app state without requiring tenant context
- Clinic switch validates membership and regenerates the session before returning React-ready state
- Verified with `AppApiFoundationTest.php`: 19 tests, 66 assertions

Recent implementation history for work continuation. Full historical logs are archived in `docs/_archive/2026-05-02-docs-consolidation/legacy-root-logs/`.

## 2026-05-04 — Navigation Stack & Smart Back Buttons

- Session-based navigation stack (`NavigationService`) auto-tracks every page visit
- `nav_back($fallback)` helper: one-line replacement for `base_url()` in any view
- Breadcrumb bar between header and content (visible when 2+ levels deep)
- All hero header back buttons use stack: nav stack → return_to → default list
- Stripped ~100 `return_to` query params from view links (forms keep hidden inputs)
- Wired across 20+ views: users, inventory, roles, odontogram, patient, exam, treatment,
  prescription, finance
- Now `/providers → /users/27 → Back` correctly returns to `/providers`

## 2026-05-03/04 — Full UI/UX Redesign Pass

### Pages Redesigned
- **Login page**: native checkbox, password toggle, full RTL mirroring with `[dir="rtl"]` CSS
- **Providers page**: table density matching /patients, single-line rows, slim pills, centered cols
- **Inventory show**: merged 4 cards into 2-col layout, actions moved to header
- **Inventory create**: reorganized into 5 logical sections (Item → Quantity/Pricing → Supplier →
  Dates/Location → Notes), colored card backgrounds
- **Finance show**: hero header cloned from patient/user pattern with gradient + avatar + chips
- **Examination show**: gender-themed hero, cleaner exam-card with layered shadows, hover lift
- **Treatment show**: hero header matching app signature, 2-col layout
- **Prescription show**: hero header, medication table matching /patients DataTable style
- **Contract create**: slimmed cards, tighter tables, compact inputs, × remove buttons

### Cross-Cutting Improvements
- `/inventory/suppliers` retired, redirected to `/providers`
- All supplier links across inventory pages wired to `/providers`
- Login page RTL rewritten with CSS-driven `[dir="rtl"]` pattern
- Patient hero CSS classes standardized and shared across 5+ pages

## 2026-05-02 — Unified Providers Directory

- New `/providers` page combining inventory suppliers and clinical payout providers into one DataTables-backed directory
- Stat cards: Total Suppliers, Active Suppliers, Clinical Providers, Outstanding Balance
- Type filter: All / Suppliers / Providers
- Add Supplier modal reuses `POST /inventory/suppliers/quick-create`
- Sidebar "Providers" menu item (shown if `inventory:view` OR `finance:view`)
- Routes: `GET /providers`, `POST /providers/data`, `GET /providers/stats`
- In-memory merge of supplier and provider_payout data; no new tables or migrations

## 2026-04 — Finance: Provider Compensation & Supplier Payables

- Implemented provider payouts + provider payout payments
- Finance create/edit split controls, payout ledger page (`/finance/payouts`)
- Payout payment actions
- Finance stats: accrual and cash-aware payout metrics
- Supplier payables expansion: unpaid/partially paid expenses, settlement entries, A/P aging

## 2026-03-29 — Finance: Treasury Custody Layer

- Custody buckets: `safe_cash`, `bank`, `undeposited_checks`
- Transfer workspace: `/finance/transfers`
- Transfer types: `cash_deposit_to_bank`, `bank_withdrawal_to_safe`, `check_deposit_to_bank`, `internal_transfer`
- `/finance/reports` canonicalized as authoritative finance reporting surface
- Owner-facing 5 metrics: Money In, Money Out, Customers Owe Us, We Owe Others, Net Position

## 2026-03 — Finance: Payables & COGS

- Tenant finance supports `Add Expense` alongside `Add Income`
- Expense metadata: `expense_category`, `counterparty_name`
- Inventory COGS auto-posting: inventory usage can auto-post tenant finance `inventory_cogs` expenses
- Tenant setting `finance_auto_post_inventory_cogs` controls behavior

## 2026-03 — WhatsApp Phases 1-6 (Partial)

- Phases 1-5 completed: schema, unified dispatcher, outbound client, webhooks, UI/ops
- Phase 6 partial: template-first workflow enqueueing wired for appointment confirmations, queued reminders, follow-up, no-show recovery, invoice reminders
- Commands: `php spark whatsapp:queue-product-flows`, `php spark notifications:dispatch --channel whatsapp`
- Remaining Phase 6: alerting/runbook hardening, broader workflow coverage

## 2026-03 — Inventory Operations Refinement

- Tenant supplier directory + quick-add
- Stock movement ledger
- Receive-stock workflow
- Starter inventory templates/import
- Supplier-item mappings (SKU/pack/preferred reorder qty/last price)
- Reorder list with supplier contact shortcuts
- Global movement ledger page (`/inventory/movements`)
- Batch/lot tracking foundation (`inventory_batches`)

## 2026-03 — Control Plane Operations

- Ops console rebuilt (health checks + tooling)
- Plan enforcement table restyled
- Control Plane Invoices: delete action (draft/void/cancelled), modal confirm
- Control Plane Defaults: time format persistence fixed

## 2026-01 — SaaS Multi-Tenant Transition (COMPLETE)

- Phases 0-6 completed and verified
- RBAC consolidation, clinic context wall, schema tenancy
- TenantAwareModel fail-closed, private file storage
- Background jobs with tenant context
- API-B stateless integration API (`/api/v1/*`)

## 2026-01 — UI Hardening & Branding

- DOM-XSS remediation across all allowlisted views
- Branding centralization via settings injection
- Secure logo upload pipeline
- Print branding consistency
