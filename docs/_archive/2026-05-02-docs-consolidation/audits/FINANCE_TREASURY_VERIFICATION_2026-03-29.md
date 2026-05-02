# Finance Treasury Verification - 2026-03-29

## Purpose
This document verifies the current treasury-aware finance model against a controlled scenario executed through the real application code.

The goal was to confirm that:
- owner summary values reconcile correctly
- treasury closing balances reconcile correctly
- internal transfers stay neutral on total liquid funds
- accounts payable aging reflects unpaid and partial expenses correctly

## Verification Method
A temporary PHP verification script booted the real CodeIgniter application and executed against the local database.

Method:
1. find a clinic with no finance rows, no provider payouts, and no treasury transfers
2. start a DB transaction
3. insert a controlled scenario into real tables
4. invoke real application code:
   - `Finance::buildFinancialLedger()` by reflection
   - `Finance::summarizeCashReport()` by reflection
   - `FinanceTreasuryService::summarizeMovement()`
   - `FinanceTreasuryService::summarizeClosingBalances()`
   - `FinanceModel::getAccountsPayableAgingSummaryByClinic()`
   - `FinanceModel::getAccountsPayableAgingDetailByClinic()`
5. capture the outputs
6. rollback the transaction so no verification data remains in the database

Verification environment:
- app path: `C:\xampp\htdocs\dental-saas`
- date window used: `2026-03-01` to `2026-03-31`
- as-of date used for closing/AP: `2026-03-31`
- isolated clinic used during verification: `clinic_id = 21`

## Inserted Scenario
### Inflows
1. direct cash payment
- amount: `1000`
- custody: `safe_cash`
- transaction date: `2026-03-12`

2. direct card payment
- amount: `600`
- custody: `bank`
- transaction date: `2026-03-13`

3. direct check payment
- amount: `400`
- custody: `undeposited_checks`
- transaction date: `2026-03-14`

### Treasury transfer
4. check deposit to bank
- amount: `250`
- from: `undeposited_checks`
- to: `bank`
- transfer date: `2026-03-16`

### Payables
5. unpaid expense
- total: `300`
- paid: `0`
- balance due: `300`
- due date: `2026-03-20`
- transaction date: `2026-03-10`

6. partially paid expense
- total: `500`
- paid: `200`
- balance due: `300`
- due date: `2026-03-22`
- transaction date: `2026-03-15`
- settlement payment: `200` from `bank` on `2026-03-18`

7. fully paid expense
- total: `150`
- paid: `150`
- balance due: `0`
- due date: `2026-03-19`
- transaction date: `2026-03-17`
- settlement payment: `150` from `undeposited_checks` on `2026-03-19`

## Verified Raw Output
```json
{
  "clinic_id": 21,
  "owner_summary": {
    "money_in": 2000,
    "money_out": 350,
    "customers_owe_us": 0,
    "we_owe_others": 600,
    "net_position": 1050
  },
  "treasury_closing": {
    "safe_cash": 1000,
    "bank": 650,
    "undeposited_checks": 0,
    "total_liquid_funds": 1650
  },
  "treasury_movement_totals": {
    "in": 2000,
    "out": 350,
    "net": 1650
  },
  "ap_summary": {
    "current": 0,
    "days_1_30": 600,
    "days_31_60": 0,
    "days_61_90": 0,
    "days_91_plus": 0,
    "total_balance_due": 600,
    "expense_count": 2,
    "as_of_date": "2026-03-31"
  },
  "ap_detail": [
    {
      "transaction_id": "VRF-EXP-UNPAID",
      "balance_due": 300,
      "aging_bucket": "days_1_30",
      "days_past_due": 11
    },
    {
      "transaction_id": "VRF-EXP-PART",
      "balance_due": 300,
      "aging_bucket": "days_1_30",
      "days_past_due": 9
    }
  ]
}
```

## Reconciliation
### Owner summary
Expected:
- `Money In = 1000 + 600 + 400 = 2000`
- `Money Out = 200 + 150 = 350`
- `Customers Owe Us = 0`
- `We Owe Others = 300 + 300 = 600`
- `Net Position = 2000 - 350 + 0 - 600 = 1050`

Observed:
- exact match

### Treasury closing balances
Expected:
- `Safe Cash = 1000`
- `Bank = 600 + 250 - 200 = 650`
- `Checks on Hand = 400 - 250 - 150 = 0`
- `Total Liquid Funds = 1000 + 650 + 0 = 1650`

Observed:
- exact match

### Treasury movement totals
Expected external movement during the period:
- inflow: `2000`
- outflow: `350`
- net: `1650`

Important:
- the internal `250` check deposit changes custody buckets but must not change total liquid funds

Observed:
- exact match

### Accounts payable aging
Expected open payables by `2026-03-31`:
- unpaid expense: `300`
- partial expense remaining: `300`
- total: `600`

Observed:
- exact match
- both landed in the `1-30 days` bucket using their due dates

## Conclusions
The current treasury-aware finance model is verified for the tested scenario.

Verified behaviors:
- direct cash, card, and check collections land in the correct custody buckets
- expense settlements reduce the correct custody buckets
- check deposits move value from `undeposited_checks` to `bank`
- internal transfers do not distort total liquid funds
- `We Owe Others` includes open expense balances correctly
- `Net Position` reconciles to the current owner-summary definition

## Limits of This Verification
This verification did not test:
- provider payout balances mixed with treasury events
- bounced checks
- opening balances
- counted cash variance
- bank reconciliation
- recurring bills

Those remain future enhancement tracks and should stay documented as future work only.
