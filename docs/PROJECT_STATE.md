# Project State

## Deployed Release Authority — 2026-10-28

- **Installed Production Authority:** Commit `29dce746c0fc3be436b85a2c1a67fbbb41be3b55` (tree `188433ad669477aa0c6812acd09fcc3ecd2c67eb`) deployed to live production (https://denta.palsync.net) on branch `release/production-20261017`.
- **Release Fast-Forward:** PR #21 merged (2026-10-28T04:30:08Z) through authorized exact-SHA fast-forward without force. Main branch remains at historical `485a19824d08e35fcd1a2a68923c63ec265867ca` and is not deployment authority.
- **Independent Acceptance:** Independent review `5670330900` was COMMENTED with an explicit ACCEPTED disposition (not a formal GitHub self-APPROVED review); no reviews or protections were bypassed.
- **Production Infrastructure:** PHP 8.3.36, MariaDB 11.8.9-MariaDB-log, 67 tables, 77 migration-history records. Source-only cutover via pinned `deploy-approved-studio.php --owner-authorized-studio` executed with zero production schema migrations (7 pending migration files stay unapplied), zero Composer/npm dependency installations/updates, zero GitHub Actions, and zero customer-data rewrites.
- **Active Entitlements & Features:** Core contracts and contract signatures were discovered live and remain ON, explicitly Owner-confirmed. Dynamic scheduling, WhatsApp, invoice corrections, checkout, contact history, and derivatives remain OFF.
- **Pinned Tailwind Asset:** Committed 293,433-byte Tailwind CSS artifact is intentionally pinned at SHA256 `3a51d2fffb54ae89fbb4188e3b1ddfc2bce09c7e81148adba78ce750b7fd801a`. Deployment never builds or installs dependencies. Scanned template differences explain smaller scratch builds; blind regeneration is strictly prohibited.
- **Cutover Receipt:** Sanitized report and adjacent verification receipts are retained in the private operator evidence directory (`studio-release-29dce74/FINAL_DEPLOYMENT_REPORT.md`, outside this repository).

## Delivered Studio Scope & Clinical Corrections

- **Studio Design Foundation:** Delivered unified forest/sage Studio shell (`studio-theme.css`, `app_drawer.php`/`app-drawer.js`, contextual action dialogs, money drawers, five-lane appointment dayboard, booking/calendar, settings, and Control Plane layout).
- **Four Surgical Clinical Corrections:**
  1. Contract item preservation: Item identity, pricing, and draft items preserved during updates.
  2. Historical odontogram preview: validated tooth identity and condition overlays render accurately; ambiguous or invalid historical facts fail closed.
  3. Examination update durability: Examination update transactions lock and persist clinical note records durably.
  4. Empty historical snapshot safety: saved `[]` stays explicitly unavailable instead of implying an assessed healthy chart; genuine saved healthy findings remain distinct. Original snapshot bytes and native SVG remain unchanged.
- **Known Operational Limits & Backlog:**
  - Patient workspace query count and latency remains an open performance concern under active review.
  - Title-separator encoding (`Â·`) in Studio layouts and retained legacy print/CDN limitations remain backlog items.
  - Production tenant dashboard displays a synthetic preview warning ("Local design preview · Synthetic clinic data") that must be cleaned from production presentation.
  - Medical history Unassessed/No/Yes clinical policy remains unresolved; no historical reinterpretation.
  - Physical assistive technology, universal accessibility certification, and production customer-write workflows were not newly certified. Native synthetic evidence is distinct from live read-only verification.

## Current Engineering Scope — Product-Wide UX, Workflow & Theme System (Development Only)

- **Active Development Branch:** `feature/studio-workflows-themes-20261028` created directly from deployed release `29dce746c0fc3be436b85a2c1a67fbbb41be3b55`.
- **Release Boundary:** DEVELOPMENT AND REVIEW ONLY. Production is live with active clinics. No merge, live deploy, production SQL/migrations, feature activation, automatic agent delegation, force push, or customer-data changes are authorized.
- **Authoritative Master Brief:** Governed by `DentaPlus_StateOfTheArt_UX_Workflow_Theme_System_Codex_Master.md` (Owner file in original checkout).
- **P0 Delivery Priorities:**
  1. **Control Plane & Impersonation Entry/Exit:** Genuine platform Super Admin entry via GET `/controlplane` entry gateway followed by CSRF-protected POST `/controlplane/enter` (never inferred from tenant admin role or front-end boolean); persistent responsive/RTL-safe impersonation banner ("Viewing clinic as platform administrator") with explicit clinic identity and return action; exit via CSRF-protected POST `/controlplane/impersonation/exit` with session/global_mode reset and audit logging.
  2. **Authentication Fit:** Resolve avoidable vertical scrolling on normal displays and mobile in `studio_access.php` / `studio-access.css` by rebalancing stage padding and panel order.
  3. **Presentation Hygiene:** Remove synthetic demo label from production presentation; resolve title-separator encoding in Studio layouts.
  4. **Multi-Tenant Theme System:** Safe theme inheritance and presets (modern/warm optional themes, retaining forest/sage Studio as default branded theme). Inspect `SettingsModel` scoping, uniqueness and concurrency before choosing storage; no persistence architecture has been accepted yet. Additive migrations may be authored and tested ONLY in verified disposable environments; zero production migrations authorized.
  5. **Continuous Clinic Journeys:** Task-complete navigation and next-step continuity across Patient Intake, Reception Dayboard, Clinical Encounter, Treatment Plan, Checkout, Finance Reconciliations, and Inventory.

## Core Reference Contracts

- Active Document Map: [START_HERE.md](START_HERE.md)
- Tenant Context Contract: [TENANT_CONTEXT_CONTRACT.md](SaaS/TENANT_CONTEXT_CONTRACT.md)
- Scheduling Contract: [DOCTOR_SCHEDULING_CONTRACT.md](SaaS/DOCTOR_SCHEDULING_CONTRACT.md)
- Operations Runbook: [OPERATIONS_RUNBOOK.md](OPERATIONS_RUNBOOK.md)
- Clinical State: [CLINICAL_CURRENT_STATE.md](CLINICAL_CURRENT_STATE.md)
- Finance State: [FINANCE_CURRENT_STATE.md](FINANCE_CURRENT_STATE.md)
- Messaging State: [WHATSAPP_CURRENT_STATE.md](WHATSAPP_CURRENT_STATE.md)
- Architecture Decisions: [DECISIONS.md](DECISIONS.md)
- Historical Deployments: Retained in Git history and release receipts outside the working tree.
