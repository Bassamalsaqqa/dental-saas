# Lovable Frontend Rebuild Strategy - Codex Study

Last updated: 2026-05-05

## 1. Executive Summary

The current application is a multi-tenant dental clinic SaaS built on CodeIgniter 4, PHP, MySQL, IonAuth, RBAC permissions, tenant isolation, finance rules, inventory accounting, provider payouts, supplier payables, and SaaS control-plane governance.

The strongest rebuild path is not to replace the backend. The right move is:

- Keep the existing CodeIgniter 4 backend and MySQL database as the source of truth.
- Build a new workflow-first React frontend with Lovable.
- Expose or expand JSON endpoints from CodeIgniter only as the frontend needs them.
- Do not ask Lovable to recreate finance math, tenant isolation, provider payout rules, or database schema.

Lovable should be used for what it is good at: modern React UI, user workflows, design system, screen composition, reusable tables/forms/modals, RTL/LTR layout, and rapid UX iteration.

## 2. Recommended Architecture

### Preferred Stack

- Frontend: React + TypeScript + Vite
- Styling: Tailwind CSS + shadcn/ui or equivalent component primitives
- Icons: lucide-react
- Data fetching: TanStack Query
- Forms: React Hook Form + Zod-style validation
- Backend: existing CodeIgniter 4 application
- Database: existing MySQL database
- Auth: first-party `/app-api/v1/*` session-cookie App API; do not let Lovable invent auth or store auth tokens
- Hosting: Hostinger-compatible static frontend or Hostinger Node.js app, depending on plan

### Why React/Lovable Instead Of Laravel Frontend

Laravel is a backend framework. If the backend and database are staying CodeIgniter/MySQL, a Laravel rewrite adds another backend layer and increases duplicated logic risk.

Lovable exports standard Vite + React projects, so React is the natural target. Hostinger currently documents Node.js app support for React/Vite on eligible Business/Cloud plans, and static React builds can also be hosted if API routing is configured correctly.

Laravel remains a good future option only if the team decides to migrate the backend from CodeIgniter to Laravel. That is a separate backend migration, not a frontend-only rebuild.

## 3. Non-Negotiable Boundaries

Lovable must not:

- Create production Supabase/Postgres tables.
- Add Supabase auth, custom auth, or browser-stored auth tokens.
- Replace the MySQL schema.
- Move finance calculations into frontend-only logic.
- Reimplement tenant isolation rules.
- Reimplement RBAC permission rules.
- Invent provider payout, supplier payable, treasury, or inventory COGS behavior.
- Store sensitive tokens in unsafe long-lived browser storage unless the backend design explicitly approves it.
- Build direct database access from the frontend.
- Use `/api/v1/*` as the logged-in React app API.

Lovable may:

- Use mock data for prototyping.
- Create TypeScript interfaces for API contracts.
- Create an API adapter layer for the existing PHP backend.
- Propose better screen flows.
- Build reusable UI components.
- Build RTL/LTR layouts.
- Build frontend validation as a convenience layer, while backend remains authoritative.

## 4. Separate Lovable Repo Workflow

For now, Lovable works only in the separate UI/UX source repo:

```text
https://github.com/Bassamalsaqqa/dentacare-shell.git
```

The main DentaCare backend repo remains:

```text
C:\xampp\htdocs\dental-saas
```

Workflow rules:

- `dentacare-shell` is the fast-moving Lovable UI/UX lab.
- `dental-saas/frontend` is a reviewed imported snapshot, not the live Lovable repo.
- Lovable does not edit `dental-saas` directly.
- `frontend/.git` must not exist.
- Do not use a git submodule or subtree unless explicitly approved.
- Import changes manually or through a controlled sync script after review.
- Regenerate `public/app` locally from `dental-saas/frontend`; do not accept it from Lovable as source truth.
- `public/app` is built deployment output and is committed with the PHP app when reviewed.
- Package changes from Lovable require explicit review.
- Dependency upgrades require explicit approval.
- Vite stays on `5.4.x` unless deliberately upgraded.
- Vite `base` must stay `/app/`.
- Vite `build.outDir` must stay `../public/app`.
- Real App API wiring is done in `dental-saas` only after review.

### Lovable Project Instructions

Use this exact block in the Lovable project settings:

