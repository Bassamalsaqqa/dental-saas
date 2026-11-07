# Historical development program

This plan predates the deployed PR #22. Its dates, branch/PR pins, pending-release claims and proposed execution order are historical. Use ../PROJECT_STATE.md for release authority and ../WORK_PROGRAM.md for current scope. Retained technical findings are leads to verify against current source, not current instructions. Obsolete root debug/redirect/deploy helpers were removed locally in the 2026-11-05 cleanup.

# Denta+ Modernization, Hardening & Performance Program

**Historical implementation record; current authority is linked below**
**Track:** Modernization & Performance Refactor
**Status:** Historical; superseded for active work by WORK_PROGRAM.md
**Initial Checkpoint:** Checkpoint 01 (Baseline & Tenant Database-Backup Containment)
**Last Updated:** 2026-09-25
**Target Head:** `c4f31e59cc656bbb622047a33217fd2fd0be5c5a`

---

## Final closure classification (2026-10-23)

This table is the consolidated delivery boundary; historical roadmap wording below is supporting context, not a claim of completed product scope.

| Classification | Actual scope |
|---|---|
| DELIVERED | Operational correction ledger/application/return and manager controls; guarded settlement/report/reminder integration; bounded secondary clinical histories and query projections; native locale correction; scoped email validation/migration preflight; proxy/file/account preservation hardening; seven reviewed schema units; default-OFF contact, checkout and derivative implementations. Targeted SQL/routed/concurrency/EN-AR evidence is preserved. |
| PARTIAL | Whole patient workspace is not constant-memory; legacy complete financial/document collections remain. Scheduling code is ready for separately authorized roster/legacy-visit/pilot review, not activated. Native medium-clinic finance/read/lock and populated file recovery pass; production peak capacity, physical power loss and activated-feature operator acceptance are not certified. Purchasing/clinical specialty depth remains existing functionality rather than a new comprehensive subsystem. |
| DEFERRED BY POLICY | Fiscal documents/tax numbering; salaries/compensation; insurance/deposit/custody rules; automatic provider adjustments/clinical billing; purge/retention; new clinical specialty definitions; guardian/portal identity and governed external sending/payment policies. No operational semantics or grants are invented. |
| OUT OF SCOPE | React source modernization in separate Bassamalsaqqa/Dentacare-Shell repository; reimplementation of deployed M1/M2; production checkpoint/merge/deployment/migrations/feature activation at this development gate; unrelated aesthetic redesign and blind old-branch integration. |

Security closure records29 exact legitimate exceptions (28clinic-bound/raw transactional queries plus a fixed protected-chart icon), unchanged guardrail algorithm and negative tests rejecting new unsafe matches. This is a verified exception inventory, not a silent waiver. Remaining7 build-tool advisories (5high/2moderate) are developer-tool limitations; production dependency audits are narrower and do not certify every bundled asset. Tailwind/parser major upgrade is deferred to a separately scoped compatibility change; no automatic security-risk waiver for running untrusted development inputs.

## 2026-10-22 consolidated execution matrix

This section supersedes historical checkpoint statuses below. Development stays in PR #20 on the deployed M2 ancestry; it does not authorize production integration, migrations or activation. The Owner-supplied `DENTA_PLUS_UNIFIED_MASTER_ENGINEERING_PLAN_2026-10-19.md` is reconciled against current source, not reapplied as patches.

