# Changelog

## 2026-07-26 - Odontogram workflow relationships

- Kept the odontogram as the fast visual clinical index while preserving canonical ownership in examinations, treatments, contracts, appointments, attachments, and finance.
- Examination print and examination-linked treatment contracts now use the effective visit chart (baseline plus visit overrides).
- Added context-preserving odontogram actions for treatment creation and treatment-plan creation.
- Treatment creation can prefill a validated tooth number from chart context.
- Clarified legacy odontogram treatment note/status fields as tooth follow-up metadata, not procedures or billable events.
- Expanded `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` with the durable relationship contract and fast doctor workflow.

## 2026-07-26 - Patient clinical workflow and visit-chart integrity

- Added the active patient clinical workflow contract covering preparation, examination, visit charting, treatment, completion, checkout, exceptions, and deferred capabilities.
- Fixed examination create/edit save-and-chart redirects and examination list/detail chart actions so they retain `examination_id`.
- Visit odontograms now show patient baseline conditions with examination tooth overrides layered on top, without copying or mutating baseline rows.
- Added clear patient-chart versus visit-chart context labels in English and Arabic.
- Added Open visit chart as a primary examination action.
- Added a database-backed regression test proving visit overrides win while untouched baseline rows remain intact.

## 2026-07-26 - Finance workflow simplification

- Simplified the daily finance workspace around three clinic jobs: Record payment, Create invoice, and New expense.
- Kept payables, provider payouts, treasury transfers, and reports available as secondary specialist finance areas.
- Added progressive disclosure to the shared income and expense quick-create forms without changing field names, routes, validation, calculations, permissions, or POST behavior.
- Exchange-rate and converted-amount fields now appear only for non-base currency; paid and due dates follow transaction state; linked records and narrative fields live under one optional-details control.
- Simplified patient finance actions to Record payment and Create invoice while retaining one ledger and a collapsed money-trail drill-down.
- Reduced repeated guidance on payables, payouts, transfers, clinical charge selection, provider share, and treatment billing handoffs.
- Preserved backend ownership of balances, taxes, discounts, allocations, provider compensation, treasury custody, inventory COGS, tenant scope, and audit history.
- Deferred consolidation of legacy full-page finance forms into shared partials to a dedicated compatibility-tested cleanup slice.

## 2026-07-26 - Finance Workflow Simplification Phase 1

- Added the active finance and clinical workflow simplification plan at `docs/refactor/FINANCE_WORKFLOW_SIMPLIFICATION_PLAN.md`, grounded in the current charge-line, ledger, treasury, payable, payout, and contextual-workspace architecture.
- Replaced the generic Finance `Add Income` entry point with explicit `Record Payment` and `Create Invoice` actions that reuse the existing canonical modal and preselect the correct transaction type.
- Kept `New Expense` as the clinic spending action and moved supplier bills, provider payments, and money moves into a secondary finance-area navigation strip.
- Reduced repeated summary-card explanations and decorative status clutter while preserving all values, routes, permissions, forms, backend calculations, and audit behavior.
- Finance quick-create modals now start fresh on normal action clicks while preserving submitted values when reopened after server validation errors.

## 2026-06-25 - App-Wide Icon Font Hardening

- Fixed the app-wide icon rendering issue after the first Font Awesome vendoring pass proved incomplete.
- Root causes:
  - Several standalone entry pages still loaded Font Awesome from CDN instead of the local vendored asset.
  - Some views used Font Awesome class names that are not present in the vendored Font Awesome Free 6.4.0 map, so those glyphs rendered blank even when the font loaded.
  - App font inheritance rules needed to protect Font Awesome pseudo-elements because the glyph is rendered through `::before`.
- Final contract:
  - All CodeIgniter entry views must load Font Awesome from `public/assets/vendor/fontawesome/css/all.min.css`.
  - Do not add CDN Font Awesome links.
  - Do not use icon names unless they exist in the vendored CSS map.
  - Layout-level icon font guards must include `.fa::before`, `.fas::before`, `.far::before`, and `.fab::before` so app typography rules cannot override glyph rendering.
