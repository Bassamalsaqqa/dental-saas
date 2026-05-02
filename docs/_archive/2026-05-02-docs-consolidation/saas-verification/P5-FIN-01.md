# P5-FIN-01 — Invoice Payments, Partials, and Revenue Recognition

## Scope
- Add invoice payment ledger with partial payments.
- Ensure revenue recognition uses payments (incl. invoice payments) and refunds.
- Update finance UI and reports to reflect balances and payment history.

## Verification Steps
1. Run migrations:
   - `php spark migrate`

2. Lint key PHP files:
   - `php -l app/Models/FinanceModel.php`
   - `php -l app/Models/FinancePaymentModel.php`
   - `php -l app/Controllers/Finance.php`
   - `php -l app/Controllers/Reports.php`
   - `php -l app/Views/finance/create.php`
   - `php -l app/Views/finance/edit.php`
   - `php -l app/Views/finance/show.php`
   - `php -l app/Views/finance/invoice.php`

3. Routes sanity:
   - `php spark routes | findstr /I "finance"`

4. Manual checks:
   - Create an **invoice** in `/finance/create` with a due date.
   - Open `/finance/{id}` and add a **partial payment**.
   - Confirm:
     - Payment history shows the new payment.
     - Amount Paid / Balance Due update.
     - Status becomes **Partial** (or **Overdue** if past due date).
   - Add remaining payment and confirm status is **Paid** and balance is `0.00`.
   - Verify `/finance/{id}/invoice` shows payment history and balance.
   - Check `/finance` cards: Total Revenue and Monthly Income reflect payments (including invoice payments).

## Expected Results
- Invoice payments are stored and scoped by clinic.
- Partial payments update balance and status.
- Revenue metrics reflect payments and refunds.
