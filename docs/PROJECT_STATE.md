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

## Latest component correction candidate

The current draft adds task-sized native dialogs, reliable dirty/validation/pending handling, full selected-patient identity, compact account/row menus and date groups, and responsive calendar panels. See [matched component gallery](ui-evidence/product-ux/component-gallery.html) and [108-check scoped receipt](ui-evidence/product-ux/component-craft.json). One owned synthetic patient was created;445 original rows remain unchanged. This pass changes frontend source only; dependencies, native clinical/evidence and existing business authority are preserved. Query counts are unchanged frombc7; patient backend latency remains unresolved. Production remains unchanged. Current exact-head review, targeted native rehearsal and fresh certified release recovery are still required. The olderbc7 native receipt is not current-source certification. Former reviewer formal request API422 remains unresolved; no approval or permission is fabricated.

## Final acceptance closure - blocked

Review5695038870 is COMMENTED with CHANGES REQUIRED. Arabic impersonation/return confirmations are corrected and routed checks passed. New actual treatment save exposed a pre-existing catalog/storage mismatch:41/43 choices are unsupported by the production ENUM. Clinical transaction rolled back; no production change. [Definitive closure and risk matrix](ui-evidence/product-ux/FINAL_ACCEPTANCE_CLOSURE.md). Source-only release remains NO-GO; themes OFF. No independent approval, new migration or deployment is claimed.

## Treatment storage / official shadcn program - current development

The2026-10-29 Owner directive authorizes a dedicated treatment type migration only in disposable environments and whole-app shadcn-aligned CI4/PHP/JS ports. P0 correction commit2348a4e widens ENUM to VARCHAR100 without key remapping;125 SQL checks,43 actual UI create/edit/read/filter cases, current boundary tests and native44-record rehearsal passed with protected fixture preservation. No production DDL or deployment. This cannot be released as source-only to repair the old schema. [Scoped evidence](ui-evidence/product-ux/treatment-storage.json).

Component program is IN PROGRESS: [source screen matrix](UI_SHADCN_SCREEN_MATRIX.csv), owned component parity contract, semantic aliases and a genuine single-surface patient Combobox port. No React/SPA/toolchain installation. Full product-family coverage, remaining native journeys and theme/accessibility/print acceptance remain pending. Production stays29dce746, themes and optional features stayOFF.

Review5697042630 remains COMMENTED/CHANGES REQUIRED, not approval of later source. Local0aae25ba/8b83d8d2 address protected finance drafts, canonical dialog context, unconfirmed outcomes, bounded mobile actions, single-surface lookup and action foregrounds. Real synthetic lost-response/payout/denial and583 original-row preservation checks (including provider allocations/payments) passed. [Current scoped correction receipt](ui-evidence/product-ux/review-5697042630.json). Whole campaign remains IN_PROGRESS; final exact-head review only after integrated readiness. No production changes/migrations/activation.

## Review5697042630 continuation

Shared protected financial tasks now include staff payment dialogs and payout/payable sheets (9d272e1d). Reception registration/booking also retain unknown save outcomes until explicit registry/schedule reconciliation (07bc3d02); actual new synthetic patient27 and appointment1145 each persisted once with lost responses.583 original rows remain unchanged. Semantic foreground collision correction restores readable helper/notice text across the existing shell and route families; [scoped actual browser evidence](ui-evidence/product-ux/semantic-consumer-correction.json). This is still IN_PROGRESS, not independent approval or whole-app acceptance. Treatment migration remains separately release-gated; themes/default-off optional features and production remain unchanged.

## Review5700597599 - current bounded acceptance

Owner review accepts the recent finance/reception UX at8d750e10 in scoped terms, not the whole application. Current continuation corrects trusted exact-record history navigation and payable hierarchy/role-safe return paths. Actual stale supplier full-payment, repeat, permission/CSRF and audit-fault rollback checks passed. A concrete existing partial-payment replay risk was confirmed across two authenticated stale tabs; no backend or accounting rules were modified or risk waived. [Single current acceptance report](ui-evidence/product-ux/CURRENT_ACCEPTANCE.md) identifies the separately scoped durable-operation/stale-state protection needed and all remaining whole-program gates. PR22 stays Draft; themesOFF; no production, migration, activation or Actions.

## Current PR22 surgical financial correction (development only)

Review5701870044 at cde62b83 authorized durable request identity, locked stale-state rejection and isolated additive-schema tests. Implementation and focused actual SQL/multi-worker/browser evidence are prepared for independent acceptance; see [CURRENT_ACCEPTANCE](ui-evidence/product-ux/CURRENT_ACCEPTANCE.md). Production was not contacted or changed. No migration batch, deployment, feature activation or new whole-app acceptance is implied. The new request schema requires separate production authorization before settlement rollout.

## PR22 final readiness ? production NO-GO

Accepted financial runtime remains c5e0a5a6/tree62857631; review5704555130 is COMMENTED with bounded acceptance, whole-PR changes required. Read-only live29dce746/tree188433ad reconfirmed. Native production-compatible synthetic rehearsal reproduces treatment widening refusal on38empty historical ENUM keys; no live record changed. Independent native request migration/service smoke and fresh encrypted off-host67table/9,664row recovery restoration passed. Existing web rehearsal was not switched. Source/coverage pins reconciled without promoting201pending entries. See [consolidated report](ui-evidence/product-ux/FINAL_RELEASE_READINESS.md). No production migration/source deployment/feature activation or Actions.

## Historical treatment release correction ? development only

Review5705127812 and Owner authorized preserving38historical ENUM-index-zero entries without clinical inference. The corrected migration preserves all44rows/bytes with private immutable provenance and checked crash recovery; explicit EN/AR unknown presentation and unrelated-edit preservation pass. Native PHP8.3.36/MariaDB11.8.9 ordered two-migration rehearsal,43actual routed catalog creations/SQL readback, lock contention/fault recovery and combined accepted finance smoke pass. [Current bounded acceptance](ui-evidence/product-ux/HISTORICAL_TREATMENT_ACCEPTANCE.md). Final independent exact-head/whole-PR acceptance remains pending; production NO-GO. No production migration/source switch/customer-data modification or optional feature activation. Preserve complete writable including the new recovery journals; source rollback never narrows storage or discards request evidence.

## PR22 completed-retry correction and acceptance reconciliation — 2026-10-30

Current development parent b95d6802: completed treatment-migration retries now validate completion metadata and exact converted schema while preserving legitimate later rows. Native27/pure17checks pass; immutable provenance retained. Actual synthetic clinical/reception/finance/platform persistence and denials are recorded in [current consolidated decision](ui-evidence/product-ux/FINAL_RELEASE_READINESS.md). All202 pending view entries are now mapped to exact blobs and scoped evidence, not falsely marked accepted. Signature/attachment ON workflow and final exact-source native HTTP gaps remain. Production NO-GO; no live operation, application migration, feature activation or dependency change in this pass. Final exact SHA/tree are reported in the PR publication handback.