| Scope | State | Current disposition |
|---|---|---|
| Stage 0, M1 inventory/COGS/tenant joins and clinic messaging | DONE | Deployed M2 ancestry; reuse accepted evidence. |
| M2 signing/publication recovery and payout preservation | DONE | Preserve immutable evidence; PR19 final external review was Owner-waived, not approved. |
| M2-C operational corrections, explicit applications/returns | PARTIAL | Implemented and focused acceptance recorded. Cash-backing finding retracted; overall external review remains COMMENTED/CHANGES_REQUESTED, not approval. Final consolidated review required. |
| Patient counts and report queries | DONE (development) | Explicit clinic/page aggregate replaces incorrect retained-builder N+1. Four monthly charts use one grouped query; DOB frequencies retain existing PHP calendar semantics. Unused full-clinic treatment/inventory registry loads removed; treatment stats 8 queries -> 1, inventory counters 4 -> 1, unchanged criteria. |
| Timeline / chart data serialization | DONE narrow correction | Twelve timeline fields and six specifically Owner-authorized chart JSON slots are HTML-safe, with exact decoded data, inert-payload browser proof and golden protected-code comparison. No stored evidence, SVG, rendering or snapshot changes. |
| Static delivery and patient preview | DONE (development) | Stable metadata asset versions, consuming-view chart/calendar vendors, hidden-tab polling pause, protected lazy raster URLs instead of Base64, responsive login WebP with preserved original. |
| Storage integrity | PARTIAL | Retained metadata quota totals fixed; newest export remains visible; publication precedes retention. User deletion retains bytes. Physical purge disabled until approved retention/legal-hold policy. Orphan/derivative physical accounting remains separate. |
| Patient email tenancy | DONE (development) | Shared clinic validation plus targeted scoped unique index migration. Current Hostinger global unique index is confirmed; source alone does not remove it. Production migration separately authorized only. |
| Legacy group authorization | PARTIAL | Unused controller wrappers removed; camelCase guard added. Two exact non-authorizing observations remain. Font guard now passes and view DOM sinks reduced from six to one in protected odontogram internals. Provider rows use one inert template with safe seeded JSON. Raw-controller-query and remaining DOM categories still fail, not waived. |
| Dynamic doctor scheduling | DONE code / BLOCKED pilot | Stable doctor identities, eligibility, availability, serialization, preserved legacy visits and impact acknowledgment already exist. Old PR4 ancestors must not be merged again. Five indexed DATE predicates optimized with SQL equivalence. Migration reentry now fails closed on incompatible schemas (56SQL checks); actual captured-schema migration preserves records/default-OFF state (12checks), and 19actual-directory/availability checks preserve all original rows after rollback. Measured optional charge lookup index has23migration checks; native targeted schema/service rehearsal now passes; roster/legacy review, load/recovery and separate migration/activation remain gates. |
| Missed/cancelled visit attention | PARTIAL (contact default OFF) | Read-only last-30-calendar-day panel with all-date canonical links plus an explicit manual factual contact ledger on appointment details. No automatic sending, consent inference, queue hiding, status change or rescheduling. Actual representative EN/AR GET/viewer/tenant/retry acceptance now passes; separate schema/activation and final release review remain gates. |
| PHP patient workspace decomposition and paginated secondary histories | PARTIAL (bounded clinical/timeline complete) | Canonical ten-source keysets, 100-candidate work budgets, 25 visible matches, complete summaries/same-day context and explicit sparse-search continuation are wired to the actual PHP shell. 87SQL/17presenter/105HTTP checks and EN/AR desktop/mobile browser acceptance pass. Existing financial/MoneyTrail and other module datasets remain complete, but duplicated invoice projection is removed (85SQLchecks, warm10->7queries). No fully bounded-page or Hostinger speedup claim. |
| Atomic invoice + initial receipt | PARTIAL (default OFF) | One owned transaction composes existing invoice, receipt, treasury attribution, clinical/provider linkage and audits. Persistent actor/tenant request identities prevent duplicate checkout. 30 request/concurrency SQL checks, 32 actual shared-receipt checks and native permission/replay/rollback acceptance pass; 18 two-connection clinical-claim checks and fresh routed clinical checkout/retry pass after stale-source double-billing containment. Actual full-shell EN/AR desktop/mobile interactions and 15 actual receipt/provider/audit composition checks pass. Separate schema/activation and final review remain gates. |
| Media derivatives | PARTIAL (default OFF) | Bounded private raster variants, checksum metadata, explicit cursor CLI and original-authorized preview routes implemented; 55 synthetic GD/SQL/file checks pass, including two-process rendering/publication, injected metadata failure, explicit recovery retry and all eight EXIF orientations. Bounded read-only integrity report works while OFF. Six native loopback HTTP checks certify original-authorized delivery/fallback, permission revocation, tenant denial and no-store; 14 migration preflight checks pass. Native LiteSpeed capabilities are confirmed; derivative-specific capacity and operator rehearsal remain required. No automatic backfill/purge. |
| React splitting / App API expansion | BLOCKED separate source | `Bassamalsaqqa/Dentacare-Shell` resolves; old `dentacare-shell-5051486e` does not resolve with current access. This repo tracks generated `public/app`, not React source. Never hand-edit compiled bundles or claim source modernization here. |
| Clinical corrections, perio/referral depth | PARTIAL / BLOCKED semantics | Existing signed notes/addenda and procedure transitions remain. Completed procedure amendments require explicit provenance and separate financial consequences; specialized chart definitions and clinical signoff are not invented. |
| Purchasing / receiving / supplier accounting | PARTIAL | Existing reorder, receiving and supplier links remain. New order/payable lifecycle requires explicit receiving/valuation and cancellation rules; no automatic supplier charges. |
| Payroll, insurance, deposits and reconciliation | BLOCKED policy | Salaries remain separate from invoice commissions. Require accrual/period/approval rules; insurance responsibility/adjustment/claim rules and custody reconciliation policies before operational writes. Existing patient-credit policy is implemented, not a universal deposit policy. |
| Patient portal / intake / self-booking | BLOCKED security/product contract | Define patient identity verification, guardian access, consent/versioning, document scope and appointment approval before exposing clinical records or granting access. Safe architecture reuses tenant services; no parallel auth/backend or public PHI endpoints. |
| Retention/purge, fiscal documents, external payments | BLOCKED explicit validation | Require retention classes/legal holds, accountant-approved fiscal modes/numbering/tax and provider settlement rules. No silent operational defaults. |
| Framework upgrade, old branches, blanket cleanup | OBSOLETE as written | CI4 4.7.5 and earlier fixes are already deployed. No duplicate upgrade, old PR merges, bulk locale removal or odontogram optimization. |

