# Project State (Readiness Summary)

Last updated: 2026-01-19

## Current Status
- **Hardening Program Phase:** 1+ (Containment + Hardening completed; advanced tracks in progress)
- **Stability:** Pre-Alpha (Do Not Deploy)
- **Last Audit Window:** 2026-01-15 to 2026-01-19 (active hardening + evidence capture)

## Data Reality Check (2026-01-16)
- **Development DB:** 5 active clinics (used for M:N membership testing).
- **Seeder:** `ClinicSeeder` provides 2 baseline clinics.
- **Implication:** Verification steps must account for multiple clinics; do not assume clinic_id=1 is the only valid tenant.

## Active Remediation Plan (SaaS Stabilization)
1) **Critical Fixes:** Patch `NotificationService` crash and add missing `settings/notifications` view.
2) **User Preferences:** Migrate `clinic_users` to add a `preferences` JSON column.
3) **SaaS Hardening:** Implement proper SMTP form UI in `settings/channels` (no hard-coded transport).
4) **UI Standardization:** Refactor Control Plane to use `layouts/main_control_plane` consistently.

## Session Start Checklist (Required)
1) `git branch --show-current` (expect `main`)
2) `git status --porcelain` (expect empty)
3) `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views` (expect empty)
4) `rg -n "csrf|secureheaders" app/Config/Filters.php`
5) `rg -n "group\('api'" app/Config/Routes.php`
6) `rg -n "install|debug_env|repair_db|init_database|simple_login_test" .htaccess public/.htaccess`
7) `git ls-files .env democa_dental.sql` (expect empty)
8) `rg -n "DBDebug" app/Config/Database.php`

## Invariants (Do Not Regress)
- CSRF + secure headers remain globally enforced; any exemption must be explicit and narrowly scoped.
- API requests fail-closed with JSON 401/403/422/429; no redirects and no HTML error pages.
- No DOM-string rendering for dynamic content in allowlisted views (no innerHTML/outerHTML/insertAdjacentHTML/.html()).
- No browser-accessible installers/repair/debug/test scripts.
- No secrets or database dumps tracked in git.

## Canonical References
- Hardening program: `docs/SECURITY_HARDENING_PROGRAM.md`
- Verification artifacts: `docs/verification/`
- Decision log: `docs/DECISION_LOG.md`
- Implementation log: `docs/IMPLEMENTATION_LOG.md`

## Local-Only Notes
- `.env` and `democa_dental.sql` may exist locally but must remain untracked.
