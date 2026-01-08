# Codex Operating Guide - Security Hardening Program

## A) Role Definition (Codex = Planner/Reviewer)
- Primary mission: select Task IDs, sequence work, generate Gemini prompts, and review changes against the program spec in `docs/SECURITY_HARDENING_PROGRAM.md`.
- Secondary mission: enforce evidence-based verification, prevent regression/drift, and maintain an audit trail of security posture.

## B) Non-Negotiable Rules
- Single source of truth: `docs/SECURITY_HARDENING_PROGRAM.md` governs scope, priority, and DoD.
- Codex selects Task IDs explicitly each iteration; Gemini must not self-expand scope.
- Reviews must be evidence-based (file paths, diffs, route/filter coverage, and verification results).
- Fail-closed enforcement is required (RBAC and security controls must not allow on exception).
- No reintroducing browser installers, public repair tools, test endpoints, or secrets.
- No implementation in planning/review-only steps.

## C) Session Start Checklist (new Codex sessions)
1) Read `docs/SECURITY_HARDENING_PROGRAM.md` for current phase and priorities.
2) Read `docs/IMPLEMENTATION_LOG.md` and `docs/DECISION_LOG.md` to understand last state.
3) Re-run targeted repo scan to detect drift (use these commands):
```
Get-ChildItem public -Filter *.php
rg -n "csrf|secureheaders" app/Config/Filters.php
rg -n "group\\('api'|session-test|debug/|test-" app/Config/Routes.php
rg -n "install.php|repair_db|init_database|simple_login_test|check_users_table|debug_env" -g\"*\" .
Get-ChildItem -Force -Path . -Filter *.env
Get-ChildItem -Force -Path . -Filter *.sql
rg -n "database.default.password|encryption.key|DBDebug" app/Config/Database.php .env environment.env
rg -n "innerHTML|\\.html\\(" app/Views
```
4) Identify next tranche of work (1-3 Task IDs max per iteration).

## D) Iteration Workflow (Codex ↔ Gemini)
1) Select next Task IDs and restate their Definition of Done and constraints.
2) Generate a Gemini prompt that includes:
   - Task IDs
   - Expected behavior
   - Exact files to touch/avoid
   - Verification requirements
   - Documentation updates required (`docs/*.html` + `docs/deployment/*` + `docs/verification/*`)
   - Prohibition on scope creep and temporary bypasses
3) After Gemini completes, perform review:
   - Validate no new public scripts or secrets
   - Validate CSRF/headers/cookies/API auth changes behave correctly
   - Validate docs updated to reflect hardened install/deploy/testing (no legacy insecure steps)
   - Validate verification files exist and are correct
4) Update task statuses in `docs/SECURITY_HARDENING_PROGRAM.md` (or instruct Gemini to do it if that is the workflow).

## E) Review Checklist (audit-grade)
- Route/filter coverage diff (confirm permission filters on plural aliases and API groups).
- Search for install/repair/test/session endpoints:
  - `rg -n "install|repair|init_database|simple_login_test|check_users_table|session-test|debug/" app/Config/Routes.php public`
- Confirm `public/.htaccess` and server rules block sensitive files and direct access to PHP tools.
- Confirm CSRF + secureheaders are enabled in `app/Config/Filters.php`.
- Confirm API auth + field minimization on `/api/search/*`.
- Confirm no secrets in repo:
  - `rg -n "password\\s*=|database.default.password|encryption.key" . env* app/Config/Database.php`
- Confirm docs do not instruct `install.php` in browser and do not show default creds for production.
- Confirm verification artifacts exist under `docs/verification/` with expected HTTP codes.

## F) Prompt Template (Codex → Gemini)
```
Task IDs: [P0-01, P0-02]

Scope/DoD:
- [paste Definition of Done from docs/SECURITY_HARDENING_PROGRAM.md]

Files to touch:
- [explicit list]

Files to avoid:
- [explicit list]

Expected behavior:
- [bullet list of behavior changes]

Verification requirements:
- [exact commands/curl and expected HTTP codes]

Documentation updates required:
- docs/*.html
- docs/deployment/*
- docs/verification/<TASK_ID>.md
- docs/IMPLEMENTATION_LOG.md (append entry)

Constraints:
- No scope creep, no temporary bypasses, fail-closed behavior only.
```

## G) State Summary (maintain each iteration)

Current risks (paths only):
- `public/init_database.php`
- `public/repair_db.php`
- `public/repair_db_final.php`
- `public/repair_db_standalone.php`
- `public/check_users_table.php`
- `public/simple_login_test.php`
- `install.php`
- `debug_env.php`
- `.env`
- `environment.env`
- `democa_dental.sql`
- `app/Config/Filters.php` (CSRF and secureheaders disabled)
- `app/Config/Routes.php` (unauth API/search + test/session/debug routes)
- `public/.htaccess`
- `.htaccess`

Completed mitigations (paths only):
- (none yet)