### Account and manual-contact preservation

Physical global-account deletion is fail-closed in the central IonAuth model, before hooks, group removal or database operations. Control-plane screens retain explicit activation/deactivation and explain the restriction in EN/AR; denied direct requests return a plain localized reason. This also restricts unused-account hard deletion until a reviewed purge/eligibility contract can prove preservation. No account is silently deactivated and existing histories are not rewritten.

Fresh read-only Hostinger FK metadata found only `clinic_users.user_id -> users.id` with cascading deletion; the migration-declared activity-log FK is absent there. FK-only preflight cannot certify non-FK clinical, financial and audit attribution under concurrent writers. Physical clinic deletion is also denied centrally by `ClinicModel::delete`, the existing deletion guard and the routed control-plane endpoint before subscription/membership/history mutations. Existing suspend/reactivate actions remain explicit. This includes apparently unused clinics until approved offboarding defines preservation and purge. Actual Hostinger metadata confirms 17 cascading clinic FK mappings including clinical/patient data; finance-only eligibility was insufficient. Manual appointment contact history now uses a separate append-only ledger with restrictive parent FKs and actor/tenant/request identities; factual note text never enters general activity metadata. It remains default OFF. Existing read-only no-show/cancellation attention lists remain available without automatically treating recorded contact as successful recovery.

### Language and operational identity preservation

Language-switch redirects are limited to the explicit configured application origin/path, including encoded traversal and forged Host-header checks. GET updates only personal preference; persisted clinic-default POST requires existing settings-edit authority and checked writes. The account/clinic purge restrictions above are intentional release limitations, not silent data cleanup.

### Measured development evidence

