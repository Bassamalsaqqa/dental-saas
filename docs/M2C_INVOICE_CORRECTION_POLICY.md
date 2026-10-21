# M2-C invoice corrections: proposed policy and architecture

Status: OWNER_POLICY_APPROVED ? isolated implementation in progress; no release acceptance. No runtime/schema change or migration. Base f86376a4a6c8f6bf0fe4471f08093af71e76e042 / tree4f12c340f8869b4b8f8aff57881c4b29ee98a72c. Original checkout and production untouched.

## Existing behavior confirmed by Codex

- InvoiceLifecyclePolicy::hasServerPositiveUnissuedProof() always false; persisted ordinary invoices are issued/legacy-unknown, irrespective of pending/overdue collection status. Basis and patient identity immutable; audit-only internal note is permitted. Deletion does not provide a cancellation mechanism.
- FinancePaymentModel::getNetSettledAmount() includes payment+adjustment-refund-void; getNetCashPaidTotal() excludes adjustments. Finance::closeInvoiceBalance() explicitly adds a noncash adjustment. This is NOT a legal credit-note document; do not relabel historical adjustments or record credits as fake receipts.
- Finance::refundInvoicePayment() / voidInvoicePayment() lock clinic-owned invoice and cap cash reversals; refund supports original receipt linkage, treasury account, optional FX, settlement recalculation and checked audit/commit. They currently reverse settlement, not the original charge; a refund alone can reopen AR. Existing behavior must remain available with clear semantics.
- FinanceSettlementService::getLockedSettlementState()/bulkSettle() own checked transactions, current payment locks, schema-optional FX and receipt creation. New correction math must be shared with these writers, mark-paid, single receipts, refunds/voids and all AR/report readers; otherwise credit can be ignored and a corrected invoice collected twice.
- ClinicalChargeLineService::prepareInvoiceSelection()/markInvoiced() link charge lines to one invoice and exclude invoiced/voided sources. Correction must not detach/reopen these rows to manufacture replacement charges. Invoice->individual monetary line allocation is incomplete; no inferred line allocation from printable free text.
- FinanceMutationService::recordPayout() and provider model callbacks preserve void/paid history. A credit does not automatically change a provider agreement, gross/share terms or paid amounts. Paid and outstanding obligations remain distinct, with native-currency buckets.
- MoneyTrailService, cash-basis FinanceModel reporting, treasury and existing print/download authorization are reusable integration points, not automatically credit-aware. Existing transaction IDs use timestamp/random suffix; a formal correction sequence needs its own locked unique namespace.

## Proposed clinic workflow

| User task | Proposed behavior |
|---|---|
| Unpaid issued invoice error | Linked full credit plus explicitly confirmed replacement, or partial credit for downward price correction. No edit/delete of original. |
| Partial credit | Select eligible monetary lines/explicit documented allocation, amount/reason, preview AR/customer-credit/provider impact, confirm once. |
| Full credit | Credit only remaining uncredited amount; mark fully credited in derived lifecycle, not cash paid. |
| Money already received | Credit and refund remain separate. Show resulting refundable/customer-credit amount; refund is an explicit existing receipt-linked cash operation. |
| Clinical-linked invoice | Financial correction overlay preserves treatment, charge-line identity, inventory, signed evidence and completion. Replacement references credited source mapping; cannot silently rebill or re-open charge lines. |
| Provider-linked invoice | Preserve existing allocation/payment history; explicit review/disposition before posting correction. Proposed first release blocks changes to those obligations rather than inventing a commission policy. |
| Replacement | Create new invoice with bidirectional original/credit/replacement links and clear new document identity. Receipts remain on original; any application to replacement is explicit, audited and policy-controlled. Wrong-patient correction never transfers money between patients automatically. |

Cash-basis example (no prior noncash adjustment): issued100, cash received100, credit40 -> net charge60, AR0, refundable/customer credit40, cash still100. Explicit refund40 -> cash net60, AR0, customer credit0. Unpaid100 credited20 -> AR80, cash0. Refund without credit remains settlement reversal and can reopen AR. Gross invoice, credit amount, noncash settlements, collected cash, refunds, AR and customer credit must each be visible. Existing noncash adjustments require distinct allocation/consumption rules to prevent double relief; do not derive refundable cash from generic amount_paid. Missing/ambiguous historical FX, line allocations or payment provenance require review rather than guesses.

## Proposed architecture (not implemented)