```text
LOVABLE PROJECT INSTRUCTIONS:

This project is the UI/UX source lab for DentaCare React frontend.

Work only on the React frontend.
Do not create backend code.
Do not create Supabase code.
Do not create database schemas.
Do not add auth systems.

The real backend is CodeIgniter 4 + MySQL.
The real app will eventually call /app-api/v1/*.
Do not use /api/v1/* for the logged-in React app.
Do not add Bearer token auth.
Do not add X-Clinic-Id headers.
Do not store auth in localStorage or sessionStorage.
Do not calculate finance totals, provider payouts, inventory COGS, tenant permissions, subscription gates, or WhatsApp dispatch rules in frontend.
Do not send WhatsApp directly from frontend.

Use mock data unless explicitly asked to wire existing /app-api/v1 endpoints.
All future real calls must go through src/api/appApiClient.ts.
Do not use fetch directly inside components.
Do not change Vite base from /app/.
Do not upgrade Vite or dependencies unless explicitly requested.
npm/package-lock is canonical.
Do not add bun.lockb.
```

### Lovable Snapshot Import Checklist

Before Lovable work:

- Confirm `dentacare-shell` is clean.
- Lovable works only in `dentacare-shell`.
- No backend assumptions.
- No Supabase.
- No `localStorage` or `sessionStorage` auth.
- No Bearer or `X-Clinic-Id` for the first-party React app.
- No frontend finance, provider payout, inventory COGS, or WhatsApp dispatch business logic.

Before importing into `dental-saas`:

- `git status -sb` in `dental-saas` must be clean.
- Inspect the Lovable repo diff.
- Reject auth/token strategy changes unless explicitly requested.
- Reject Supabase.
- Reject dependency upgrades unless approved.
- Reject Vite base changes.
- Reject direct `fetch` in components.
- Reject hardcoded `clinic_id` as tenant authority.

Import allowed:

- `frontend/src/**`
- `frontend/public/**`
- `frontend/components.json`
- `frontend/index.html`
- `frontend/tailwind.config.ts`

Import with explicit review:

- `frontend/package.json`
- `frontend/package-lock.json`
- `frontend/vite.config.ts`
- `frontend/tsconfig*.json`
- `frontend/eslint.config.js`

Never import:

- `node_modules`
- `dist`
- `.git`
- `bun.lockb`
- Supabase files
- Generated unrelated files

After importing:

```powershell
cd frontend
npm.cmd install # only if package files changed
npm.cmd run build
cd ..
rg -n "supabase|createClient\(|@supabase" frontend/src frontend/package.json
rg -n "localStorage|sessionStorage" frontend/src
rg -n "Bearer|X-Clinic-Id" frontend/src
rg -n "fetch\(|axios\." frontend/src
rg -n "clinic_id|active_clinic_id" frontend/src
vendor\bin\phpunit --no-coverage --no-logging --do-not-cache-result tests\unit\AppApiFoundationTest.php
vendor\bin\phpunit --no-coverage --no-logging --do-not-cache-result tests\unit\AppApiPatientsTest.php
```

Commit only the reviewed snapshot and regenerated `public/app`.

## 5. Current Product Context

Product: multi-tenant dental clinic SaaS.

Main planes:

- Tenant Plane: clinic staff operations.
- Control Plane: platform administration, clinic lifecycle, subscriptions, plans, plan requests, platform invoices, operations, WhatsApp policy.

Current core modules:

- Dashboard
- Patients
- Providers directory
- Appointments
- Examinations
- Odontograms
- Treatments
- Treatment contracts and signatures
- Prescriptions
- Finance
- Finance reports
- Treasury transfers
- Supplier payables
- Provider payouts
- Inventory
- Suppliers
- Reports
- WhatsApp / notifications
- Users
- Roles and permissions
- Settings
- Activity logs
- File attachments
- Kiosk check-in
- SaaS control plane

Primary users:

- Clinic owner / doctor
- Dentist / clinical provider
- Receptionist
- Finance/admin user
- Inventory/admin user
- Platform superadmin

## 6. Existing Database Context

Keep MySQL as source of truth. Key table families:

