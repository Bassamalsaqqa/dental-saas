# Remediation checkpoint 02 — source integration evidence

Production is preserved at `97cd12c67b3f426997d5f80f769031fa35a9f0c0`, tree `0f2b978af72a5ec31788b7294043d527b309a266`. This checkpoint does not authorize integration or deployment. Dynamic scheduling remains OFF. No migrations, production writes, GitHub Actions, or original-checkout edits were performed.

## Exact component heads

| PR | HEAD | Review status at preparation |
| --- | --- | --- |
| #9 framework | `c1d2218370e45dbca498a9230467846e4539d2a6` | Bounded COMMENTED review; formal approval requested |
| #10 reporting | `f5a15c775c6a986062eb2002b02751fede17844b` | Reproducible SQL fixture added; fresh review requested |
| #11 settlement | `27b333ac40c1361d88bdac6415733dbed54a3737` | Optional-FX correction pushed; fresh review required to clear prior CHANGES_REQUESTED |

Core composition commit `ceb58f88625aa64814501597b0a6c23e0d42b507`, tree `7192c4b575c266e5271f1aee5207b4fadb9c6e74`, has those three exact heads as parents. This stacked correction adds only the bounded finance API/reminder containment and its verification sources. It introduces no dependencies, migrations, configuration changes or scheduled jobs.

## Existing defects and bounded containment

Both release-sensitive defects were independently reproduced against the baseline behavior with new synthetic fixtures; neither was introduced by PR #11.

- Finance API update could convert a paid invoice into an unpaid expense while retaining its real receipt. Updates now own a transaction, lock the clinic-owned parent before related current reads, reject direct computed/type/status/history changes, preserve identical roundtrips and paid metadata edits, and audit successful corrections atomically. Truly unpaid, unlinked document basis edits still use the actual model totals. Explicit FX inputs retain their validation meaning and six-decimal rate precision. Invoice creation rejects fabricated settlement history; direct-payment and paid-expense creation retain their existing paths.
- Unused invoice deletion could leave a claimable payment reminder. Deletion now reconciles unsent reminders in the same parent-owned transaction and retained audit; processing, sent or attempted demand history blocks deletion. Claims lock parent before targeted queue row, reject missing/foreign/settled/stale demands, validate clinic-specific formatting without session/cache dependence, and pass the freshly claimed payload to transport. Payloads and attempt history are retained.

Ordinary receipts remain available. A receipt committed after a valid reminder claim cannot recall an external message already being sent; validation guarantees apply at claim time. No database locks span transport.

## Focused independent verification

Executed with PHP 8.3.35 / MariaDB 11.8.9 in the non-webserved private CLI copy using only the identity-marked synthetic staging database and its restricted owner. Production and the public staging installation were not changed.

- PR #11: 26 actual SQL assertions across full-FX and temporary no-FX schemas, including attribution, idempotency, tenant rejection and audit-failure rollback; 10 focused unit tests / 78 assertions.
- API: 17 actual SQL assertions covering unpaid recalculation, conflicting/coherent FX inputs, six-decimal precision, protected history, metadata preservation, identical roundtrip, foreign-clinic rejection and audit rollback.
- Actual API controller: JSON 409 for the reproduced paid-history tamper, exact parent/receipt preservation, and rejection of forged invoice settlement creation.
- Reminders: 29 actual SQL assertions covering cancellation/history, batch rollback, stale/missing/foreign claims, malformed keys, clinic-local formatting, transaction ownership and unaffected unrelated email claims. No transport was invoked.
- Two deterministic concurrent SQL cases: deletion versus claim, and settlement versus API basis edit. The contender waited on the parent lock and rejected after the first transaction committed. Settlement retained one payment and its audit.
- Reused completed framework provenance/security/runtime checks and the 23-assertion reporting SQL golden fixture. Reporting implementation is unchanged from the previously verified composition: patient cash 138; external inflow 160, outflow 47, net 113. Transfers remain balanced treasury legs. These cash measures are not universal profit or salary accounting.

Reproduction requires an explicit private bootstrap. `scripts/verify_finance_release_guards_sql.php` checks canonical paths, exact DB/user/host/port, marker, authenticated grants and scheduling OFF. `--verify-isolation` performs only the guard checks; without a bootstrap it exits 2 before connecting. Its two fixture classes were independently executed; concurrency helpers are separate to avoid broad repeated suites.

## Preservation and release gates

Before/after verification preserves all 275 baseline synthetic rows and 68 table schemas; only newly owned synthetic fixtures were added/removed. Production `.env`, root/public `.htaccess` hashes remain unchanged. The original 24 dirty files and 238 corrupted-staging metadata entries remain unchanged.

Required next steps: exact-head independent approvals for the corrected components and this stacked integration result, then separate Owner release authorization with a fresh verified recovery checkpoint. No merge or deployment is authorized by this evidence. The original unapproved historical-data reconciliation, commission changes and salary product decisions remain outside this release.