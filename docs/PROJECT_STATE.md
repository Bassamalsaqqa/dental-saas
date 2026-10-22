# Project State

Last updated: 2026-10-22 (protected M2; consolidated autonomous development in Draft PR #20).

## Deployed source

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
- No merge, deployment, migration, activation, production writes or customer-data changes without separate Owner authorization. No GitHub Actions; no broad audit/test repetition.
- Preserve odontogram SVG/rendering/snapshots and signed clinical evidence. The Owner expanded nonproduction scope to the reconciled roadmap; financial/legal/clinical policies are not invented. See the compact execution matrix in `refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md`.

## Known operational limits

- Native production browser certification remains outstanding, as recorded above. No fresh power-loss or database restoration test is claimed.
- Ambiguous/historical partial signing evidence and unreferenced artifact retention require controlled operator decisions; no automatic repair/deletion.
- Existing guardrail drift is not a passing gate. No broad guardrail rerun for documentation-only discovery.
- Hostinger MCP remains unavailable; current SSH runtime/schema checks were read-only. Production SHA/tree were reconfirmed unchanged. No production writes are authorized by this campaign.

## References

- Active map: [START_HERE.md](START_HERE.md)
- Tenant contract: [TENANT_CONTEXT_CONTRACT.md](SaaS/TENANT_CONTEXT_CONTRACT.md)
- Scheduling contract: [DOCTOR_SCHEDULING_CONTRACT.md](SaaS/DOCTOR_SCHEDULING_CONTRACT.md)
- Operations: [OPERATIONS_RUNBOOK.md](OPERATIONS_RUNBOOK.md)
- Finance: [FINANCE_CURRENT_STATE.md](FINANCE_CURRENT_STATE.md)
- Clinical: [CLINICAL_CURRENT_STATE.md](CLINICAL_CURRENT_STATE.md)
- Messaging: [WHATSAPP_CURRENT_STATE.md](WHATSAPP_CURRENT_STATE.md)

Superseded development chronology and old pending-review claims remain in Git history rather than being current release instructions.
