# Project State (Readiness Summary)

Last updated: 2026-01-10

## Current Status
> **Phase:** 0 (Security Hardening)
> **Stability:** Pre-Alpha (Do Not Deploy)
> **Last Audit:** 2026-01-15 (Partially Complete)

### Data Reality Check (2026-01-16)
*   **Development DB:** Contains **5 active clinics** for testing M:N user scenarios.
*   **Seeder:** `ClinicSeeder` currently provides **2 baseline clinics**.
*   **Implication:** Verification steps should account for this discrepancy.

### Active Remediation Plan (SaaS Stabilization)
1.  **Critical Fixes:** Patch `NotificationService` crash and add missing `settings/notifications` view.
2.  **User Preferences:** Migrate `clinic_users` table to add `preferences` JSON column.
3.  **SaaS Hardening:** Implement proper SMTP Form UI in `settings/channels`.
4.  **UI Standardization:** Refactor Control Plane to use `layouts/main_control_plane`.

## Session Start Checklist
1) `git branch --show-current` (expect `main`)
2) `git status --porcelain` (expect empty)
3) `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views` (expect empty)
4) `rg -n "csrf|secureheaders" app/Config/Filters.php`
5) `rg -n "group\('api'" app/Config/Routes.php`
6) `rg -n "install|debug_env|repair_db|init_database|simple_login_test" .htaccess public/.htaccess`
7) `git ls-files .env democa_dental.sql` (expect empty)
8) `rg -n "DBDebug" app/Config/Database.php`

## Invariants (Do Not Regress)
- CSRF + secure headers always on; no silent bypasses.
- API fail-closed with JSON 401/403; no redirect for API requests.
- No DOM-string rendering for dynamic content in views.
- No browser-based installers/repair scripts exposed.
- Documentation logs are append-only.

## Canonical References
- Hardening program: `docs/SECURITY_HARDENING_PROGRAM.md`
- Verification artifacts: `docs/verification/`
- Decision log: `docs/DECISION_LOG.md`
- Implementation log: `docs/IMPLEMENTATION_LOG.md`

## Local-Only Notes
- `.env` and `democa_dental.sql` may exist locally but must remain untracked.
