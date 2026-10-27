# Project State

Last updated: 2026-10-27 (source authority reconciled from existing receipts; PR21 actual-persistence acceptance blocked; no live production verification).

## Source authority reconciliation — 2026-10-27

- GitHub protected `release/production-20261017` was verified read-only at `89352fd20e0d5166e81f631cb743bead1f596a68` (tree `71fcf67a0def31a99619437169a58ddfabc7b141`). `main` remains historical and is not deployment authority.
- Existing local deployment receipt `release-execution-89352fd-20261023T120943Z/FINAL_RELEASE_REPORT.json` records installed SHA `89352fd20e0d5166e81f631cb743bead1f596a68` and matching tree, previous source `f86376a4`. This reconciles the stale M2 text below as historical evidence.
- The current live installation was **not contacted** for this development acceptance task. The receipt establishes last recorded deployment, not proof of today's installed state or unchanged customer activity.
- PR21 Studio foundation accepted by independent review5666649981 at27bc01e; full workflow/UI/release acceptance remains blocked. Actual disposable mutation findings and separate protected correction proposals: [final acceptance evidence](ui-evidence/final-acceptance/README.md).

## Historical M2 deployment receipt (retained)


- Production and GitHub `release/production-20261017`: `f86376a4a6c8f6bf0fe4471f08093af71e76e042`.
- Exact tree: `4f12c340f8869b4b8f8aff57881c4b29ee98a72c`. M1 and M2 are deployed; do not reapply their corrections.
- PR #18 exact head `7d405af6738ee39f24b15c8be76c8e14cf52fe5a` was independently approved. PR #19 exact head `3f157567e6cfad47a031fc2cbf8fb5c01bb2b9b1` remained COMMENTED; final external decision was explicitly waived, not approved: `OWNER_WAIVED_PR19_FINAL_EXTERNAL_REVIEW`, https://github.com/Bassamalsaqqa/dental-saas/pull/19#issuecomment-6319034871 .
- `main` is historical state, not deployment authority. Original dirty checkout and historical staging evidence are preserved.
- Hostinger PHP 8.3.35 and MariaDB 11.8.9. Production source-only M2 deployment executed no migrations, dependency changes or feature activation. Scheduling remains OFF; pending scheduling migration remains unapplied. WhatsApp was not activated; existing email settings are retained.
- M2 post-switch checks: exact source hashes/modes, 28 PHP syntax checks, 14 core/clinical table fingerprints, 67 schemas, 77 migration records and 49 durable files/configuration preserved. Public HTTP/auth denial passed. Fresh authenticated production browser smoke was unavailable because native browser transport failed; prior isolated native EN/AR/kiosk/document/recovery evidence was reused, not relabeled as live certification.
- Verified encrypted off-host recovery checkpoint: `denta-private-recovery-20261021T164409Z`; separate private key storage. Authenticated decryption/archive checks passed; prior isolated restore reused. DB/files are non-atomic. Entire writable (journals, staging, generated HTML/uploads) must survive backup and source-only rollback; never restore/rewrite customer DB automatically.

## Current engineering scope: consolidated roadmap campaign

- Isolated development branch `m2c/invoice-corrections-discovery-20261021`, based on the exact deployed M2 source.
- Owner-approved isolated implementation: immutable issued/legacy invoices, linked corrections/credit notes, refunds distinct from noncash credits, clinical/provider obligations preserved, explicit replacement document links and consistent reporting/treasury.
- Existing invoice/payment/void/adjustment and clinical/provider infrastructure must be reused carefully. A noncash credit is not a receipt or cash refund. Existing `adjustment` settlement semantics are not adopted as a credit-note ledger by assumption.
- Implementation is a default-OFF unaccepted draft; see [focused evidence and remaining acceptance blockers](M2C_IMPLEMENTATION_EVIDENCE_20261021.md). Core accounting policies approved: explicit same-patient/clinic/currency replacement applications or refunds, manager approval for provider-linked issuance, operational-only documents; fiscal mode requires separate accountant validation. No automatic provider clawback, historical mutation, clinical unbilling or signed evidence changes.
- Consolidated development also contains default-OFF manual appointment contact history, atomic invoice/initial-receipt checkout and protected media derivatives; `FINANCE_CURRENT_STATE.md` and `M2C_RELEASE_PROCEDURE.md` describe their separate schema/activation/recovery boundaries. Original source remains protected; final consolidated review is pending.
- No merge, deployment, migration, activation, production writes or customer-data changes without separate Owner authorization. No GitHub Actions; no broad audit/test repetition.
- Preserve odontogram SVG/rendering/snapshots and signed clinical evidence. The Owner expanded nonproduction scope to the reconciled roadmap; financial/legal/clinical policies are not invented. See the compact execution matrix in `refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md`.

## Native synthetic acceptance checkpoint

