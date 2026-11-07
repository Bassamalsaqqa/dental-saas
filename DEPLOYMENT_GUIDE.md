# Controlled Denta+ releases

The active procedure is [OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md), with correction-ledger limits in [M2C_RELEASE_PROCEDURE.md](docs/M2C_RELEASE_PROCEDURE.md). This file grants no deployment or migration authorization.

Production authority is the exact Owner-approved SHA/tree on `release/production-20261017`, not historical `main`. Do not use generic FTP/archive overwrites, Git resets, blanket migrations or automatic database restores. Preserve deployed configuration, both `.htaccess` files, all writable storage, uploads, signing journals and customer records.

PR #27 is already deployed (installed `5b7c0688a1afa4626a4ed913e759a763eb282c12`, tree `b1ec2c3e3b3472c5b62d11734d94fa8e1dc6941a`). Review the exact new candidate and migration differences against the installed SHA/tree; an old development plan is not a release inventory. Optional features retain their current activation state.

A production release needs exact-source integration authority, native staging compatibility, a fresh recoverable checkpoint and essential authenticated postflight. Activated credit history requires correction-aware fix-forward or approved finance containment; old code must not resume financial writes while ignoring it. Keep additive ledger tables and all customer evidence intact during any source recovery.