- SaaS / tenancy: `clinics`, `clinic_users`, `clinic_subscriptions`, `plans`, `plan_requests`, `plan_usage`
- Auth / RBAC: `users`, `groups`, `users_groups`, `roles`, `user_roles`, `permissions`, `role_permissions`, `user_permissions`
- Patients / clinical: `patients`, `appointments`, `examinations`, `odontograms`, `patient_odontogram_snapshots`, `treatments`
- Contracts: `treatment_contracts`, `treatment_contract_items`, `treatment_contract_odontogram_snapshots`, `treatment_contract_signatures`
- Prescriptions: `prescriptions`
- Finance: `finances`, `finance_payments`, `finance_liquidity_transfers`, `financial_summary`, `provider_payouts`, `provider_payout_payments`
- Inventory: `inventory`, `inventory_batches`, `inventory_usage`, `inventory_stock_movements`, `inventory_item_templates`, `inventory_supplier_links`, `inventory_reorder_items`, `suppliers`
- Notifications / WhatsApp: `notifications`, `notification_attempts`, `clinic_notification_channels`, `patient_whatsapp`, `whatsapp_delivery_events`, `whatsapp_optouts`, `whatsapp_tenant_policies`
- Files / audit: `file_attachments`, `activity_logs`, `control_plane_audits`, `job_audits`, `plan_audits`
- Control plane billing: `control_plane_invoices`, `control_plane_invoice_payments`
- Settings / secrets: `settings`, `email_templates`, `platform_secrets`, `api_tokens`

Every tenant-owned table must remain scoped by `clinic_id` through backend enforcement.

## 7. Finance Rules Lovable Must Respect

Simple terms for UI:

- Money In
- Money Out
- Customers Owe Us
- We Owe Others
- Net Position
- Supplier Bills
- Provider Payments
- Safe Cash
- Bank Cash
- Checks on Hand

Rules:

- Provider payouts are for clinical providers receiving shares from patient transactions.
- Supplier payables are for vendors, labs, rent, inventory suppliers, utilities, and other bills.
- Provider payouts are not supplier bills.
- Inventory COGS is an expense lane, not a provider payout.
- Treasury transfer moves money between custody buckets and does not change profit.
- Custody buckets are `safe_cash`, `bank`, and `undeposited_checks`.
- `transaction_date` is business/document date.
- `payment_date` / `paid_date` is cash settlement date.
- `created_at` is audit timestamp only.

Owner summary formulas:

- Money In = collected inflows for selected period.
- Money Out = refunds + paid expenses + paid provider payouts.
- Customers Owe Us = open receivables as of selected end date.
- We Owe Others = supplier payable balances + provider payout balances.
- Net Position = Money In - Money Out + Customers Owe Us - We Owe Others.

## 8. Workflow-First Product Model

The new frontend should be organized around real clinic work, not isolated CRUD pages.

Primary clinic workflow:

1. Patient is created or found.
2. Appointment is booked or patient checks in.
3. Examination is created.
4. Findings are recorded.
5. Odontogram is updated.
6. Treatment plan is created.
7. Contract/estimate may be generated and signed.
8. Invoice/payment is recorded.
9. Provider shares are calculated when applicable.
10. Inventory usage can be recorded and COGS may be posted.
11. Reports update for clinic owner.

Important UX direction:

- Patient profile should be the central workspace.
- Doctor workflow should stay in a clinical workspace.
- Finance should use simple terms.
- Inventory should focus on stock, usage, receiving, suppliers, reorder, and COGS impact.
- Providers directory should unify suppliers, clinical providers, and guest providers without confusing their financial meaning.

## 9. Target Navigation

Tenant sidebar:

- Dashboard
- Patients
- Appointments
- Clinical
  - Examinations
  - Odontogram
  - Treatments
  - Contracts
  - Prescriptions
- Finance
  - Transactions
  - Reports
  - Supplier Bills
  - Provider Payments
  - Treasury Transfers
- Inventory
  - Items
  - Usage
  - Movements
  - Low Stock
  - Reorder List
- Providers
- Reports
- Users & Roles
- Settings
- Activity Log

Control plane sidebar:

- Platform Dashboard
- Clinics
- Plans
- Plan Requests
- Platform Invoices
- Users
- Operations
- WhatsApp
- Settings
- Activity Log

## 10. Design System Requirements

Create reusable components before building screens:

