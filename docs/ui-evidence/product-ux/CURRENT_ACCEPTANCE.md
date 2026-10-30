# Current PR22 acceptance — completed retry correction and risk-ranked workflows

**PRODUCTION NO-GO.** Completed-journal migration retry is corrected with native27/pure17checks; no fresh independent approval is claimed. The current [consolidated decision](FINAL_RELEASE_READINESS.md), [sanitized current receipt](RETRY_WORKFLOW_ACCEPTANCE.json) and [per-view manual matrix](../../UI_SHADCN_SCREEN_MATRIX.csv) supersede older whole-release status counts while retaining original receipts as scoped historical evidence.

Actual synthetic persistence now covers registration, booking, examination create/edit/complete/sign and signed denial, prescription, contract draft identity/resave, explicit invoice/receipt, contextual examination and platform invoice. EN/AR platform/clinic switching, password reset/replay and role/tenant denials are distinguished from presenter-only/component checks.202 entries are reconciled, not relabeled accepted. Signature/attachment ON workflows, same-day kiosk confirmation and final current-source native HTTP remain disclosed gaps.

The current patch changes only the treatment migration, three presentation templates and two form/booking scripts plus tests/evidence. Financial calculations, original records, native SVG and optional switches remain unchanged. No production access, production migration/deployment/permission grant or feature activation occurred. A new unprivileged synthetic test actor, read-only role and memberships were created only in the owned disposable database.

## Previously accepted financial correction — preserved

