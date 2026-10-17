# Finance cash reversal source candidate

Verified on 2026-10-17. This draft candidate is packaged directly on main `8be7ad67a4a2810daea55f23c307b206e09cfdf4`; no merge or production release is authorized. The candidate originated on the earlier PR #4 baseline `0986fefea865e5e0acb858ee57eabd2e69c7a7d2`. All three runtime Git blobs are byte-identical between that baseline and main, so this package includes none of PR #4's tests, scripts or evidence.

## Scope and resulting behavior

- Collected-cash aggregation excludes noncash write-off/adjustment entries and subtracts refunds and voids, with explicit clinic and invoice predicates.
- Refund and void acquire a clinic-scoped parent invoice lock before reading reversal ceilings. Refund is bounded by global remaining collected cash and by the selected receipt remainder when supplied. Void is bounded by the smaller remainder; an earlier unlinked refund can therefore produce a partial remaining void.
- Both endpoints reject an existing transaction or poisoned transaction status before beginning work. They leave the caller's transaction untouched. Only a fresh owned transaction can append the reversal, synchronize invoice status, record the checked audit and commit. Begin, append, status update, audit, transaction status and commit failures cannot return success.
- Parent lock SQL uses the connection's prefixTable and escapeIdentifiers helpers. Clinic and invoice values remain bound parameters. Public catch responses are generic; internal exception detail remains in server logs.
- UI and API finance reference checks reject an examination belonging to another selected patient. Two existing early check-scan validation exits now roll back their open transaction.

## Source review evidence

The reversal routes are session-authenticated UI endpoints under the finance route group (`app/Config/Routes.php`): POST `finance/(:num)/payments/refund` and POST `finance/(:num)/payments/(:num)/void`, with tenant/subscription and finance edit checks. IonAuthModel::setSession supplies user_id, which the real ActivityLogger requires. Missing audit identity fails closed. No new Bearer reversal route or actor fallback was added.

FinanceModel, FinancePaymentModel and ActivityLogModel use the same default database group as the controller: system/Model.php constructs through Database::connect($this->DBGroup), and system/Database/Config.php shares connections by group. The audit participates in the same transaction under this existing construction. This is a source trace; the standalone harness does not execute the real database driver or audit model.

CodeIgniter pseudo-nested transactions only decrement transDepth for an inner commit/rollback. The new entry guards prevent an inner method from reporting durable success or leaving its writes in a caller's transaction after a reported failure. Existing two-decimal arithmetic and schema representation are retained; JOD precision is not certified.

## Rejected changes and remaining gates

The proposed single/bulk/API history-preserving deletion helpers were rejected and restored to baseline behavior. They had unchecked transaction boundaries, incomplete audit/relationship checks and uncoordinated charge-link writers. No half-applied deletion guard remains. Hard-deletion preservation is still an open finance blocker.

This patch does not make all finance writers cooperate with the invoice lock. Payment, mark-paid, write-off, provider payout, bulk settlement, invoice editing and clinical charge claims still require coordinated atomicity and real routed/multi-worker tests. Durable idempotency and JOD three-decimal representation remain unresolved. No new schema, migration, dependency, route or configuration is introduced.

The approved synthetic schema differs from production: staging finance_payments lacks adjustment, production finances.created_by and staging-only invoice lifecycle fields differ. No ALTER or production migration was run. Representative financial write-off/reversal certification needs a reviewed parity-compatible synthetic baseline; the current synthetic schema cannot establish production parity.

## Focused verification

Command: `C:/xampp/php/php.exe -n scripts/verify_finance_preservation.php`. Result: **131 assertions passed, 0 failed**. Four PHP files pass `php -n -l`; `git diff --check` passes. AGY supplied implementation and test candidates; Codex inspected the actual diff, rejected unsafe deletion changes, corrected incomplete fixtures and independently executed these checks. Timed-out AGY relay exits were not treated as proof of completion.

The standalone script lives outside PHPUnit discovery. It executes the actual refund/void controller and cash aggregate model methods using narrow doubles, with no framework bootstrap or database connection. Cases cover cash/receipt ceilings, write-off-only cash rejection, reversal failures, nested-transaction preservation, poisoned status, empty/nonempty table prefixes and sanitized exception responses with internal logging.

These results prove PHP method flow and query assembly only. They do not certify real routing/authentication, SQL aggregate execution, engine locking, concurrent workers, recovery, schema parity or full finance release readiness. Production release remains blocked by the unresolved gates above and the broader release review. Scheduling remains OFF.
