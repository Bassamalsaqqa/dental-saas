# Controlled Denta+ releases

The active deployment procedure is [OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md), with correction-ledger limits in [M2C_RELEASE_PROCEDURE.md](docs/M2C_RELEASE_PROCEDURE.md). Current Owner explicitly authorizes eventual commit/push/deploy; actual release execution remains bound to reviewed candidate commits and runbook preflight gates.

Production authority is the exact Owner-approved SHA/tree on `release/production-20261017`, not historical `main`. Deployed production is installed commit `bc5dbaa6f67dd60f8a074496180a4421facd2ffb` (tree `8d9a18636a18f9634168f5ae100b3b8d8105a481`); maintenance is OFF. The immediate source-recovery baseline is `1b17d06587a07a473f714e296af27f977134208d` (tree `257f1661717e898a430c5bec0d7128af1568ead1`, PR #28). Repository-only documentation or tooling commits do not alter the installed production runtime SHA.

Do not use generic FTP/archive overwrites, Git resets, blanket migrations, or automatic database restores. Preserve deployed configuration, both `.htaccess` files, all writable storage, uploads, signing journals, and customer records. Review exact candidate and migration differences against installed production SHA/tree. Optional features retain their current default-OFF activation state.

A production release needs exact-source integration authority, native staging compatibility, a fresh recoverable checkpoint and essential authenticated postflight. Activated credit history requires correction-aware fix-forward or approved finance containment; old code must not resume financial writes while ignoring it. Keep additive ledger tables and all customer evidence intact during any source recovery.