- Actual synthetic MariaDB report parity: 19 checks. Twelve-month baseline **12 queries / 9.852 ms -> 1 query / 1.259 ms**, same results on 1,200 synthetic patients. This is local timing, not Hostinger capacity certification. DOB hydration falls to 91 distinct dates in that fixture, not constant-space buckets.
- Registry actual SQL parity: six checks on 1,200 synthetic records per module. Treatment eight-counter baseline **8 queries / 7.783 ms -> 1 query / 1.657 ms**; inventory counters one query, preserving null/quantity/status semantics. Paginated table endpoints and quick-create options remain intact.
- Patient count query-contract: 18 checks, 100 rows **100 -> 1 query**; actual SQL aggregate/tenant parity also included in report fixture.
- Appointment DATE predicate equivalence: 12 actual SQL checks. No native EXPLAIN speedup claim.
- Patient secondary-history transfer on a deliberately wide 90-row synthetic fixture: examinations **2,548,255 -> 22,675 bytes**, appointments **2,551,561 -> 25,981**, treatments **2,591,240 -> 65,660**. Same rows/values/order and tenant filters; this is projection evidence, not production timing or bounded-row PHP completion.
- Login original **6,231,977 bytes** retained; 1920-wide WebP **31,176 bytes**, mobile **3,844 bytes**. EN/AR desktop/mobile rendered acceptance passed; fallback font limitations recorded privately.
- Fresh Hostinger read-only identity: commit `f86376a4a6c8f6bf0fe4471f08093af71e76e042`, tree `4f12c340f8869b4b8f8aff57881c4b29ee98a72c`, PHP **8.3.35**, MariaDB **11.8.9**, 77 migration records. `appointments.appointment_date` is DATE, `finances.total_amount` exists, correction tables absent, patient email globally unique. Runtime/schema observation is not native write/concurrency certification.
- Composer locked advisory check reports no advisories/abandoned packages; production npm audit reports zero. Compatible build-tool lockfile patches reduce full npm audit from 13 to 7 findings (5 high/2 moderate); build succeeds. Remaining Tailwind3-related dev-tool issues need reviewed remediation/containment, not `audit fix --force`. Bundled framework `system/` and separately generated React assets are outside those narrower clean audits; no blanket security certification.

Generated CSS rebuilt with the compatible patched lockfile: **291,800 -> 293,388 bytes**. Retained selectors keep identical property values; five unreferenced utility selectors disappear and 29 are added for current templates. This is not a claim that every source/artifact got smaller.

Eligible consolidated development is frozen for final independent review. The matrix below does not assert that policy-bound or separately sourced roadmap features are delivered. No production data, original checkout, historical staging or protected clinical evidence was changed.

---

## Architectural Contracts & Governance

This document provides historical delivery context and does not supersede permanent architectural contracts. Core invariants continue to be governed authoritatively by:

- [Tenant Isolation & Context Mechanics (docs/SaaS/TENANT_CONTEXT_CONTRACT.md)](../SaaS/TENANT_CONTEXT_CONTRACT.md)
- [Public `/api/v1/*` Stateless Semantics (docs/API_CONTRACT.md)](../API_CONTRACT.md)
- [First-Party Session `/app-api/v1/*` Semantics (docs/APP_API_CONTRACT.md)](../APP_API_CONTRACT.md)
- [SaaS Invariants & Airlock Doctrine (docs/SaaS/SAAS_PROGRAM.md)](../SaaS/SAAS_PROGRAM.md)
- [Current Release & Activation State (docs/PROJECT_STATE.md)](../PROJECT_STATE.md)
- [Current Maintenance Work Program (docs/WORK_PROGRAM.md)](../WORK_PROGRAM.md)
- [Documentation Index (docs/START_HERE.md)](../START_HERE.md)

### Checkpoint 01 Baseline & Security Containment Summary

1. **P0 Tenant Database Backup Route Exposure Contained:**
   - Legacy `/settings/create-backup`, `/settings/download-backup/(:segment)`, `/settings/restore`, and `/settings/backup` hard-deny with 404 (`PageNotFoundException`).
   - `SettingsService::generateDatabaseBackup()` unconditionally throws `RuntimeException`.
   - Tenant settings UI contains zero backup creation forms or download buttons; managed platform advisory displayed in EN/AR. Existing backups preserved on disk.
2. **Test Database Isolation & Tooling Safety:**
   - Isolated target preflight enforced via `scripts/TestDatabasePreflight.php`: requires exact dotted `database.tests.database`, local loopback host (`127.0.0.1`, `localhost`, `::1`), approved ports (`3306`, `3307`), and strict business disjointness.
   - Non-destructive canary tracking via `_test_db_marker`; safe runner bridge (`scripts/safe_test_runner.php`) protects configuration from environment leakage.
3. **Security Closure & Verified Exceptions:**
   - 29 verified legitimate exceptions (28 clinic-bound/raw transactional queries plus a fixed protected-chart icon); guardrail algorithm intact.

### Historical 13-Stage Roadmap Archive

The speculative 13-stage modernization roadmap originally drafted in September 2026 (Stages 1–13) is superseded by PR #20, PR #22, and PR #27. Its full preliminary text is preserved in Git history at `7c70c111f55ce754dca3d9229021c8b1c88e092b:docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` (`git show <commit>:<path>`).