- Corrected unsupported icon names:
  - `fa-calendar-edit` -> `fa-calendar-alt`
  - `fa-calendar-range` -> `fa-calendar-days`
  - `fa-timer` -> `fa-clock`
  - `fa-shield-check` -> `fa-shield-alt`
- Verification:
  - No remaining `cdnjs.cloudflare.com/ajax/libs/font-awesome` references in `app/Views` or `public/assets`.
  - App-wide Font Awesome class scan found no unsupported icon classes.
  - PHP syntax checks passed for touched views/layouts.
  - `php spark routes` passed.
  - `AppApiFoundationTest.php` passed.
  - `AppApiPatientsTest.php` passed.

## 2026-06-19 - Tenant App Sidebar and Header Frame Refresh

- Refined the tenant app `layouts/main_auth` sidebar/header combo into a single app-frame system.
- Reworked the sidebar as the primary navigation rail with a stronger blue/slate surface, clearer active state, consistent icon treatment, and internal scrolling on desktop.
- Simplified the app header into a quieter sticky utility bar with one shared button style for quick actions, notifications, clinic switcher, user menu, logout, and sidebar control.
- Removed the duplicate sidebar collapse control from inside the sidebar; the header menu button is now the single control for desktop collapse and mobile drawer open/close.
- Preserved route URLs, permission filtering, language switching, clinic switching, quick-create modal triggers, notification behavior, and user menu behavior.
- Expanded parent-route highlighting so singular/detail pages such as `patient`, `examination`, `appointment`, `treatment`, and `prescription` highlight their canonical sidebar module.
- Verification: `php -l app\Views\layouts\main_auth.php`, `npm.cmd run build`, `AppApiFoundationTest.php`, and `AppApiPatientsTest.php` passed. Build still reports the existing Browserslist warning.

## 2026-06-06 - Entity Detail Page Visual Contract

- Refined `/treatment/{id}` into the app's reference entity detail page layout: stable hero identity, operational snapshot strip, primary clinical content column, and contextual patient/billing/timeline sidebar.
- Added the reusable entity detail page visual contract to `docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` so future detail pages follow the same hierarchy, stronger domain colors, readable contrast, and workflow-focus rules.
- Preserved existing treatment actions, invoice drawer, permissions, workflow navigation, and backend write behavior.

## 2026-05-31 - Clinical-Finance Trace and Workflow-Focus Follow-Up

- Continued the clinical-finance connection work across treatment, patient, finance, inventory/material cost, and provider payout surfaces.
- Recent commits covered patient clinical billing links, treatment invoice/payment visibility, captured clinical charge lines, invoice creation from clinical work, appointment-to-clinical finance trace, provider payout trace, material cost trace on finance records, and provider payout payment workflow polish.
- Added a deferred manual QA reminder in `docs/PROJECT_STATE.md` because full end-to-end testing is postponed: treatment -> invoice -> patient payment -> patient profile trace -> treatment trace -> finance trace -> provider payout payment -> provider/user payout ledger.
- Tightened workflow-focus entry points so Dashboard quick actions and Examination Calendar new-examination action open existing quick-create modals instead of navigating away.
- Preserved canonical full-page create links where they are intentional fallbacks or where a safe shared modal is not loaded yet.
- Continued with patient-row contextual prefill: `/patients` New Examination row actions now open the existing examination quick-create modal with the selected patient injected into the canonical patient Select2 field, while preserving the full-page fallback link.
- Continued with patient-row contextual prefill: `/patients` New Treatment row actions now open the existing treatment quick-create modal with the selected patient injected into the canonical patient Select2 field; the modal resets on close so later row actions start fresh.
- Fixed the `/patients` row workflow gap by loading the existing examination quick-create modal on the page, so row New Examination actions can stay in context instead of falling back to the full create page.
- Continued the treatment workflow-focus slice: `/treatment/{id}` Create invoice from this treatment now opens an app drawer with the canonical finance form partial, selected patient, selected clinical charge line, CSRF, and full-page fallback preserved.
- Added the follow-up workflow navigation cleanup: visible action links and JS-generated create/edit/show links now use `workflow_context_url()` or the shared `WorkflowNavigationContext` helper instead of hardcoded `return_to=patients`, `return_to=finance`, `return_to=inventory`, `return_to=users`, or `return_to=appointment/calendar` destinations.
- Preserved hidden `return_to` fields, language-switch `return_to`, modal fallback metadata, and controller sanitization because those remain the secure post-submit/context redirect contract.
- Added shared modal/context helpers (`app-modal.js`, `navigation-context.js`) so existing quick-create popups can reset/close consistently and fallback links can inherit the current list, tab, filter, or show-page context without duplicating page-specific return logic.
- Known follow-up: consider scoped slices for dashboard finance quick-create, supplier/payable expense quick-create, and patient-detail item actions only after their shared modal/drawer data requirements are made safe.

