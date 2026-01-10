# Project State (Readiness Summary)

Last updated: 2026-01-10

## Current Status
- All hardening tasks P0-01 through P3-11 are marked DONE.
- DOM-string sinks removed from all views (innerHTML/outerHTML/insertAdjacentHTML/.html).
- CSRF + secure headers enabled; API group protected by auth filter.
- Installer/debug scripts blocked; scripts are CLI-only.
- Secrets/dumps are untracked; local-only files may exist.

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
