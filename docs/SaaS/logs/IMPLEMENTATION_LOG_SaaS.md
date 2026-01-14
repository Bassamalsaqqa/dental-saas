### Task: S4-02c Finance DataTables scoping
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Scoped `Finance` controller `getFinancesData` to the active clinic. Refactored `FinanceModel` to include scoped methods. Removed raw queries from controller and cleaned allowlist.
- **Files Changed:**
    - `app/Models/FinanceModel.php`
    - `app/Controllers/Finance.php`
    - `docs/SaaS/guardrails/raw-tenant-queries.allowlist`
- **Verification:** `docs/SaaS/verification/S4-02c.md`