- AppShell
- Sidebar
- Topbar
- LanguageSwitcher
- ClinicSwitcher
- GlobalSearch
- PageHeader
- StatCard
- ActionCard
- DataTable
- TableToolbar
- StatusBadge
- MoneyBadge
- Modal
- Drawer
- ConfirmDialog
- FormField
- SelectField
- DatePickerField
- FileUploadField
- EmptyState
- LoadingState
- ErrorState
- AuditTimeline
- PatientAvatar
- ProviderAvatar
- PermissionGate

Rules:

- All layout must support English and Arabic.
- RTL must mirror the sidebar, topbar, buttons, arrows, table order, modals, drawers, and action groups.
- Required fields use red `*`, not "(required)" text.
- Modals close by Esc and backdrop click.
- Tables are slim, single-row where possible, searchable, sortable, paginated, and horizontally scroll only when unavoidable.
- Cards use meaningful background density and hierarchy, not extra divider lines.
- Forms use consistent dropdowns, date pickers, validation errors, and helper text.

## 11. API Adapter Plan

Lovable should create a frontend API abstraction, not hardcode fetch calls inside components.

Adapters:

- `authApi`
- `clinicApi`
- `patientsApi`
- `appointmentsApi`
- `examinationsApi`
- `odontogramApi`
- `treatmentsApi`
- `contractsApi`
- `prescriptionsApi`
- `financeApi`
- `inventoryApi`
- `providersApi`
- `reportsApi`
- `settingsApi`
- `usersApi`
- `controlPlaneApi`

HTTP wrapper responsibilities:

- Base URL configuration
- Same-origin session-cookie requests to `/app-api/v1/*`
- In-memory CSRF handling from `/app-api/v1/csrf` or bootstrap
- Server-controlled active clinic state from bootstrap, not client-sent tenant authority
- Locale header
- Normalized handling for 401, 403, 404, 419, 422, 429, 500
- Validation error mapping
- Audit-safe request logging in development only

Current first-party App API contract:

- Use `/app-api/v1/*` for the logged-in React application.
- Use CI4/IonAuth httpOnly session cookies.
- Use CSRF for unsafe methods.
- Treat `active_clinic_id` as server-controlled state only.
- Use JSON response envelopes and `error.code`, not message parsing.
- Do not store auth tokens in `localStorage` or `sessionStorage`.

Public API v1 remains separate:

- `Authorization: Bearer <token>`
- `X-Clinic-Id: <clinic_id>`
- JSON errors only
- No redirects or HTML for `/api/v1/*`

Lovable should target mock adapters shaped like `/app-api/v1/*`. It must not use `/api/v1/*` as the logged-in app API.

## 12. Recommended Migration Plan

### Phase 0 - Prototype Only

Use Lovable to create the full UI shell and mock workflows with fake data.

Output:

- React app shell
- Design system
- Navigation
- Mock data layer
- Patient workflow prototype
- Finance workflow prototype
- Inventory/providers prototype

No production database writes.

### Approved Lovable Session 1 Scope

Current status: Session 1 shell has been imported under `frontend/` and builds static output to `public/app/` with Vite base `/app/`. It remains mock-only; real App API wiring is not part of the import stabilization pass.

Build only:

- React app shell
- Design system
- Mock bootstrap response
- App state walls for no clinic, clinic required, tenant ready, control-plane placeholder, suspended, and subscription inactive
- Tenant app shell with sidebar/topbar/navigation
- Control-plane placeholder shell only
- Placeholder module pages
- Mock adapters shaped like `/app-api/v1/*`

Do not build yet:

- Full Patients module
- Real API wiring
- Supabase or any new auth system
- Browser-stored auth tokens
- Backend business rules
- Finance, tenant, permission, or plan-gate calculations

### Phase 1 - API Gap Analysis

Compare Lovable mock screens to existing CodeIgniter routes.

Output:

- Required endpoint list
- Existing endpoint reuse list
- Missing endpoint list
- Backend changes needed

### Phase 2 - Patients + Appointments

Wire:

- Patients directory
- Patient profile
- Patient create/edit
- Appointments calendar
- Appointment create/edit

### Phase 3 - Clinical Workspace

Wire:

- Examinations
- Odontogram state
- Treatment plans
- Treatment contracts
- Prescriptions

### Phase 4 - Finance

Wire:

- Transactions
- Transaction detail
- Add payment/refund modals
- Supplier bills/payables
- Provider payments/payouts
- Treasury transfers
- Finance reports