- Workflow-tested source: `8f426fcdbf61a6aa805285e0ac925dc47f92c610`, tree `9118bb36e08f5597580256a95497ef346861f0e0`; every exported file was verified. Later changes are scoped and separately verified; do not relabel earlier suites as final-head reruns.
- Seven targeted schema units: 82 preservation checks on PHP8.3.35/MariaDB11.8.9; no grants, remapping or persistent feature activation. Seventeen actual-service financial checks preserve cash/invoice history through explicit credits/application/return and retries.
- Actual HTTPS browser checks cover EN/AR patient-finance desktop/mobile, Arabic mobile hours, and both language-switch entrypoints. The observed stale-query locale defect is corrected; 134 actual-method checks and three native language transitions pass, with zero new page errors.
- Synthetic restore: all78 tables, their schema definitions/30FKs and four captured writable files match separate restore targets. Off-host SQL/archive retrieval is verified. This is a synthetic checkpoint, not a fresh production backup. Entire writable/configuration remains the required production coverage, including signing journals/staging/generated evidence when present.

## Current exact-head review correction

The review at6bb06dc returned CHANGES_REQUESTED for migration rollback ownership. Scheduling, contact and charge-index migrations may adopt pre-existing schema; their automatic down now refuses before database access, including empty objects. Up/preflight code is unchanged. Forty-six new focused real-SQL checks preserve each pre-existing/new/linked object and all rows. This is an intentional rollback restriction, not a completed external approval. AGY timed out without edits; Codex performed the correction. The latest f754f1f independent review confirms the ownership defect corrected. Its changed PHPUnit fixture now passes2tests/15assertions. Final source identity/re-review is tracked on PR #20.

## Known operational limits

- The Owner specifically authorized a JSON-serialization-only odontogram correction after a confirmed script-breakout finding. Mini/full decoded chart values, SVG assets, rendering and snapshots are preserved; focused template/browser/golden checks pass. No broader chart modification is authorized.
- Native production browser certification remains outstanding, as recorded above. A separate synthetic Hostinger rehearsal now has actual LiteSpeed HTTPS/EN-AR acceptance and verified database/files restore; this is not a production restore or power-loss test.
- Ambiguous/historical partial signing evidence and unreferenced artifact retention require controlled operator decisions; no automatic repair/deletion.
- Final closure guardrails pass using evidenced exact-line exceptions, with unchanged detectors and negative probes. This is not a zero-query/zero-sink claim; see final acceptance in M2C_IMPLEMENTATION_EVIDENCE_20261021.md.
- Hostinger MCP is authenticated after Owner reauthorization. The Owner separately authorized a cost-free isolated native rehearsal: new restricted synthetic databases, private source/storage and a new guarded dentarehearsal.palsync.net webroot. Existing production/staging installations are preserved. No production writes, migrations or deployment are authorized.

## References

- Active map: [START_HERE.md](START_HERE.md)
- Tenant contract: [TENANT_CONTEXT_CONTRACT.md](SaaS/TENANT_CONTEXT_CONTRACT.md)
- Scheduling contract: [DOCTOR_SCHEDULING_CONTRACT.md](SaaS/DOCTOR_SCHEDULING_CONTRACT.md)
- Operations: [OPERATIONS_RUNBOOK.md](OPERATIONS_RUNBOOK.md)
- Finance: [FINANCE_CURRENT_STATE.md](FINANCE_CURRENT_STATE.md)
- Clinical: [CLINICAL_CURRENT_STATE.md](CLINICAL_CURRENT_STATE.md)
- Messaging: [WHATSAPP_CURRENT_STATE.md](WHATSAPP_CURRENT_STATE.md)

Superseded development chronology and old pending-review claims remain in Git history rather than being current release instructions.

## Final consolidated development closure

Native synthetic closure now includes5000invoices across2clinics, actual settlement race/cross-clinic independence, bounded metadata-lock timeout, process termination after publication and populated writable restore/off-host hash verification. No physical power-loss or production-load guarantee is claimed. The final acceptance matrix distinguishes fresh narrow runs from reused evidence. Source is frozen for one final exact-head review; roadmap policy/dependency boundaries remain explicit. No fresh production checkpoint is created here; it belongs to separately authorized release preflight.

## Final Gemini reconciliation

PR20 at96b80cc6 received formal exact-head APPROVED. The bounded five-item reconciliation confirms one changed-code release blocker: retained quota summed duplicate metadata for the same export path. The narrow fix aggregates exact clinic/path once without deleting evidence or changing print workflows;8model SQL/13retention checks pass. Existing print dedup, WhatsApp workflow uniqueness before activation, visit-stage serialization and licensing alignment remain explicit backlog/policy items. Current Select2 already routes session patient search correctly (36checks). The new correction needs one exact-head reaffirmation; prior approval is not silently transferred. No migration/production/release-ref/feature changes.

## Owner licensing/security steering during release preparation

Owner confirmed proprietary rights and third-party preservation; exact main485a198 legal notices are reconciled selectively with current candidate, preserving upstream MIT text and newer lock patches. GitHub repository remains public; npm private:true is publication protection only. All10default-main advisory ranges are addressed in the candidate after tested selector-parser7.1.6 override; remaining5candidate audit entries share an unpatched braces build-tool issue requiring a later Tailwind major compatibility change. Production audit0; no production npm installation/Actions/watchers. Latest evidence records build parity and no silent waiver. This changes the frozen source and requires fresh exact-head review, not deployment under old pins.
