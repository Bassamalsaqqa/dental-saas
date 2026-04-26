# Provider Compensation and Supplier Payables Progress

Date: 2026-04-26

Related plan:

- [docs/PROVIDER_COMPENSATION_AND_SUPPLIER_PAYABLES_IMPLEMENTATION_PLAN_2026-04-19.md](C:/xampp/htdocs/dental-saas/docs/PROVIDER_COMPENSATION_AND_SUPPLIER_PAYABLES_IMPLEMENTATION_PLAN_2026-04-19.md)

## Current Status Snapshot

The codebase is ahead of the original April 19 plan in several areas:

- provider payout ledger exists and supports payment actions
- finance transaction detail page supports provider payment recording
- supplier payables ledger exists
- supplier statement page exists
- finance reports already include provider and supplier payable reporting inputs
- finance create/edit already supports multiple provider allocation rows

The main remaining work is no longer "introduce the workflow from scratch". It is:

- finish statement-surface parity
- verify multi-provider integrity end to end
- clean up remaining UX inconsistencies
- preserve reporting correctness
- defer migration/admin remapping tools until after workflow completion

## Audit Result From This Session

### Provider side

Confirmed already implemented:

- multiple provider allocation rows in finance create/edit
- validation that total provider compensation cannot exceed collected amount
- provider payout ledger actions
- provider compensation summary on user pages
- provider payment recording from finance detail and payout ledger

Gap found and addressed in this session:

- user/provider page showed payout rows, but not the actual payout payment rows as a first-class table

### Supplier side

Confirmed already implemented:

- supplier detail page with:
  - total bills
  - total paid
  - open payable balance
  - linked expense rows
  - payment entry points
- supplier payable recording from supplier surface

Current judgment:

- supplier statement surface is materially closer to the plan target than the provider/user surface was
- no supplier page refactor was started in this session

## Changes Completed In This Session

### 1. Added provider payment history to the user page

Updated:

- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)
- [app/Controllers/Users.php](C:/xampp/htdocs/dental-saas/app/Controllers/Users.php)
- [app/Views/users/show.php](C:/xampp/htdocs/dental-saas/app/Views/users/show.php)

What changed:

- added provider payout payment query by provider user
- passed provider payment rows into `/users/{id}`
- rendered a dedicated payment history table on the user page using the page's existing table/card pattern

Why:

- this closes a real gap against the plan requirement that provider statement surfaces show both payout rows and related payment rows

### 2. Fixed provider "pay now" behavior during finance edit

Updated:

- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)

What changed:

- existing provider allocation rows can now record an additional payout payment when the edit form uses `mark paid now`
- edit validation now blocks paying more than the remaining provider balance
- inline payout payments from create/edit now set `liquidity_account` the same way the dedicated payout-payment endpoint does

Why:

- before this fix, the edit UI exposed provider payment fields for existing payout rows, but the controller only created payment rows for brand-new allocations
- that was a real integrity gap between the UI and saved accounting behavior

### 3. Improved provider labels in provider payout reports

Updated:

- [app/Models/ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php)

What changed:

- provider breakdown and provider payout report rows now use the linked user full name when available
- guest snapshot names remain the fallback

Why:

- provider reports should read like the rest of the app and not fall back to `User #id` when the real clinical provider name is available

### 4. Added supplier payment history surface to supplier detail

Updated:

- [app/Controllers/Inventory.php](C:/xampp/htdocs/dental-saas/app/Controllers/Inventory.php)
- [app/Views/inventory/supplier_show.php](C:/xampp/htdocs/dental-saas/app/Views/inventory/supplier_show.php)
- [app/Language/en/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/en/Finance.php)
- [app/Language/ar/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/ar/Finance.php)

What changed:

- supplier detail now loads actual expense payment rows
- supplier page now shows a compact recent payment history surface
- supplier statement keeps a single main bills table instead of adding another heavy full table
- added missing language text for the empty payment-history state

Why:

- supplier detail already had bill balances and payment actions, but not the actual payment rows as a first-class surface
- this brings supplier statements closer to the provider/user statement pattern without duplicating large tables

## Verification Completed In This Session

### Provider edit workflow

Verified end to end with a temporary local finance row and provider payout row, then cleaned up:

- opened `/finance/61/edit`
- submitted `mark paid now` for an existing provider allocation
- confirmed a new `provider_payout_payments` row was created with:
  - amount `20.00`
  - method `cash`
  - liquidity account `safe_cash`
  - note `Paid while updating transaction`
- confirmed `provider_payouts` summary fields updated to:
  - paid `20.00`
  - balance `10.00`
  - status `partial`
- opened `/finance/61` and confirmed the page showed the new payment history row
- removed the temporary verification rows after the check

### Supplier detail page

Verified in browser with a temporary local supplier + expense + payment row, then cleaned up:

- opened `/inventory/suppliers/1`
- confirmed summary cards, compact payment history, and supplier statement render together
- confirmed browser console had `0` errors
- removed the temporary verification rows after the check

## Remaining Work Queue

### High priority

1. Multi-provider integrity verification
- verify create
- verify edit
- verify update after partial payments
- verify ledger rows and user/provider surfaces
- verify report totals after multi-provider allocations

2. Supplier/page parity review
- review whether supplier page still needs a dedicated payment history table or tighter summary unification with `/users/{id}`

### Medium priority

3. Cross-surface wording cleanup
- keep doctor-facing terms simple
- keep supplier-facing terms simple
- avoid accounting-heavy wording where not needed

4. Reporting verification pass
- provider outstanding
- provider paid cash
- supplier outstanding
- supplier paid cash
- no double counting

### Later

5. Migration/admin cleanup tools
- repeated guest provider mapping
- optional external provider vs supplier normalization tools

## Notes

- The multi-provider feature already exists at the UI/controller level; the remaining work is validation, integrity, and surface consistency.
- Supplier payables are already operational enough that blind redesign would be lower value than finishing provider-side parity first.