### Phase 5 - Inventory + Providers

Wire:

- Inventory item directory
- Create/edit item
- Receive stock
- Adjust stock
- Record usage
- Usage history
- Movement ledger
- Low stock
- Reorder list
- Suppliers
- Unified providers directory

### Phase 6 - Settings, Users, Control Plane

Wire:

- Users
- Roles
- Settings
- Notifications
- WhatsApp settings
- Activity log
- Control plane pages

### Phase 7 - Cutover

Run old and new frontend side-by-side until stable:

- Existing UI: `/dental-saas`
- New UI: `/app` or subdomain such as `new.palsync.net`

Switch module by module. Keep old UI available for finance/control-plane fallback until audited.

## 13. Lovable Master Prompt

Use this as the first prompt:

```text
Build a modern React + TypeScript + Tailwind + shadcn/ui frontend for a multi-tenant dental clinic SaaS.

Important:
Do not create Supabase tables or backend business logic for production.
Assume the production backend already exists as a PHP CodeIgniter 4 + MySQL application.
Use mock API adapters for now, but structure every screen so it can later call real REST/JSON endpoints.
Do not invent finance/accounting rules. Treat the backend as the source of truth.

Product:
A dental clinic management SaaS with:
- Dashboard
- Patients
- Appointments
- Examinations
- Odontogram
- Treatments
- Treatment contracts and signatures
- Prescriptions
- Finance
- Finance reports
- Supplier payables
- Provider payouts
- Treasury transfers
- Inventory
- Providers directory
- General reports
- Users and roles
- Settings
- Control plane for platform admins

Design goal:
Make the app workflow-first, simple for doctors and clinic staff, not accounting-heavy.
Avoid flat pages with many unrelated cards.
Use professional SaaS UI patterns: clear sidebar, top bar, dense but readable tables, cards with meaningful hierarchy, modals/drawers for quick actions, responsive desktop/tablet/mobile, full RTL support.

Languages:
Support English and Arabic. Every layout must mirror correctly in RTL.

UX principles:
- Never reinvent table styling. Create one reusable DataTable component.
- Never reinvent modal styling. Create one reusable modal/drawer pattern.
- Use consistent form fields, dropdowns, date pickers, cards, buttons, badges, icons.
- Prefer workflow pages over isolated CRUD pages.
- Use clear simple terms suitable for doctors, not accountants.
- Finance should say: Money In, Money Out, Customers Owe Us, We Owe Others, Provider Payments, Supplier Bills.

Deliver:
1. App shell with sidebar/topbar
2. Route map
3. Design system components
4. Mock data layer
5. First full workflow: patient profile -> examination -> treatment -> payment
```

## 14. Lovable Design System Prompt

```text
Create the design system for this dental SaaS.

Components needed:
- AppShell with sidebar, topbar, language switcher, clinic/user menu
- StatCard
- ActionCard
- DataTable
- TableToolbar
- StatusBadge
- MoneyBadge
- Modal
- Drawer
- ConfirmDialog
- FormField
- SelectField
- DatePickerField
- FileUploadField
- EmptyState
- LoadingState
- AuditTimeline
- PatientAvatar
- ProviderAvatar
- PermissionGate

Rules:
- All components must support RTL.
- Icons must mirror when direction changes.
- Tables must stay slim and readable.
- Cards must use background density and hierarchy, not extra borders/lines.
- Modals close by Esc and backdrop click.
- Required fields use red asterisk, not "(required)" text.
```

## 15. Lovable Patient Workflow Prompt

```text
Build the Patients module.

Pages:
- Patients directory
- Patient profile
- Create/edit patient
- Patient clinical timeline
- Patient appointments
- Patient finance summary
- Patient files/attachments
- Patient odontogram summary
- Patient WhatsApp/contact identity

Patient profile should be the central workspace.
Do not make users jump between unrelated pages.

Patient profile sections:
- Header: name, ID, phone, age, alerts, quick actions
- Clinical status
- Upcoming appointment
- Last examination
- Active treatment plan
- Balance summary
- Timeline
- Tabs: Overview, Clinical, Appointments, Finance, Files, Notes

Actions:
- New appointment
- New examination
- Add payment
- Create treatment plan
- Upload file
- Send WhatsApp message placeholder

Use mock data but structure API calls through an adapter.
```

