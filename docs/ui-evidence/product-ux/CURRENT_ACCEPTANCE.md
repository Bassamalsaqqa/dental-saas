# PR22 current acceptance - review5700597599

**Recommendation: CHANGES REQUIRED. PR remains Draft. No production operation authorized or performed.**

Review 5700597599 at `8d750e10` accepts the recent financial/reception UX in bounded scope. It is COMMENTED, not formal whole-app approval. The accepted design is preserved.

Code checkpoint: `36bf0f0a54808757868e9aad2e690cf32647d917`; tree `fc1411182b0a56aab6b5f266eef6b6f93ab0940f`. The adjacent JSON pins actual changed source hashes. Later documentation-only commits do not imply new runtime acceptance.

## Delivered and actually verified

- cb26e4d9: trusted Review History handoff to the exact same-origin record pinned when the task opens. Submission stays locked; phase/history stay intact. Wrong-record/external destinations cannot borrow the exemption. Generic leaving still warns. Nine completed EN/AR user-activation cases, including real keyboard navigation, produced zero misleading history warnings. One Arabic supplier case became unavailable after its actual full settlement; it is not claimed complete.
- `29d8d784` and `36bf0f0a`: payables lead with identity, status, outstanding and payment fields; secondary facts/help remain accessible through a native disclosure. Four EN/AR desktop,320/390 mobile/landscape task checks; four real manager/viewer disclosure and record/return journeys. Read-only users receive workspace styles without settlement forms/assets. Supplier navigation uses its existing permission.
- Actual two-session stale supplier Mark Fully Paid: exactly one 19 ILS remaining payment; total 20 paid, 0 due, safe_cash attribution, one bulk-payment audit. An explicit repeat skipped without another receipt. Viewer, foreign-clinic and missing-CSRF POSTs did not settle the other owned bill.
- Test-only audit failure after a real SQL ledger insert restored the owned parent, ledger and audit exactly, with transaction depth 0. The service source is unchanged.
- 583 original business/clinical/evidence rows plus both supplier rows remain unchanged. Current fixture did not import original activity_logs from its source; a cross-database audit-ID comparison was invalid and is not a preservation pass. Actual transaction audit and rollback checks are separately evidenced.
- 80 pinned financial-controller method checks,85 token-role checks,57 action foreground checks and unchanged SaaS guardrails pass. Existing stale DOM allowlist advisory retained. No backend/schema/dependency/native-chart/evidence changes from `8d750e10`.

[Consolidated actual receipts, exact source hashes and limitations](review-5700597599.json). [Matched actual UI gallery](controls-gallery.html).

## Confirmed financial acceptance blocker

Two independently authenticated stale browser contexts for the same actor submitted identical 1 ILS partial-payment terms to the existing owned expense endpoint. Both succeeded and created separate receipts; outstanding 19 became 17. Parent locking protects the remaining amount, but does not identify an operation or reject a stale settlement view while funds remain. This is existing backend behavior, not a new frontend regression: current batch changes no financial writer. Do not call it tested idempotency, silently waive it, or claim the current-page UI guard covers other tabs/reloads/direct POSTs.

The reviewer explicitly requested a separately scoped backend protection proposal if demonstrated. The minimum coherent correction proposal is:

1. Durable clinic-scoped operation identity, bound to actor, target, mutation type and canonical persisted financial terms. Exact retries return the original result; conflicting reuse fails closed.
2. A server-derived settlement revision/precondition checked under the existing parent/ledger lock. A second independently opened stale form must review the changed history before submitting another partial payment, even when a balance remains.
3. Request recording and financial/audit writes share the existing owned transaction and lock-order contract. Preserve all current receipt/FX/liquidity/refund calculations and history; do not infer duplicate intent from equal amounts or suppress legitimate later payments.
4. Review every session/API caller for compatibility. A frontend-only/localStorage lock, session nonce alone or using unindexed audit text as uniqueness is insufficient. Durable uniqueness may require additive schema; none is authored/applied here under the no-migration instruction. Required source/schema/API scope needs separate authorization/review before implementation.

This is the specific next engineering decision; no production authorization or risk waiver is inferred.

## Whole-program acceptance still open

The matrix distinguishes inventory, partial render/component observation, actual mutation, denial and preservation evidence. It is not blanket complete. Full role-based clinical/contract/checkout/patient-history journeys, native current-source rehearsal, all persona/state combinations, complete theme/CSP/print/zoom and physical assistive-technology acceptance remain pending. Checkout and other optional gates must not be enabled merely to test them. Persisted themes remain OFF.

The existing treatment-storage migration remains separately production-release-gated; no migration was run in this continuation. No merge/deploy/activation/Actions or customer-data change. No final exact-head all-app review requested while these gates remain open. Historical FINAL_ACCEPTANCE_CLOSURE.md remains unchanged.

## Test fixture custody

Owned test changes are retained, not erased: supplier 800019 now 20 paid/0 due; payable 800018 now 3 paid/17 due. A first harness used the wrong refund interception URL and recorded one 1 ILS refund on owned invoice 800015 (net paid 31/due 69). Exact SQL identified it; subsequent browser-only history tests use independent finance-POST blocking. No original record was repaired/reset. See the JSON for guarded test resumes, corrected currency-account expectation and all evidence limits.
