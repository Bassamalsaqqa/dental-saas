# Controlled Denta+ releases

The active procedure is [OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md), with correction-ledger limits in [M2C_RELEASE_PROCEDURE.md](docs/M2C_RELEASE_PROCEDURE.md). This file grants no deployment or migration authorization.

Production authority is the exact Owner-approved SHA/tree on `release/production-20261017`, not historical `main`. Do not use generic FTP/archive overwrites, Git resets, blanket migrations or automatic database restores. Preserve deployed configuration, both `.htaccess` files, all writable storage, uploads, signing journals and customer records.

PR #20 is a consolidated development candidate. Its migration inventory must be reviewed explicitly: the operational correction ledger, the tenant email constraint replacement, and the existing pending scheduling expansion are separate changes. Do not run any of them in production without separate authorization. Scheduling, WhatsApp and corrections stay OFF.

A production release needs exact-source integration authority, native staging compatibility, a fresh recoverable checkpoint and essential authenticated postflight. Activated credit history requires correction-aware fix-forward or approved finance containment; old code must not resume financial writes while ignoring it. Keep additive ledger tables and all customer evidence intact during any source recovery.
