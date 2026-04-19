# Provider Payout, Supplier, and User Detail Audit

Date: 2026-04-19

## Scope

This note captures the current codebase behavior and product implications for:

- provider payouts in tenant finance
- clinic users vs guest providers vs labs/suppliers
- whether multiple providers can be attached to one finance transaction
- whether deferred payout is supported
- how supplier/inventory wiring currently works
- whether tenant user detail pages already exist

## Short Answer

- The app currently supports only one provider payout record per finance transaction.
- The app already supports paying the provider immediately or later.
- The app already distinguishes between:
  - clinic user
  - guest provider
- The app does not properly distinguish a lab/material provider from a generic guest provider.
- Inventory suppliers already exist as first-class records in the app.
- For labs/material vendors, suppliers are the closest existing canonical entity today.
- Tenant user detail pages already exist at `/users/{id}`.

## Current Provider Payout Model

### Data model

The current payout tables are:

- `provider_payouts`
- `provider_payout_payments`

Key implementation files:

- [app/Database/Migrations/2026-02-19-120000_AddProviderPayouts.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-19-120000_AddProviderPayouts.php)
- [app/Models/ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php)
- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)

### Important constraint: one payout per finance row

The current design is one payout row per `finance_id`.

Evidence:

- [ProviderPayoutModel::getByFinanceId()](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php#L51) fetches one payout by clinic + finance ID.
- [Finance::upsertProviderPayout()](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php#L533) updates or inserts exactly one payout row for that finance entry.

Implication:

- One invoice/payment/finance transaction cannot currently be split across two providers.
- Example not currently supported:
  - Doctor share
  - Lab share
  - both attached to the same finance transaction as separate liabilities

## Current Provider Types

### What exists now

Current allowed `provider_type` values are:

- `user`
- `guest`

Evidence:

- [migration enum](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-19-120000_AddProviderPayouts.php#L31)
- [validation rule](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php#L34)
- [payload normalization](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php#L447)

### Current UI behavior

Finance create/edit already exposes provider type selection:

- [app/Views/finance/create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/create.php)
- [app/Views/finance/edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/edit.php)

Options:

- `Clinic User`
- `Guest Provider`

Behavior:

- If `user`, the provider must be an active clinic user.
- If `guest`, the provider is stored only as a free-text name snapshot.

### Assessment

For a visiting doctor or internal clinic doctor:

- The current `user` flow is structurally sound.
- It is the correct current path if the provider is already a clinic user.

For a lab or material vendor:

- The current `guest` flow is weak.
- It stores only a name snapshot and does not link to a canonical supplier/vendor record.
- It does not distinguish:
  - lab
  - supplier
  - external specialist
  - technician

## Immediate vs Later Payment

### Current support

The app already supports both:

- pay now
- pay later

Evidence:

- [Finance::normalizeProviderSplitPayload()](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php#L447) supports:
  - `provider_mark_paid_now`
  - `provider_paid_amount`
  - `provider_paid_method`
  - `provider_paid_date`
- [Finance::upsertProviderPayout()](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php#L533) creates the payout row and, if applicable, the initial payment row.
- unpaid balances remain in:
  - `provider_balance_due`
  - `status = unpaid|partial|paid`

### How later payment works today

The later-payment workflow already exists.

Routes:

- [GET `/finance/payouts`](C:/xampp/htdocs/dental-saas/app/Config/Routes.php#L327)
- [POST `/finance/payouts/{id}/payments`](C:/xampp/htdocs/dental-saas/app/Config/Routes.php#L329)
- [POST `/finance/payouts/{id}/mark-paid`](C:/xampp/htdocs/dental-saas/app/Config/Routes.php#L330)

UI:

- [app/Views/finance/payouts.php](C:/xampp/htdocs/dental-saas/app/Views/finance/payouts.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)

What the user can do today:

1. Create a finance transaction with provider split enabled.
2. Leave provider unpaid.
3. Later go to `/finance/payouts`.
4. Filter/search the payout ledger.
5. Record a payment against the outstanding provider balance.

### Can the user see current balance and transactions?

Yes, but within the payout ledger model, not as a dedicated provider account statement.

Current visibility:

- Payout ledger table shows:
  - transaction
  - patient
  - provider
  - split
  - paid/balance
  - status
  - date
- Finance show page displays:
  - provider name
  - gross collected
  - provider share
  - clinic net
  - paid to provider
  - balance due

Limit:

- There is no true provider account page yet.
- There is no canonical “provider statement” that rolls up all doctor/lab balances and all payment history by provider entity.

## Supplier / Inventory Wiring

### Suppliers already exist

Suppliers are already first-class tenant records in inventory.

Key files:

- [app/Controllers/Inventory.php](C:/xampp/htdocs/dental-saas/app/Controllers/Inventory.php)
- [app/Models/SupplierModel.php](C:/xampp/htdocs/dental-saas/app/Models/SupplierModel.php)
- [app/Models/InventorySupplierLinkModel.php](C:/xampp/htdocs/dental-saas/app/Models/InventorySupplierLinkModel.php)
- [app/Views/inventory/suppliers.php](C:/xampp/htdocs/dental-saas/app/Views/inventory/suppliers.php)

Key routes:

- [GET `/inventory/suppliers`](C:/xampp/htdocs/dental-saas/app/Config/Routes.php#L465)
- quick create supplier
- supplier-link mapping on inventory items
- reorder list
- receive stock

### Supplier capabilities already present

The supplier side is not just a label. It already has structure:

- supplier directory
- supplier contact details
- optional preferred supplier mapping per inventory item
- supplier SKU
- pack size label
- last supplier price
- reorder list tied to supplier
- supplier contact logging on reorder list

### Assessment

For materials/lab procurement, suppliers are already the correct canonical object more often than `guest provider`.

That means:

- if the “provider” is actually who sold/provided materials, the app already has a better concept than guest payout provider
- current payout logic does not integrate with that supplier concept

## Product Interpretation

### Doctor provider

Current handling is mostly good when:

- the provider is a clinic user
- the clinic wants to split collected revenue with that doctor

This is already aligned with the current payout feature.

### Lab/material provider

Current handling is not mature enough when:

- the provider is an external lab
- the provider is a material/vendor supplier
- the provider is not simply a free-text guest

The current `guest` payout type is too weak because:

- no canonical entity link
- no provider history page
- no supplier/vendor relationship reuse
- no way to distinguish guest doctor vs lab vs vendor cleanly

## User Detail Pages

Tenant user detail pages do already exist.

Evidence:

- [routes for `/users/{id}`](C:/xampp/htdocs/dental-saas/app/Config/Routes.php#L502)
- [Users::show()](C:/xampp/htdocs/dental-saas/app/Controllers/Users.php#L332)

There is also a second older management flow:

- [UserManagementController::show()](C:/xampp/htdocs/dental-saas/app/Controllers/UserManagementController.php#L319)

Practical answer:

- Yes, `/users/28` should exist and show a tenant-scoped user detail page if permissions allow it.

## Recommended Direction

### 1. Keep the current doctor payout flow

Do not throw away the current `user` provider flow. It is useful and already supports:

- split rule
- unpaid/partial/paid lifecycle
- pay now
- pay later
- payout ledger

### 2. Do not use generic guest for labs long term

For labs/material vendors, the cleaner direction is:

- link payouts or payables to `suppliers` instead of free-text guest names

At minimum, expand provider typing to something explicit like:

- `user`
- `supplier`
- `external_provider`

If keeping `guest`, it should be reserved for truly ad-hoc one-off providers, not as the main lab model.

### 3. Support multiple payout/payable rows per finance entry

If one transaction can involve:

- doctor share
- lab share

then the data model should allow multiple rows per finance transaction.

Current blocker:

- one `provider_payouts` row per `finance_id`

Preferred future shape:

- multiple allocation rows under one finance transaction
- each row has its own:
  - provider entity/type
  - share amount
  - paid amount
  - balance due
  - status
  - payment history

### 4. Separate revenue-share payouts from supplier payables conceptually

Important product note:

- doctor payout is usually revenue-share / provider compensation
- lab/material payment is often a payable / vendor bill, not a provider revenue split

This distinction matters.

In many clinics:

- doctor share belongs to compensation/payout logic
- lab bill belongs to expense/accounts-payable logic

This suggests the future model should likely be:

- doctor/clinical-provider payouts stay under provider payout logic
- supplier/lab material bills stay under expense + payable logic

That is cleaner than forcing labs into the same semantic bucket as doctor revenue sharing.

## Best Practical Conclusion For This App

### What should be used today

- Use `provider_type = user` for doctors/visiting doctors who are clinic users.
- Use `guest provider` only as a temporary workaround for non-user providers.
- Use inventory suppliers as the canonical source for material vendors on the inventory side.

### What is missing today

- multi-provider allocation on one finance transaction
- proper lab/supplier linkage from finance payout flow
- provider statement page by canonical provider entity
- explicit distinction between:
  - clinical provider payout
  - supplier/vendor payable

## Proposed Next Implementation Track

### Phase A

Document and clarify domain rules first:

- when to use provider payout
- when to use expense/payable
- when supplier should be used

### Phase B

Upgrade provider entity typing:

- `user`
- `supplier`
- optional `external_provider`

### Phase C

Allow multiple provider allocation rows per finance entry.

### Phase D

Add provider/supplier statement pages:

- balances
- linked transactions
- payments
- aging/outstanding

## Concrete Questions Answered

### Q1. If provider is a doctor/visiting doctor, is current app good?

Mostly yes, if that doctor is represented as a clinic user.

### Q2. If provider is a lab/generic provider for materials, is current app good?

No. Current `guest` is not a strong enough long-term model for labs/material vendors.

### Q3. Should that generic provider already exist in the app?

Often yes. If it is a materials vendor/lab supplier, the closest current canonical entity is the inventory supplier record.

### Q4. If provider was not paid immediately, how do I pay later and see balance?

Today:

- go to `/finance/payouts`
- find the payout row
- record a payment or mark paid
- current balance is shown in payout ledger and finance show page

What is still missing:

- a provider-centric ledger/statement page

### Q5. Should `/users/28` exist?

Yes. Tenant user detail route already exists, subject to permission.

