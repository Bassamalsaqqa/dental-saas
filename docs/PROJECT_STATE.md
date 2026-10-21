# Project State

Last verified: 2026-10-20 (M0 read-only observation).

## Deployed source

- Production and GitHub `release/production-20261017`: `84990429a65f37017cf5bde1e389c1eac6986e6e`.
- Exact tree: `bcfa2023a81107a44896a711d88a25865cf691e8`.
- `main` is historical integration state, not deployment authority. Do not merge old PRs or pull main into production.
- Stage 0 financial/history/security changes and appointment/settings fixes are deployed. Do not reapply them.
- Hostinger uses explicit `/opt/alt/php83/usr/bin/php` (8.3.35); database runtime reported MariaDB 11.8.9.
- Production has 67 tables and 77 migration-history records. Dynamic doctor scheduling remains OFF; `2026-09-26-000001_AddDynamicDoctorScheduling` remains deliberately unapplied.
- Production `.htaccess` has the expected protected local modification. Preserve it, `.env`, uploads and writable storage.

## Current engineering scope

M0 baseline reconciliation is read-only. M1 candidates are developed in isolated worktrees against the deployed release: inventory concurrency/COGS atomicity, clinic-local notification formatting and tenant-safe joined references. No candidate is integrated or production-authorized by this document.

The 2026-10-19 unified engineering plan supplied by Owner governs this program. Confirm source findings before implementing them; distinguish execution evidence from hypotheses. Preserve odontogram SVG/rendering/snapshots, signed records, issued invoices/payment histories and provider obligations. Staff salaries and invoice cancellation policy require separate product rules. No automatic billing from contract signing or historical data remapping.

M2 review evidence: [Contract consent publication/recovery](M2_CONTRACT_SIGNING_EVIDENCE_20261021.md). External exact-head approval and separate Owner release authorization remain required.

## Verification and release boundaries

- Focused SQL/concurrency tests use identity-verified synthetic databases only; no local business database or customer writes.
- Latest deployed maintenance receipt records preserved schemas/data/configuration/uploads, 167 focused settings rendering safeguards and EN/AR synthetic browser checks. This is prior evidence, not a claim that all current workflows were retested.
- M0 login and two booking scripts return HTTP 200; both scripts retain mode 0644. No recent application log file after 2026-10-15 was available, so absence of recent log errors is not a health guarantee.
- Baseline SaaS guardrails fail on six existing DOM sinks, controller-query allowlist drift and four font matches. Do not widen allowlists or call this a passing gate. IonAuth snake-case scan currently has zero matches; camel-case coverage remains incomplete.
- Hostinger MCP is unavailable in the current tool inventory; authorized read-only checks use established SSH. Cron inspection is unavailable in the restricted shell and has not been certified.
- No GitHub Actions. Source integration, migrations, activation and deployments require separate Owner authorization. A release must pin SHA/tree, enumerate migrations, verify recovery, protect configuration/storage and include source-only rollback when schema/data are unchanged.
- Existing recovery checkpoint `denta-private-recovery-20261019T072526Z` and its verified restore evidence are preserved outside the repository. They must be freshly assessed before any future authorized deployment; DB/files backup is not atomic.

## References

- Active map: [START_HERE.md](START_HERE.md)
- Tenant contract: [TENANT_CONTEXT_CONTRACT.md](SaaS/TENANT_CONTEXT_CONTRACT.md)
- Scheduling contract: [DOCTOR_SCHEDULING_CONTRACT.md](SaaS/DOCTOR_SCHEDULING_CONTRACT.md)
- Operations: [OPERATIONS_RUNBOOK.md](OPERATIONS_RUNBOOK.md)
- Finance: [FINANCE_CURRENT_STATE.md](FINANCE_CURRENT_STATE.md)
- Clinical: [CLINICAL_CURRENT_STATE.md](CLINICAL_CURRENT_STATE.md)
- Messaging: [WHATSAPP_CURRENT_STATE.md](WHATSAPP_CURRENT_STATE.md)

Superseded development chronology and old pending-review claims remain in Git history rather than being current release instructions.