- Add a dedicated append-only invoice correction ledger: header and item tables plus a locked per-clinic document-sequence row. Schema design must justify each column; no new general finances.transaction_type enum by default. If explicit receipt/credit/replacement application records are needed, use an additive allocation table rather than rewriting finance_payments history.
- Header: clinic, original invoice, type/status, original document/patient/tax snapshot, reason, immutable issued number and date, currency and historical FX snapshot, canonical payload hash/idempotency identity, creator/issuer/audit references. Lines: immutable source reference (where positive evidence exists), credited subtotal/discount/tax/gross in original and base representations, explicit allocation. Precision uses canonical decimal amounts, deterministic rounding and caps in both currencies; never silently recalculate old exchange rates.
- Draft previews have no accounting effect. Issue atomically locks the original invoice and relevant correction/payment/clinical/provider records, revalidates ownership and source snapshot, enforces remaining-credit caps, allocates unique number and persists journal/audit together on one connection. Equivalent retry returns same document; conflicting retry fails. Preview freshness is a source hash/version, not an authorization bypass. No unbounded historical locks.
- Reuse FinanceCreationGuard parent contract (clinic then patient/examination with NOWAIT) for replacement/new references; do not assume all finance writers already use that order. Existing settlement starts at invoice while audit FKs may lock clinic. Before implementation document actual common lock graph, use fail-closed NOWAIT/retry where needed and prove no parent/invoice inversion with real MariaDB races. No nested transaction ownership.
- One correction-aware invoice position calculator supplies settlement writers, bulk settlement, FinanceModel AR/aging, patient money trail, reminders, printable balance and treasury boundaries. Original invoice monetary basis remains unchanged. Derived/cache balances can change only through the audited service; never rewrite historical payments or printed invoice basis.
- New correction issue/refund permission mapping must use existing PermissionService and tenant context; no global-role guessing. Invoice edit permission alone should not silently grant new financial-document issuance. Browser and API call same service; APIs keep JSON/auth/Bearer isolation. Default proposed issuer is an explicitly authorized clinic owner/finance manager; other staff can prepare only.
- Issued corrections immutable; wrong credit requires an explicit linked reversal/supplement document, never deletion or reused number. Original-number preservation and no silent renumbering. A legal numbering format is not chosen yet.
- Render immutable issued snapshots with escaped Arabic-first/English responsive UI and protected downloads. No odontogram/rendering/signed consent changes. Current document tax nomenclature remains unchanged until legal profile established.

## Palestine document/tax discovery ? bounded, not legal certification

Official Gazette VAT Law26/2024 (articles49,53,54) distinguishes registered/exempt documents, supplementary invoices for understatements and prescribed correction/cancellation procedure. It does not by itself certify our proposed credit-note wording or sequence. The original medical-examination exemption in article33 is conditional, not a blanket dental-services exemption. The 2026 amendment11/2026 changes competent-authority/form/record provisions including49,103,118; do not implement only against the unamended text. Clinic registration, actual services, approved forms/serials and accountant confirmation are needed before claiming tax-compliant issuance. No Saudi/Egyptian numbering or tax model is imported.

Primary sources checked2026-10-21:
- Official Gazette Law26/2024 and amendment index: https://mjr.ogb.gov.ps/Decrees/Details/33829/
- Official law PDF: https://mjr.ogb.gov.ps/Decrees/Download/?d=%D9%82%D8%B1%D8%A7%D8%B1-%D8%A8%D9%82%D8%A7%D9%86%D9%88%D9%86-%D8%B1%D9%82%D9%85-26-%D9%84%D8%B3%D9%86%D8%A9-2024-%D9%85-%D9%81%D9%84%D8%B3%D8%B7%D9%8A%D9%86&p=e6078fbb-948a-4721-a2cd-48550faba4e0.pdf
- Official amendment11/2026: https://mjr.ogb.gov.ps/Decrees/Details/34270/ (full four-page text checked). This research does not establish clinic-specific compliance or approved implementation instructions.

## Owner decisions adopted 2026-10-21

1. APPROVED: explicit same-patient, same-clinic, initially same-currency credit applications to linked replacement invoices or separate refund; immutable audited applications, no automatic transfers, over-credit/double refund/allocation, unapplied cash-backed credit tracked as patient liability.
2. APPROVED: provider-linked issuance requires manager approval bound to current locked financial state and displayed impact. Existing allocations/payments untouched; payable changes require separate explicit process, no automatic clawbacks or compensation reductions.
3. CONDITIONALLY APPROVED: operational-first documents; no official tax/exempt credit-note claim. Future fiscal configuration requires accountant validation of clinic classification/form/sequence/treatment, no invented VAT or blanket dental exemption.

Posting dates default to current clinic business date; original dates/tax/FX remain snapshots. No backdating/reopening closed periods or transfer between currencies. Issued credits capped by original uncredited gross; no new patient wallet/commission policy without Owner adoption.

## Bounded implementation and acceptance after decisions

A. Agree ledger/line/receipt application and authorization rules; implement additive schema source only plus one shared position calculator and transaction/idempotency service. Migration execution needs separate authorization even on synthetic staging.
B. Adapt existing single/bulk receipt/refund/void/adjustment writers and report/AR/reminder/treasury readers to the same projection; preserve all legacy no-credit behavior and invoice printing. Add explicit provider/clinical disposition and replacement provenance. Avoid keeping two totals implementations.
C. Arabic-first invoice correction preview/confirmation/document, English/mobile equivalents, clear labels for credit versus cash refund and no hidden destructive fallback.
D. One focused synthetic real-MariaDB suite for unpaid/partial/paid/refunded/adjusted, currency/FX and rounding, linked clinical/provider, concurrent credit/refund/payment/parent deletion, rollback/audit/idempotency/forged IDs and permissions. Preserve original fixtures; use real SQL, not only doubles. Bounded EN/AR/mobile browser checks and protected print/download acceptance. Independent exact-head financial/security review before release recommendation.
E. Pinned release with precise additive migrations, backward-compatibility/rollback limits and fresh recovery; no source integration/deploy/migration/activation without Owner authorization. Scheduling and WhatsApp stay OFF.