Owner authorization and review [5701870044](https://github.com/Bassamalsaqqa/dental-saas/pull/22#pullrequestreview-5701870044) applies to parent `cde62b838f25abd9c1d09e253e3c14dda547c51e`. The earlier finance/reception design is preserved. This report supersedes the earlier proposal-only status; it does not imply whole-app acceptance. The adjacent [sanitized receipt](settlement-replay.json) pins the actual runtime SHA-256 values. The final commit/tree are supplied in the PR and handback, avoiding a self-referential commit hash.

## Implemented

- One additive InnoDB `finance_settlement_requests` table with NOT NULL clinic/key and database UNIQUE(clinic_id, request_key). Request identities bind actor, operation, sorted targets and canonical submitted terms. Completed operations retain their original receipt/outcome and digest. Exact retries return that outcome; mismatched reuse fails closed without another write.
- A server-derived revision covers the current locked parent, finance receipts, provider allocations/payments, applicable corrections/applications/returns and effective base currency. New requests lock all parents in ascending order, then ledger rows, before checking the revision. Same-key replay is checked before stale rejection, preserving legitimate recovery after later activity.
- Request insertion/completion shares each existing financial transaction with receipt, cached balance, liquidity attribution and checked audit. Calculations remain in existing services. Native expense edits also bind the rendered parent preimage and uploaded-file content hashes.
- Existing payment/refund dialogs, provider/staff/supplier drawers and direct confirmations acquire their context before confirmation. Loading, conflict and uncertain-outcome states do not automatically resubmit or silently acquire a fresh revision. Current facts replace the existing context card. EN/AR history handoff and submission locks remain.

## Actual verification

| Gate | Result and scope |
|---|---|
| Overlapping HTTP workers | Two PHP-CGI worker PIDs and DB connections waited on an independent held SQL parent lock. Same-key pair: one receipt and one replay. Different-key stale pair: 200/409, one receipt. |
| Legitimate later payment | Fresh revision/new key allowed another identical 1 ILS partial payment; no amount-based deduplication. |
| Isolation/security | Changed payload, wrong actor/clinic, restricted role, missing CSRF and expired session denied without extra receipt or disclosure. |
| Routed financial writers | Invoice payment, refund, void, noncash closure, provider partial/full and two-parent bulk settlement each committed and exact-replayed without duplicate writes. Actual Arabic mobile supplier Mark Fully Paid produced one receipt. |
| Atomic failure | Request/receipt insertion failures, audit failure and result-completion failure restored parent/ledger/request/audit exactly. Disconnect after result writes but before commit rolled back; the dead connection was discarded and same-identity retry on an independent connection produced one receipt. Terminating only the owned worker after real receipt/audit inserts rolled back all four. |
| Browser | Actual EN1366/AR390 dialogs; actual AR mobile stale conflict and committed/lost-response task stayed locked, Escape did not dismiss uncertainty, Review History navigated without a misleading warning. Exact captured multipart retry returned the existing result. |
| FX/native edit | 1 USD at3.5 retained3.50 base,1.00 original,3.500000 rate and safe_cash. Native expense edit persisted, exact retry retained one audit. |
| Schema | New disposable schema: uniqueness/nullability/index/engine requirements, existing-table refusal, unowned/populated rollback refusal, missing-index503, no-FX/no-total_amount compatibility and attribution. DDL refuses active transactions without implicit commit. |
| Preservation | Original583 business/clinical/evidence rows and2suppliers unchanged. Current-task baseline includes existing audit rows and was compared by original ID and all persisted fields; unchanged. |
| Focused code checks | 10 unit tests/78assertions; reusable SQL8checks;72unmodified controller methods including monetary helpers;85token/57foreground assertions; PHP/JS syntax and whitespace checks. Local SaaS guardrails pass. |

[Actual EN/AR gallery](settlement-replay.html). Historical accepted UX receipts remain in [review-5700597599.json](review-5700597599.json).

## Caller and release compatibility

- Session browser: existing authorized routes and CSRF remain authoritative. Context is `GET /finance/settlement-context?operation=...&targets=...`, no-store. Send its `request_key` and `settlement_revision` unchanged with FormData or the existing JSON transport. Bulk JSON keeps its existing `ids` array.
- Direct service callers must supply identity/revision to `bulkSettle()` or `recordPayout()`. Missing/old clients fail closed (409; missing/incompatible table503). There is no permissive legacy mode. Historical direct-service harnesses without preconditions are obsolete callers, not passing current acceptance.
- Existing `/api/v1` finance CRUD has no alternate settlement endpoint; computed settlement/history mutations remain prohibited by the unchanged FinanceApiMutationService. No new Bearer endpoint, permissions or feature activation was added. Standalone transaction creation and the separate gated checkout identity contract are not claimed to gain universal idempotency from this patch.
- **Not source-only deployable before separately authorized schema rollout.** Apply only `2026-10-29-180000_CreateFinanceSettlementRequests.php` after exact-head review, recovery/native preflight and separate Owner authorization. Never run a blanket migration batch. Treatment-storage/theme/scheduling migrations remain separately gated.
- Drain old writers and refresh open financial forms at cutover. Mixed old writers can bypass the new contract; do not certify overlap. Keep request evidence indefinitely; populated down is refused. A source rollback retains the DB/table/durable files but old code loses these protections, so settlements must be contained during rollback rather than silently reopening unguarded writes.

## Remaining limits

- Independent exact-head financial/security acceptance is pending. Whole-app PR22 journey/theme/clinical/physical-accessibility acceptance remains separately open; this correction does not certify it.
- Local PHP8.2.12/MariaDB10.4.32 tested. Native Hostinger compatibility/rehearsal and production recovery/deployment were not performed in this task.
- The historical whole-frontend lock still reports earlier PR22 backend/coverage drift and this newly authorized scope. Its accepted pins were not weakened or relabeled approved. The focused parent-boundary check and source hashes certify this surgical delta; final whole-PR lock reconciliation remains pending acceptance.
- One first lost-response test reconstructed pre-submit fields; its retry was rejected and its single owned receipt is retained. It is not a replay pass. A separate fixture captured the actual multipart request and passed exact retry. Test redirects/disclosure mistakes were corrected against actual persisted state without replaying committed operations.
- Atomicity claims cover database writes, not the existing filesystem attachment publication lifecycle. No historical receipt, signed clinical evidence, native chart, dependency, optional feature or production record was changed.

No merge, production migration, deployment, activation or GitHub Actions occurred.
