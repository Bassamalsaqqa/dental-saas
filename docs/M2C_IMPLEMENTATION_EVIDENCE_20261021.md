# M2-C implementation checkpoint: draft, not release accepted

Base production: f86376a4a6c8f6bf0fe4471f08093af71e76e042 / tree4f12c340f8869b4b8f8aff57881c4b29ee98a72c. No production operations in M2-C. InvoiceCorrections.enabled=false; operational document mode only. No manager permission grants or scheduling/WhatsApp activation.

Implemented draft: append-only correction/line/approval/sequence/replacement/application/return tables; exact two-decimal position; immutable original invoice and provider/clinical preservation; provider manager approval rebound to locked authority and financial proof; explicit cash-backed samepatient/clinic/currency application and immutable full return; shared settlement/refund/report/reminder/API projection; restricted API summary; escaped Arabic/English views, operational notice, manager impact preview, default-off invoice link. Physical correction deletion is not provided.

Owner policy/source sharing approvals recorded in session: explicit applications or refunds, no automatic transfers, manager-approved provider-linked issuance, operationalfirst/fiscalvalidation later; AGY may receive M2-C source only (no env/credentials/backup/customerdata). Source and production integration/deployment remain forbidden absent separate approval.

## Focused evidence executed by Codex

- 68 exactmoney/position checks, including credit-funded replacement liability and cash/noncash separation.
- 135 source language-key/UTF8/corruption/DOMsink/defaultOFF checks. Not browser authorization evidence.
- Actual MariaDB10.4.32, a NEW confined datadir, bind127.0.0.1:3307. Synthetic database m2c_synthetic_acceptance, limited m2c_test identity; every SQL harness checks DB/port/datadir/user before fixture writes. Existing business/healthy/corrupt staging datadirs untouched.
- Only M2-C migration applied to disposable schema with14 minimal synthetic base tables +7 new ledger tables; approval permission registered, zero role grants. No blanket migrate or scheduling migration. This is NOT full Hostinger11.8.9/FK parity certification.
- 20 actual SQL checks: issue, equivalent retry/conflict, foreignclinic denial, manager approval requirement, cash backing, application/return retries and original invoice/provider records unchanged.
- 14 actual SQL/concurrency/fault checks: two real PHP/MariaDB processes, one fresh proof commits and other rejects; no duplicate credit; revoked permission denied; injected SQL audit failure rolls back correction and strict connection remains failedclosed; earlier invoice rows preserved.
- 8 targeted actualSQL checks: manager permission revoked after approval denied under current locked check; no unauthorized credit, original FXcap independent of rounded base, credit-funded replacement liability, zero invented cash/refund, explicit return AR/source liability.
- Six actual CodeIgniter views rendered EN/AR with synthetic service data and a MINIMAL acceptance shell.18 browser layout checks at1365/390/320px passed direction, no corruption/pageoverflow. The harness explicitly sets the language service; Arabic checks require Arabic text, not direction alone. This does NOT prove fullshell login/routed/CSRF/subscription/permission/session/Bearer behavior. Original signed evidence and SVG assets unchanged.
- 26 read-only real-SQL adapter checks across five retained corrected invoices: existing FinanceSettlementService locked reads match correction-aware balances, historical settlement, cash refund capacity and patient liability; original invoice rows unchanged. This does not certify receipt/refund/treasury writers.
- Five read-only real-SQL-backed clock checks: distinct clinic year/day boundaries, explicit spring/autumn DST offsets and invalid clock rejection. Posting timestamps/sequence now use clinic timezone; approval expiry stores/compares UTC. Actual issuance/expiry boundary and historical-asof SQL writer acceptance remain pending.
- Changed PHP syntax and diff whitespace checks passed at checkpoint; final reviewed head must be pinned separately.

AGY: delegated schema/service bounded packages repeatedly timed out with no completed source artifact; no claimed implementation success. No-tool hello responded. Larger bounded no-tool source review calls returned empty responses; the latest small clock-rule review timed out without a response; NO independent approval or no-blocker conclusion is claimed. Codex fallback corrected sourceproof collision, returnedcredit liability, document shape, locked authority, FXcap and manager-preview shape. AGY responses/result metadata retained privately. No production data was shared.

## Acceptance still required / concrete release blockers

1. Full actual routed authorization/CSRF/tenant/session-Bearer isolation and fullshell EN/AR/mobile acceptance; current rendering-shell checks do not substitute.
2. Full existing single/bulk receipt, cashrefund/void and treasury/report/reminder integration SQL checks with representative supported schema and realFK contracts. Current minimal SQL harness exercises new ledger paths, not complete legacy app.
3. Complete actual issuance/approval-expiry and historical-asof writer acceptance. The focused clock conversion checks pass; they do not establish full historical reporting behavior or resolve ambiguous repeated-hour local timestamps.
4. Representative clinic-scale query/count/index assessment: current report overlay can load/project many invoices; no performance improvement or capacity certification claimed.
5. Independent exact-head financial/security review pending. No waiver applies to M2-C. No release recommendation until substantiated findings and gaps resolved.
6. Migration/permission rollout and schema-only artifact/dump compatibility must be verified before production authorization. DDL failure is not transactionally rolled back; nonempty down refuses. Historical ledger data cannot be erased for rollback. No production migration authorized.

Operational limits: same currency AND equivalent persisted FX currently required for application; unknown historical provenance fails closed. Incoming-backed credit is a tracked liability but cannot become cash on target; explicit application return to original cash source precedes further source refund/application. Full application returns only; no silent partial rewrite. Manager permission must be explicitly assigned through normal authorized administration before activation. No automatic compensation adjustment or clinical unbilling. No fiscal mode/numbering/VAT compliance claim; accountant validation remains required. Operational gross credits do not establish a legal tax rebate.

## Reproduction and preservation

Standalone: php tests/unit/InvoiceCorrectionPositionStandalone.php and InvoiceCorrectionI18nStandalone.php.
SQL harnesses require explicit trusted bootstrap, exact new disposable schema identity and populated owned synthetic fixtures; no production/bootstrap .env usage. Test-created fixtures/faulttrigger/barrier/evidence are retained; never reset an existing schema. Isolated parent workspace retains initializer/bootstrap and run results; no credentials or raw customer data in this PR. Re-running initial fixture suite against the same DB is intentionally not destructive/idempotent setup. Use a newly authorized disposable schema or retain previous evidence.

No merge, release-ref update, deployment, featureactivation, productionwrites or customerdata migration happened. Production remains M2, scheduling/WhatsApp OFF.