## 16. Lovable Clinical Workflow Prompt

```text
Build the clinical workflow.

Core flow:
Patient -> Examination -> Findings -> Odontogram -> Treatment Plan -> Contract -> Follow-up.

Pages/components:
- Examination create/edit
- Examination detail
- Clinical findings form
- Odontogram workspace placeholder
- Treatment plan builder
- Treatment contract preview
- Prescription builder

UX:
- Keep the doctor in one clinical workspace.
- Use tabs or steps, not scattered pages.
- Show patient context persistently.
- Show warnings/allergies/medical alerts clearly.
- Make treatment plan items easy to add, price, reorder, and mark status.
```

## 17. Lovable Finance Workflow Prompt

```text
Build the finance module for a doctor/clinic owner, using simple language.

Do not use complex accounting labels unless necessary.

Pages:
- Finance dashboard
- Transactions
- Transaction detail
- Add income/payment
- Add expense/supplier bill
- Supplier payables
- Provider payouts
- Treasury transfers
- Finance reports

Important terms:
- Money In
- Money Out
- Customers Owe Us
- We Owe Others
- Clinic Net Position
- Supplier Bills
- Provider Payments
- Safe Cash
- Bank Cash
- Checks on Hand

Rules:
- Provider payouts are for doctors/providers receiving shares from patient transactions.
- Supplier payables are for labs, inventory vendors, rent, utilities, etc.
- Inventory COGS is an expense.
- Treasury transfer does not change profit, only moves money between buckets.

Transaction detail:
- Header with type/status/ID/date
- Summary cards
- Details card
- Patient/provider/supplier context card if available
- Payment history table
- Add payment/refund/provider payment in modals
```

## 18. Lovable Inventory And Providers Prompt

```text
Build Inventory and Providers modules.

Inventory pages:
- Inventory dashboard
- Item directory
- Item create/edit
- Item detail
- Receive stock
- Adjust stock
- Record usage
- Usage history
- Stock movements
- Low stock
- Reorder list
- Supplier directory

Providers page:
A unified directory for:
- Suppliers/vendors
- Clinical providers/users
- Guest providers

Important:
Suppliers are not the same as clinical providers.
Clinical providers may receive provider payouts.
Suppliers are linked to inventory and expenses/payables.

Use the same DataTable component as patients and finance.
Quick-create supplier should be a modal.
```

## 19. Lovable API Adapter Prompt

```text
Create a frontend API adapter layer.

Do not call Supabase.
Create TypeScript interfaces and placeholder API functions for the existing PHP backend.

Auth:
- The logged-in React app uses `/app-api/v1/*`.
- Auth is CI4/IonAuth session-cookie based.
- Unsafe requests use CSRF.
- Do not store auth tokens in localStorage or sessionStorage.
- `/api/v1/*` is for separate public/stateless integrations only, not the React app.
- Every API call should handle 401, 403, 404, 419, 422, and validation errors.

Create adapters:
- patientsApi
- appointmentsApi
- examinationsApi
- treatmentsApi
- prescriptionsApi
- financeApi
- inventoryApi
- providersApi
- reportsApi
- settingsApi
- usersApi

Each adapter should use a central HTTP wrapper with:
- base URL
- CSRF support from bootstrap or `/app-api/v1/csrf`
- server-controlled clinic state from bootstrap
- locale
- error normalization
```

## 20. Red Flags To Reject In Lovable Output

Reject or correct any output that:

- Creates Supabase tables as production truth.
- Uses localStorage or sessionStorage for auth tokens.
- Calculates provider shares only in frontend.
- Calculates finance report totals only in frontend.
- Omits clinic/tenant context from API design.
- Hides permissions in frontend only.
- Builds separate table/modal/dropdown styles per page.
- Ignores Arabic RTL.
- Uses accounting-heavy terms for normal doctors.
- Makes every workflow a separate disconnected CRUD page.

## 21. Final Recommendation

Use Lovable to design and implement the new frontend experience, but keep it behind an API boundary. The backend remains the authoritative engine.

The safest phrasing to Lovable is:

```text
You may redesign workflows and UI.
You may propose better screen flow.
You may create mock data.
You may create frontend-only state for prototypes.
But do not change business rules, database truth, tenant isolation, finance calculations, provider payout rules, supplier payable rules, or permissions.
```