## 2026-05-20 - Clinical-Finance Money Trail Slice 1

- Added a read-only `MoneyTrailService` that traces existing finance rows, settlement payments, provider payout rows, and inventory usage costs without changing write behavior or schema.
- Added a reusable Money Trail panel to patient financials, examination detail, and treatment detail pages.
- Kept the UI intentionally simple for clinic managers: charged, paid, balance due, provider remaining, material cost, and linked records.
- No `/api/v1/*`, `/app-api/v1/*`, React/Lovable, schema, or finance calculation changes were made in this slice.

## 2026-05-20 - Clinical Charge Line Foundation Slice 2A

- Added the `clinical_charge_lines` migration as the canonical future bridge between clinical work and finance.
- Added `ClinicalChargeLineModel` with tenant scoping, amount normalization, lifecycle status validation, and patient/examination/treatment query helpers.
- Extended Money Trail to read charge lines when the table exists and display captured clinical work separately from invoiced finance rows.
- Kept invoice/payment/provider-payout behavior unchanged; automatic clinical charge creation, invoice line items, and payment allocation remain future slices.
- Deployment note: run `php spark migrate` after pulling this slice so `clinical_charge_lines` exists before future charge-capture work.

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
- Continued Phase 2 with calendar appointment click behavior:
  - Clicking an existing appointment now opens a calendar-local detail modal instead of immediately leaving `/appointment/calendar`.
  - The detail modal links to the canonical appointment show/edit pages and patient page, preserving canonical module ownership.
  - The existing appointment show page already uses `nav_back()`, which delegates to `NavigationService::previousSafe()` for context-aware Back behavior.
- Started appointment list/workspace refinement:
  - `/appointment` now has a compact appointment summary strip using loaded appointment data.
  - New Appointment entry points on the page open the existing appointment quick-create modal.
  - Daily schedule period counts now use real loaded rows instead of placeholder random values.
  - Appointment cards and filters were tightened for a denser appointment operations view.

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
## 2026-05-19 - Clinical-Finance Traceability Planning

- Audited current clinical/finance linkage across active docs, models, controllers, and local schema
- Confirmed existing strengths: patient/examination-linked finance rows, invoice settlement rows, provider payout rows, inventory COGS source links, and treatment contract item pricing
- Documented the main architecture gap: no canonical clinical charge line / invoice line / payment allocation layer yet
- Added `docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` with phased plan for charge capture, patient ledger, invoice lines, payment allocations, provider production/collections, payout reconciliation, and future insurance/accounting integration
- Added explicit simplicity rule: daily UX should show money trail, charges, payments, balance due, provider share, and linked clinical records without accounting jargon
- Updated active docs map, finance current state, clinical current state, and decision log
