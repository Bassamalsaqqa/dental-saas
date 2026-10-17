# Finance history and payout release correction

Base candidate: `7d2173bef77b360b00cb496829f58621cc230c6e`.

Corrected permission parsing exposes pre-existing tenant finance deletion and payout paths. Actual production FK metadata confirms finance deletion cascades to cash and payout history. Shared checked transactions now reject history-bearing deletion while allowing unused pending/overdue invoice and pending expense drafts, retain deleted draft snapshots in audit, and serialize payout writers finance-parent first. Paid allocations retain provider, patient and currency identity; zero-sum historical rows and historical attachments remain protected. No schema changes.

AGY implemented the correction; Codex independently reviewed and tightened history/identity preservation. Focused real Hostinger PHP8.3/MariaDB11.8 routed checks passed: legitimate draft deletion; payment and zero-value history protection; atomic mixed bulk rejection; API JSON400 history rejection; cross-clinic denial; overlapping payout requests with one acceptance and one balance rejection; disjoint-clinic progress; checked audit rows; repeat mark-paid creates no payment. Completed EN/AR, booking, patient, clinical-file, Bearer/session and recovery evidence was reused. No broad suite reruns. AGY's extra PHPUnit draft is unaccepted and excluded; the actual SQL/HTTP helper evidence is authoritative.

Release executes zero migrations; dynamic scheduling remains OFF. Preserve production `.env`, every `.htaccess`, `writable/`, uploads and existing customer data. Git fast-forward of an exact release ref is permitted only after protected-path and source checks, with private source/config/data rollback checkpoints. Production database restoration is emergency recovery, never the normal code rollback.
