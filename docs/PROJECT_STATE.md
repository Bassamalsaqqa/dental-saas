# Project State

Last updated: 2026-10-21 (M2 deployment evidence reconciled; M2-C isolated discovery).

## Deployed source

- Production and GitHub `release/production-20261017`: `f86376a4a6c8f6bf0fe4471f08093af71e76e042`.
- Exact tree: `4f12c340f8869b4b8f8aff57881c4b29ee98a72c`. M1 and M2 are deployed; do not reapply their corrections.
- PR #18 exact head `7d405af6738ee39f24b15c8be76c8e14cf52fe5a` was independently approved. PR #19 exact head `3f157567e6cfad47a031fc2cbf8fb5c01bb2b9b1` remained COMMENTED; final external decision was explicitly waived, not approved: `OWNER_WAIVED_PR19_FINAL_EXTERNAL_REVIEW`, https://github.com/Bassamalsaqqa/dental-saas/pull/19#issuecomment-6319034871 .
- `main` is historical state, not deployment authority. Original dirty checkout and historical staging evidence are preserved.
- Hostinger PHP 8.3.35 and MariaDB 11.8.9. Production source-only M2 deployment executed no migrations, dependency changes or feature activation. Scheduling remains OFF; pending scheduling migration remains unapplied. WhatsApp was not activated; existing email settings are retained.
- M2 post-switch checks: exact source hashes/modes, 28 PHP syntax checks, 14 core/clinical table fingerprints, 67 schemas, 77 migration records and 49 durable files/configuration preserved. Public HTTP/auth denial passed. Fresh authenticated production browser smoke was unavailable because native browser transport failed; prior isolated native EN/AR/kiosk/document/recovery evidence was reused, not relabeled as live certification.
- Verified encrypted off-host recovery checkpoint: `denta-private-recovery-20261021T164409Z`; separate private key storage. Authenticated decryption/archive checks passed; prior isolated restore reused. DB/files are non-atomic. Entire writable (journals, staging, generated HTML/uploads) must survive backup and source-only rollback; never restore/rewrite customer DB automatically.

## Current engineering scope: M2-C

- Isolated development branch `m2c/invoice-corrections-discovery-20261021`, based on the exact deployed M2 source.
- Targeted discovery and policy proposal only: immutable issued/legacy invoices, linked corrections/credit notes, refunds distinct from noncash credits, clinical/provider obligations preserved, explicit replacement document links and consistent reporting/treasury.
- Existing invoice/payment/void/adjustment and clinical/provider infrastructure must be reused carefully. A noncash credit is not a receipt or cash refund. Existing `adjustment` settlement semantics are not adopted as a credit-note ledger by assumption.
- Core accounting and clinic tax/document policy requires Owner decisions before implementation of financial effects. No automatic provider clawback, historical mutation, clinical unbilling or signed evidence changes.
- No merge, deployment, migration, activation, production writes or customer-data changes without separate Owner authorization. No GitHub Actions; no broad audit/test repetition.
- Preserve odontogram SVG/rendering/snapshots and signed clinical evidence. Salaries, broad redesign and unrelated cleanup are outside M2-C.

## Known operational limits

- Native production browser certification remains outstanding, as recorded above. No fresh power-loss or database restoration test is claimed.
- Ambiguous/historical partial signing evidence and unreferenced artifact retention require controlled operator decisions; no automatic repair/deletion.
- Existing guardrail drift is not a passing gate. No broad guardrail rerun for documentation-only discovery.
- Hostinger MCP unavailable in the current tool inventory; prior authorized operations used SSH. No production operations are part of current M2-C discovery.

## References

- Active map: [START_HERE.md](START_HERE.md)
- Tenant contract: [TENANT_CONTEXT_CONTRACT.md](SaaS/TENANT_CONTEXT_CONTRACT.md)
- Scheduling contract: [DOCTOR_SCHEDULING_CONTRACT.md](SaaS/DOCTOR_SCHEDULING_CONTRACT.md)
- Operations: [OPERATIONS_RUNBOOK.md](OPERATIONS_RUNBOOK.md)
- Finance: [FINANCE_CURRENT_STATE.md](FINANCE_CURRENT_STATE.md)
- Clinical: [CLINICAL_CURRENT_STATE.md](CLINICAL_CURRENT_STATE.md)
- Messaging: [WHATSAPP_CURRENT_STATE.md](WHATSAPP_CURRENT_STATE.md)

Superseded development chronology and old pending-review claims remain in Git history rather than being current release instructions.
