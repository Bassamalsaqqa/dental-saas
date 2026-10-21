# M2 contract signing: protocol and acceptance evidence

Review-only source based on deployed M1 `6908df7c116ccd9d01941724835f071574ef17e1`, tree `c1ed1da1d8fe4f002a605b04f83eb53c9f03fe58`. No integration or deployment authorization.

## Consent and publication protocol

The service owns two short transactions with clinic -> patient -> optional examination -> contract -> item/snapshot/signature/attachment lock ordering. The first captures the draft. Rendering and verified immutable HTML publication happen outside SQL transactions. The second revalidates ownership, complete draft state and captured content, then commits signature, attachment, signed-contract links and audit together on one database connection. Draft edit/delete use the same contract locking boundary and checked writes.

Identical signing retries return the original signature and attachment IDs; conflicting input, tampered consent or incomplete historical evidence fails closed. The new SHA-256 consent fingerprint covers canonical persisted contract terms, ordered items and the frozen snapshot, excluding mutable signing links/timestamps. Historical hashes retain their original, narrower notes/items/snapshot guarantee; no historical evidence is upgraded or rewritten. Kiosk consent retains PIN/session allowlists and records actor type without impersonating staff.

A private operation journal precedes publication. Exclusive staged writes, SHA-256/size checks, file fsync and a no-replace hard link publish HTML under `writable/uploads/clinic_<id>/contract_<id>_<operation>.html`. The journal is under `writable/contract-signing/operations/`; temporary bytes are under `writable/contract-signing/staging/`. New private directories/files require 0700/0600 on POSIX. Canonical paths reject symlinks/aliases. Hard-link support and fsync are required on the target filesystem. Windows records that directory-fsync is unavailable; it does not claim POSIX crash durability.

Database and filesystem are deliberately separate resources. Rollback, process loss or concurrent losers can leave retained, unreferenced private artifacts. A database commit followed by journal failure returns an explicit reconciliation error rather than claiming rollback. No automatic evidence deletion or consent repair occurs.

## Recovery

With the correct application environment and PHP runtime:

```sh
php spark contracts:signing-recovery
php spark contracts:signing-recovery --reconcile
```

The default reports only. `--reconcile` changes private journal state only after verifying current clinic/patient/examination ownership, contract/signature/attachment linkage, consent and artifact hashes, file integrity and operation-linked audit evidence. It never rewrites consent, inserts clinical/financial rows or deletes files. Ambiguous, missing and unreferenced artifacts require an operator decision against audit and backups. Preserve both `writable/contract-signing/` and uploads in recovery checkpoints. No scheduled reconciliation job is introduced.

Generated evidence remains actual `text/html`, served through existing protected attachment download authorization; `contract_pdf` remains the legacy purpose identifier, not a claim of PDF output. Inline active HTML remains prohibited. Original odontogram SVG, snapshot service and print rendering internals are unchanged. Signing creates no completed treatment, invoice, provider liability or clinical charge.

## Focused independent verification

Codex executed 25 real-MariaDB/storage acceptance checks on the identity-verified synthetic private Hostinger clone, using `tests/regression/M2ContractSigningSql.php` and its explicit guarded bootstrap. Coverage includes simultaneous real PHP signers, an independent clinic-lock probe during rendering, same-ID retries, conflicting retries, immutable-term/item/snapshot tampering, render/storage/link/audit faults, rollback, postcommit journal failure/reconciliation, signing versus draft edit/delete, tenant mismatch, actual EN/AR document content, and preservation of pre-existing rows, schemas and durable files. Fixtures and evidence are retained and explicitly owned.

An additional 54 routed CodeIgniter web-kernel assertions passed with actual sessions, CSRF, permissions and tenant filters: anonymous/denied/foreign-clinic direct POSTs, draft edit, signing/retry/conflict, signed edit/delete protection, file download authorization, inline HTML denial and EN/AR show/print content. This is not wire HTTP, streamed-body or interactive browser certification. All 542 original synthetic rows and 68 schemas remain unchanged; additional owned synthetic fixtures remain.

The actual report and `--reconcile` CLI paths also passed on owned synthetic journals; the latter made journal-only changes with all 542 original rows and 68 schemas still preserved. Six Windows scratch-storage checks passed for publication, paths, size/hash and journal transition/idempotency/conflict behavior. Final bounded independent source review found no remaining concrete publication or ownership blocker; this is not external GitHub approval. Raw manifests and failure evidence are retained privately without customer data or credentials in this PR.

## Remaining acceptance and release gates

- Fresh exact-head independent clinical/security review is required before composition or integration.
- Interactive EN/AR/mobile and kiosk pilot verification remains separate. Existing print-template labels are English; Arabic content remains intact. No print/SVG redesign is included.
- Existing legacy or partial signing evidence fails closed and needs manual investigation; no automatic historical repair is authorized.
- Unreferenced evidence consumes storage until an explicitly approved retention decision. Rendering may be repeated under contention, while consent commits once.
- No schema/configuration/dependency change or migration. New private storage paths must be writable, included in backups, and protected from public access.
- Production, protected files, original checkout and forensic staging are preserved. Scheduling/WhatsApp remain OFF. Owner authorization and a fresh recovery checkpoint are required for any release.
