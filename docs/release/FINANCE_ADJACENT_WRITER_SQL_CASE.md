# Adjacent finance writer correction and focused SQL case

2026-10-17. Baseline PR #6 HEAD `402da9c5022e0749b4d72cafd366f748f9d88e4b`. This follow-up correction requires fresh independent GitHub approval. Scheduling stays OFF. No database connection, migration, production write or deployment occurred in this implementation lane.

## Actual source change

`Finance::markAsPaid`, `addInvoicePayment`, `closeInvoiceBalance` and `bulkMarkAsPaid` now use the same clinic-scoped parent invoice/expense lock as refund/void. A checked owned transaction covers remaining-balance reads, settlement append, parent synchronization and checked audit/commit. Existing or poisoned transaction contexts fail closed without changing caller state. Identifier quoting/prefixing and bound clinic/id values are retained. Bulk locks positive deduplicated IDs in ascending order. Missing/foreign rows are skipped consistently with the existing scoped batch behavior; they do not cause the whole batch to fail. Any actual write/audit failure rolls back the batch. Public exception responses are generic; internal details remain in logs.

The bulk non-invoice branch still preserves its previous parent-only paid-status behavior. It does not create expense settlement ledger entries and must not be certified as equivalent to single expense settlement. Bulk invoice entries also retain their existing lack of liquidity-account attribution. These are visible release risks, not silently repaired here.

AGY implemented the patch. Codex independently read the full diff, linted the changed PHP, and ran `git diff --check`. Existing actual-method verifier: 131 assertions passed. A temporary no-database harness executed the four new methods in success, existing-outer-transaction and insert-failure modes: 12 scenarios passed. These checks establish PHP control flow only, not actual SQL/concurrency behavior.

## One practical real MariaDB certification case

Target the separately authorized Hostinger synthetic staging website/database on its actual MySQL port 3306. Port alone is never an isolation proof. Before any request, the lead must verify exact stage domain/docroot, instance/schema identity, synthetic-only marker, schema-specific grants with no production grants, candidate source hash, required schema, and scheduling OFF. Do not reuse production cookies or credentials. Preserve baseline snapshots and all existing stage evidence. No new schema is needed for barriers. Root controls the execution window; no SQL test has run in this lane.

Use independently authenticated synthetic sessions per worker, with separate cookie jars and CSRF values. A shared PHP session could serialize requests before the database and give false confidence. The web server must support parallel requests. The runner uses an external start barrier and an independent database observer; no test-only route or deployed fault injector is required.

1. Seed or identify a new synthetic clinic A invoice total 100 with a genuine payment of 40, plus a disjoint clinic B invoice and synthetic operator sessions. Record IDs and complete parent/ledger/audit/link snapshots. Existing clinic/customer fixtures are forbidden.
2. Observer acquires the clinic A invoice parent `FOR UPDATE` in its own transaction. Launch two actual routed `POST /finance/{id}/payments` requests, each amount 60, with different sessions behind a start barrier. Confirm both requests are pending at the same parent lock, rather than serialized by session/HTTP infrastructure. While A is held, a clinic B payment must complete independently. Then release only the observer's own transaction.
3. On invoice A, exactly one 60 payment should commit, the other should return an orderly failure or refuse excess settlement. Raw signed ledger settlement must equal 100, cash must equal 100, parent amount_paid 100 and balance_due 0. There must be exactly one new successful settlement audit, no partial second append, and all patient/clinical/provider/file links must remain unchanged. Verify raw SQL aggregates; a max(0) application getter can hide a negative ledger and is not sufficient evidence.
4. Use a separate fresh synthetic invoice total 100/payment 40 for write-off versus refund: launch actual `POST /finance/{id}/close-balance` and `POST /finance/{id}/payments/refund` amount 40 with the same observer/start-barrier technique. Valid serial outcomes differ: write-off 60 then refund 40 produces settled 60/balance 40; refund 40 then write-off 100 produces settled 100/balance 0. The result must match one valid serial order. Raw cash becomes 0; positive settlements include adjustment while cash excludes it. Parent amount_paid equals signed payment+adjustment-refund-void total; balance_due equals max(0,total_amount-amount_paid). Two accepted mutations require two matching audits.
5. Also reject a clinic A session targeting clinic B's invoice and verify zero append/audit/parent change. Record actual HTTP statuses instead of assuming a 403/404 contract; current methods can return success=false JSON with HTTP 200.

Record source SHA/tree, server/runtime/database versions, session identities (no secrets), start/finish times, observer wait evidence, redacted HTTP results, before/after signed totals and complete relationship/audit reconstruction. A lock timeout or deadlock must fail closed with no partial writes; do not promise no deadlocks merely from sorting parent IDs. Do not reset the entire database or delete existing records to repeat cases.

## Remaining genuine finance gates

- `addProviderPayoutPayment` and `markProviderPayoutPaid` still lack coordinated payout locking/atomicity.
- UI/API document editing and UI/API/bulk hard deletion remain outside this lock/preservation correction. Cascade deletion and orphaned clinical/treasury/file relationships remain risks. A safe solution need not assume a particular new soft-delete architecture.
- `ClinicalChargeLineService::prepareInvoiceSelection` runs before the invoice transaction; `markInvoiced` reports query success rather than exact claimed-row count. Competing claims can race.
- Durable idempotency/replay protection, JOD three-decimal representation and representative real routed/SQL/rollback verification remain open.
- The four patched settlement endpoints do not establish full-finance release readiness or resolve the separate clinical-file and dependency gates.
