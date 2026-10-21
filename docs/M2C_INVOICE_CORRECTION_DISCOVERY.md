# M2-C targeted source discovery

Base: f86376a4a6c8f6bf0fe4471f08093af71e76e042. Codex independently inspected the bounded paths below. AGY Gemini3.8FlashHigh was delegated this package but timed out after5minutes without final findings or edits; no agent acceptance is claimed. Proposed policy/implementation sequence: [M2C_INVOICE_CORRECTION_POLICY.md](M2C_INVOICE_CORRECTION_POLICY.md).

| Existing source | Confirmed behavior / M2-C consequence |
|---|---|
| app/Services/InvoiceLifecyclePolicy.php:114,132,189 | No positive draft proof; all ordinary persisted invoice basis/identity protected, physical deletion fails closed. Keep it. |
| app/Models/FinanceModel.php:273,292,1022,1052 | Clinic-scoped cash receipts/reporting and AR aging; current generated IDs not an approved legal correction sequence. Integrate new position overlay, do not replace original basis. |
| app/Models/FinancePaymentModel.php:52,68,86 | Noncash adjustment included in settlement, excluded from net cash; related refunds/voids bounded. Credits need distinct history, not false cash. |
| app/Controllers/Finance.php:3344,3506,3657,3782 | Existing invoice lock/refund/void/close-adjustment/settlement-status paths. Refund does not reduce original charge and may reopen AR. All must use common correction-aware math. |
| app/Services/FinanceSettlementService.php:57,69,95,189 | Current locks, ledger state and owned bulk-settlement transaction; supports optional FX columns. Preserve atomic receipt/history protection while integrating credits. |
| app/Services/FinanceCreationGuard.php:127 | Clinic->patient->examination parent locks, child NOWAIT to avoid existing FK/audit lock inversion. New replacement workflow must follow actual lock graph; invoice-only lock cannot be assumed safe across all writers. |
| app/Services/FinanceMutationService.php:64,160 | Invoice-linked allocation locks and safe payout recording. Credits must not revive void obligations or rewrite payment history. |
| app/Services/ClinicalChargeLineService.php:53,105 | Same-patient/same-currency invoiceable selection and finance link. Already-invoiced sources cannot simply be detached/reused for replacement. No immutable monetary invoice-item allocation found in this bounded pass. |
| app/Services/MoneyTrailService.php:210,335 | Existing settlement history and summary paths; keep cash/noncash/credit distinctions explicit and reconcile with reports. |
| app/Services/FinanceTreasuryService.php:23,56,81,91 | Liquidity account direction/compatibility and movement summary reusable for explicit cash refund; credit document alone has no treasury movement. |
| app/Config/Routes.php:344-363; Finance::generateInvoice():3835 | Session tenant/permission guards and existing invoice printing. New issue authorization must be explicit and enforced in route+service; printable issued snapshots protected. |

No dedicated credit-note/correction ledger, issue lifecycle or replacement-credit applications found in the inspected app/services/models/routes. Existing invoice correction prohibition is a safeguard, not completed credit-note functionality. Existing supplier payables/provider payouts, inventory COGS and signed consent are separate systems to preserve.

Discovery-only constraints: no app bootstrap, database connection, SSH, migrations, code tests, GitHub requests, source integration or production activity executed during M2-C. Active governance docs were reconciled to actual deployed M2; existing broad guardrail drift/suites were not rerun for documentation-only work.

Critical integration risks before implementation: double relief with legacy adjustments; overcollection if mark-paid/bulk paths ignore credits; receipt FX versus invoice FX; missing line-level allocation provenance; provider obligations already earned/paid; snapshot historical AR; stale reminders and replacement provenance; no legal-numbering assumption. See proposed acceptance matrix and Owner questions in policy document.
