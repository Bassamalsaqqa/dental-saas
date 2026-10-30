# PR22 material workflow release sign-off

Source freeze: application runtime is unchanged after the patient grid correction. Final exact Git SHA/tree appear in the PR handback and raw-file native receipt. No production migration/deployment is authorized by this table.

**Engineering result:** the executed release-critical synthetic gates below pass. **Independent result: PENDING**, not fabricated approval. **Production: NO-GO until formal review resolves and fresh cutover gates pass.**

| Material changed/reachable workflow | Actual evidence / preserved authority | Engineering | Independent |
|---|---|---|---|
| Treatment storage and catalog | Ordered two-migration native rollout; 44 historical records / 38 unknown bytes and provenance preserved; completed retry27SQL/17purechecks; current final43offeredkeys exact SQLroundtrip with rollback. Prior43routedPOSTevidence retained. | PASS scoped | PENDING |
| Financial receipt/refund/payout surfaces | Native invoice25/payment5/due20; one receipt after uncertain wait; accepted real replay/stale/multiworker/refund/provider/supplier/rollback evidence reused with unchanged business files;72sourceboundaryassertions. | PASS scoped | PENDING |
| Clinical examination and patient context | Actual create/edit/complete/note-sign/signed-denial; exact item draft persistence, local rollback/concurrency receipts; current native create/edit, patient actions and warnings preserved. | PASS scoped | PENDING |
| Contract consent and evidence | Actual native canvasPOST, one signature/document, exact/conflicting retries, immutable edit/delete denial, safe protected HTML download;25native concurrency/fault/reconciliation checks; no automatic charges. | PASS scoped | PENDING |
| Patient attachments | Enabled isolated upload/link/download; exact bytes and tenant/RBAC/CSRF/MIME/size/storage-fault checks; original media preserved. No new replacement/versioning feature or destructive evidence deletion promised. | PASS scoped | PENDING |
| Reception and kiosk | Actual booking with authoritative availability/locks; legitimate same-day kiosk PIN/phone/allowlist confirmation and reset/replay preservation; no scheduling activation. | PASS scoped | PENDING |
| Tenant and platform boundaries | Native real Bearer own200/foreign403, session-only401; global entry, review/cancel/focus/impersonation/exit/stale-tab EN/AR; foreign-file and restricted-role denials. | PASS scoped | PENDING |
| Responsive task controls | Actual AR390clipping reproduced and corrected by three gridtracks; six final EN/ARdesktop/mobile/tablet menu/drawerkeyboard/Escape/focus checks. Finance standard narrow/tablet and200%CSSscale checks. | PASS scoped | PENDING |
| Recovery / source fallback | Synthetic offhost UTCrestore68tables/762rows/91files exact; actual read-only old-source fallback and fixforward68tablefingerprints/51protectedhashes unchanged; oldfinancialwrites503. Stable absolute writable/journal roots required. | PASS scoped | PENDING |
| Static/shared/feature-OFF inventory |133deployed-parity views; current parent traces/presenter checks and explicit gated exclusions. Not202separate pages or202mutationpasses. Added previously omitted shared settlement-asset partial to matrix; no new route/backend. | Scoped parity/exceptions | PENDING |

## Informed Owner release decisions

The Owner explicitly accepted the existing English-only signed/legal print format, incomplete400%/nativebrowserzoom/physicalAT/virtualkeyboardcertification and lack of fullthirdpartyAPImutationrecertification. These remain limitations, not passing tests. This acceptance expressly **does not waive formal review** or clinical/financial safeguards. No historical consent or legal text is rewritten. No further product-policy questionnaire is needed for these named limits.

## Production-only gates after review

Reconcile liveSHA/schema/history/entitlements and stable durable paths; check grants, actual table size/free disk, long transactions/metadata lock waiters; create fresh encrypted offhostbackup and verify isolatedUTCrestore; drain old treatment/financial writers with no mixed versions; execute only the two pinned migrations and reviewed source procedure; essential live read-only checks; contain oldfinancialwrites on source fallback. No blanketmigrate/down, automaticDBrestore, featureactivation or CI.

[Detailed plan](FINAL_RELEASE_READINESS.md) ? [Actual receipts](NATIVE_CLOSURE_ACCEPTANCE.json) ? [Per-view inventory](../../UI_SHADCN_SCREEN_MATRIX.csv).
