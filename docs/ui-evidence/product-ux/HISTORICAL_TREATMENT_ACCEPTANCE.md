# Historical treatment compatibility ? bounded correction

**Development correction verified locally and on native Hostinger; final independent acceptance pending. Production NO-GO.** Parent: `c24038a65dedddb04620f7ec0a5718592667cb47`. [Exact runtime hashes and sanitized receipts](HISTORICAL_TREATMENT_ACCEPTANCE.json). Review5705127812 authorizes this scope; its COMMENTED disposition is not final approval.

## Correction and preserved meaning

The former migration refused actual persisted ENUM-index-zero values. The corrected forward-only path preserves the 38 empty strings exactly. It never substitutes NULL, `other`, healthy, a procedure or a recoverable original invalid string. MariaDB already lost that invalid input; only the persisted empty bytes and original numeric index0 are knowable.

Before ALTER, an immutable private journal records every ID/clinic, HEX value, original numeric ENUM index, full-row digest, source DDL and database identity. Exclusive hard-link publication, fsync and checked hashes precede DDL. After COPY, all44 full original rows and exact indexes/constraints/column metadata must match before a completion record is durably published. Private files live under complete `writable/recovery/treatment-type-storage/`; both staged and published links must be retained, backed up and securely retrieved. No clinical narratives are copied into the journal. It is integrity evidence, not a clinical classification or a new business table.

A crash after ALTER cannot be rolled back transactionally. Recovery requires the original journal, exact original rows and converted table definition. Missing evidence, row drift or unreviewed schema changes fail closed. Completed reruns retain later legitimate records. Never narrow to ENUM or automatically repair/restore clinical rows.

List DTO, detail, patient history and dynamic print now say **Unknown historical treatment (unclassified)** in EN/AR. Existing signed/generated documents and stored snapshots are untouched. Edit offers a transport-only ?keep unknown? choice: it is never stored or included in the clinical catalog. Unrelated authorized edits omit type/name from the update; a locked current type check rejects concurrent reclassification. Explicit clinician category selection still follows existing validation/audit; no new classification policy is invented.

## Actual acceptance

| Gate | Actual result |
|---|---|
| Failing-before | Native exact-schema synthetic38empty+valid values reproduced the former pre-ALTER refusal; records/schema stayed unchanged. Original receipt retained outside Git. |
| Historical conversion | Native PHP8.3.36/MariaDB11.8.9:44records,38index0,4crown/1cleaning/1extraction; all original full rows/bytes preserved; nine linked clinical/financial/evidence tables unchanged. First conversion2.355s. |
| Native locks | Final fresh-prefix check: observed metadata-lock wait; concurrent writer waited0.606s; migration2.478s. Configured metadata lock bound5s; full timed refusal7.264s includes setup. Session timezone/mode/wait restored. These tiny-fixture results are not a production downtime guarantee. |
| Failure recovery | Injected pre-ALTER refusal and post-ALTER committed failure retained all records. Retry completed from original journal. Deliberate unexpected index on the incomplete conversion was rejected before completion; removing only that synthetic test index allowed recovery. |
| Ordered combined migrations | Fresh actual67-table schema, authentic FK constraints, synthetic rows only; pinned treatment then request migration,77?79history,67?68tables. All preexisting table rows unchanged. Widening2.239s; request CREATE0.010s. No unrelated migration ran. |
| Current catalog |43/43 real authenticated native CI4 creation POSTs; SQL verifies exactly43distinct types, one row each. Existing eight legacy keys also roundtrip in a rolled-back SQL transaction. This storage check is not approval of old unguarded financial writers. |
| Unknown edit | Actual local and native browser saves persist unrelated text while preserving empty type and original stored name. Native original44fixtures remain unchanged; edit uses a separate owned row. Validation, missing CSRF, restricted role and cross-clinic GET/directPOST rejected; forged text never persisted. |
| EN/AR |25actual native observations including login and12routes per locale at1366/390; explicit unknown detail/edit/print labels and selected keep-state. No JS errors observed. Route success is not mutation certification. |
| Finance integration | Accepted finance source unchanged; combined native service produces one20-unit receipt/oneaudit/safe_cash, same-result exact replay and stale-key denial. Existing accepted HTTP/multiworker/rollback/FX receipts reused, not rerun broadly. |
| Preservation |583 original local business/clinical rows unchanged. Native original44 and all original tables across DDL preserved. Native SVG, signatures, contract services, finance calculations, dependencies and production remain unchanged. |
| Source gates |62catalog/localization checks,72finance boundary checks,11PHP lints, whitespace and strict authorized-source/inventory guard pass. Full frontend acceptance guard is still NOT PASS:202pending entries, not202separate pages or proven defects. |

[EN native detail](historical-unknown-en1366.png) ? [AR native edit](historical-unknown-ar390.png). These are disposable synthetic screens.

## Scope and release limits

This closes the demonstrated historical-storage defect and its bounded native integration tests. It does not certify every role?route?state, physical assistive technology, universal zoom/CSP/print behavior or all201prior pending view entries (202after registering the authorized print-label exception). Previously accepted clinical/finance workflows remain supported by their existing receipts; no whole-product external approval is manufactured.

Private raw harnesses, original failed assumptions and evidence remain outside the repository. The browser harness first assumed treatment1 existed, but authentic AUTO_INCREMENT starts the synthetic fixture at66; the404was corrected by read-only ID lookup. A synthetic POST socket-close was reconciled against actual SQL before continuation; no duplicate retry. The combined runner initially printed a stale cached67-table count; independent SHOW TABLES verifies68. No application logic was changed to satisfy either harness assumption.

Final independent exact-head review, whole-PR acceptance, a release-time fresh-enough recovery checkpoint and separate Owner migration/deployment authorization remain mandatory. Themes and all other optional feature gates stay OFF; existing contract/signature entitlements are not disabled. No production action occurred.
