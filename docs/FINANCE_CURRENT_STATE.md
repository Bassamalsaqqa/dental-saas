# Finance Current State

Last reconciled: 2026-11-05. Installed release and activation authority: [PROJECT_STATE.md](PROJECT_STATE.md). Current usability findings and work: [WORK_PROGRAM.md](WORK_PROGRAM.md).

## User Surfaces

- `/finance`: daily clinic finance workspace.
- `/finance/reports`: owner/accounting summaries.
- `/finance/payables`: unpaid expenses, including salaries and supplier/vendor obligations.
- `/finance/payouts`: provider obligations and payments.
- `/finance/transfers`: movement between liquidity accounts.
- `/finance/expenses/create`: expense entry, including the Salaries category; this is distinct from patient-linked provider shares.

## Canonical Lanes

| Lane | Owns |
|---|---|
| Patient finance | Charges, invoices, payments, refunds, adjustments, balance due |
| Payables | Unpaid expense balances (including salaries), supplier/vendor bills and settlements |
| Provider payouts | Shares owed and paid to clinic/outside providers |
| Treasury | Safe cash, bank, checks, and internal transfers |
| Inventory COGS | Material cost posted as an expense with source linkage |

Provider payouts are not supplier payables. Internal transfers do not change profit or total liquid funds.

## Reporting Semantics

- `money_in`: collected inflows in the period.
- `money_out`: refunds plus paid expenses and provider payouts.
- `customers_owe_us`: open patient invoice balance at period end.
- `we_owe_others`: open supplier/payable and provider payout balances.
- `net_position`: money in - money out + receivables - obligations.
- Business and settlement dates drive reports; `created_at` is audit time only.
- Revenue uses cash-basis receipt, not unsigned plans, appointments, or invoice issuance alone.

## Clinical Traceability

- Treatments may explicitly capture a clinical charge line.
- Charge lines retain patient, examination, treatment/procedure, appointment, provider, amount, currency, and source context where available.
- Money Trail views connect clinical source -> charge -> invoice -> payment -> provider obligation -> inventory cost.
- Procedure transitions never bill automatically.
- Invoice/payment/provider calculations remain backend-owned.

## Remaining Gaps

- Invoice line allocation to individual clinical charges.
- Payment allocation by line/provider and production-versus-collection reporting.
- Opening/counting/reconciliation workflows for liquidity accounts.
- Bounced checks, bank fees, delayed card settlement, and recurring bills.
- Insurance claims, write-offs, denials, and responsibility split.

Implementation sequencing is in `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`.

## Operational boundaries

- **Settlement protection is deployed** in PR #22 at `9e1f8558`, including `finance_settlement_requests`. Payment/refund/provider/supplier tasks require server-issued request identities and locked revisions; missing schema/preconditions fail closed. Matching retries return the original result; changed terms or stale state cannot silently produce another payment. Original calculations and receipts remain authoritative. [Recorded implementation evidence](ui-evidence/product-ux/CURRENT_ACCEPTANCE.md) contains historical pre-release status; [PROJECT_STATE.md](PROJECT_STATE.md) governs actual deployment.
- **Invoice corrections remain OFF.** Original invoices, receipts and provider history are preserved. Incoming replacement applications transfer existing backing; refundable capacity excludes incoming amounts. Full returns, equal-FX applications and no automatic provider clawbacks remain policy limits. An activated correction ledger prevents treating source-only rollback as complete recovery. Policy, implementation evidence and rollout requirements live in [the correction policy](M2C_INVOICE_CORRECTION_POLICY.md), [its evidence](M2C_IMPLEMENTATION_EVIDENCE_20261021.md) and [its release procedure](M2C_RELEASE_PROCEDURE.md).
- **Combined invoice/receipt checkout remains OFF.** `FinanceCheckout.enabled` requires both `finance:create` and `finance:edit`, separate schema/activation approval and no automatic permission grants. The receipt uses invoice currency and canonical payment/liquidity/FX rules (base currency requires rate 1), with clinic-local dates. Invoice, receipt, explicit provider entries, selected clinical lines, replay identity and audit share one checked transaction. Exact actor/clinic/request terms include uploaded check content, exclude CSRF/return navigation and retain decimal input identity; equivalent completed retries preserve the original receipt even after refunds and never enqueue another creation reminder. Failure rolls back new database writes, without claiming database/filesystem atomicity. Separate invoice/payment entry remains available.

No new financial feature, migration or production change is authorized by a usability review. The live currency-disclosure mismatch and remaining salary/share/history usability work are tracked once in [WORK_PROGRAM.md](WORK_PROGRAM.md); no financial mutation was used to inspect them.

## Verified workflow distinctions (2026-11-05 candidate)

Salaries use the existing expense writer, category `salaries` and a free-text payee. They are not linked to employee user IDs, do not recur automatically, and are not included in the provider history on user detail. Record salary -> paid/unpaid/partial -> settle remaining balance in Bills & payables -> dated history on the expense detail.

Provider shares are entered on their patient transaction as fixed amounts or percentages of its total in base currency. The financial selector lists active clinic members; named outside providers are supported separately. It is not the scheduling doctor directory. There is no verified automatic compensation agreement/default: the candidate leaves new rates blank and requires explicit entry, preserving stored/validation values. Pay a share through Provider balances; dated history is on transaction detail `#provider-payouts` and the existing user payout view. Do not enter the same outflow again as a salary expense. Existing no-automatic-clawback policy is unchanged.

Active cash reporting is `Finance::buildFinancialLedger()/summarizeCashReport()`, not the legacy Reports helper. Separate accumulators do not, by themselves, prove correct SQL provenance or prevent an operator from entering the same payment twice. Current focused unit/SQL evidence and limits are recorded in WORK_PROGRAM.
