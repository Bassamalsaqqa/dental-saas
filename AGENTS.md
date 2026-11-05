# Shared agent guide - Dental SaaS

## Scope and evidence

- All coding agents follow this guide. Read [WORK_PROGRAM](docs/WORK_PROGRAM.md) for assigned work, [PROJECT_STATE](docs/PROJECT_STATE.md) for release/activation status, and [START_HERE](docs/START_HERE.md) for relevant contracts. A task ledger never overrides security contracts or release authorization.
- Documentation can be stale. Verify implementation/status claims against source, tests and runtime when relevant. Record contradictions without weakening safeguards. Read task-relevant contracts, not every historical plan.
- Reuse authoritative application settings, services, records and shared components. Never invent business rules/data, hardcode configurable values or create a parallel system to patch a loose connection. If no authoritative rule exists, identify the gap instead of guessing.
- Inspect working changes, branch/HEAD and untracked files before editing. Preserve unrelated work; never reset, stash or clean a tree to prepare a task.
- Update the existing task register with results, limitations and the next bounded task. Do not create competing master plans or accept an agent report without checking the actual diff.

## Single writer and manual AGY

- Direct Codex work is the default. Only the Owner's explicit `$agy-manual` enables an interactive AGY handoff in this existing checkout.
- No automatic AGY, headless dispatch, relay/controller, retries or additional worktrees. Codex stops editing while AGY works.
- At handback, independently inspect the actual diff and untracked files and run focused checks. Do not infer authorization for commits, pushes, PRs, merge, reset, deletion or deployment.

## Business and security invariants

- Fail closed for missing/invalid auth, tenant context, RBAC and feature entitlement. UI visibility never replaces server authorization.
- Keep Control Plane and Tenant Plane separate. Control Plane requires `global_mode=true`; tenant requests require validated membership and server-owned clinic context. Preserve impersonation governance.
- Public `/api/v1/*` uses stateless Bearer auth plus `X-Clinic-Id`; first-party web/session interfaces retain their own CSRF/session contract. Never add redirects or HTML to `/api/*`.
- Scope tenant reads/writes/files to the authorized clinic using session context or ApiRuntimeContext as appropriate. Preserve existing denial/concealment behavior.
- Preserve original clinical/signed evidence, financial receipts, calculations, history, replay identity, transaction/locking and audit guarantees. Do not guess missing financial units or rewrite history to simplify presentation.
- No DOM sinks in `app/Views`; use escaped output and safe DOM APIs. Guardrail inputs, accepted evidence, license notices, backups, customer files and Owner reference assets are not disposable clutter.
- Keep secrets, dumps, customer records, browser profiles and private recovery material out of Git and public web paths.

## Local work and verification

- Existing checkout: `C:/xampp/htdocs/dental-saas`. Read environment settings privately; never print credentials. Session cookie: `ci_session_saas`.
- At the 2026-11-05 checkpoint, local business MySQL was stopped and earlier synthetic environments retired. Recheck readiness; never assume an old fixture/database is disposable or available.
- Inspect tests before running them: a `tests/unit` path does not prove database safety. Use mocks for pure unit checks. SQL/HTTP mutation tests require a proven isolated synthetic target and repository preflight; never use business data as fixtures.
- Run relevant checks and `git diff --check`. Common source checks:

```powershell
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views
php spark routes | findstr /I "controlplane api/v1 api/search"
& "C:/Program Files/Git/bin/bash.exe" scripts/ci/saas_guardrails.sh
```

- For an inspected database-free PHPUnit file: `php vendor/phpunit/phpunit/phpunit --no-coverage --no-logging --do-not-cache-result <file>`. Do not run the whole suite without checking database requirements.
- Migrations, rollback, seeders, permission sync, key regeneration, business exports/restores, notification dispatch and feature activation are not routine setup/test steps. Follow explicit authorization and target checks in [OPERATIONS_RUNBOOK](docs/OPERATIONS_RUNBOOK.md).
- Preserve pinned `public/assets/css/tailwind.css`. Do not routinely rebuild assets, install new dependencies or upgrade locks. Authorized CSS changes need temporary-output comparison and consumer verification; see README.

## UI and workflows

- Read `C:/Users/bassa/.agents/skills/ui-design-defaults/SKILL.md` and its BoardUI/shadcn routing. Detect the actual stack before installation; this app uses native CI4/PHP views, Tailwind 3 and Studio components. Do not introduce React/BoardUI into PHP views.
- Read DESIGN.md and inspect the actual route, forms, scripts and permissions before editing. Preserve semantic tokens, readable type and shared components while questioning task architecture and misleading defaults.
- Verify relevant desktop/tablet/mobile, EN/AR and RTL, keyboard/focus, dialogs, validation, loading/empty/error and recovery states. Historical coverage claims do not certify new changes.
- Old rebuild ledgers remain historical evidence; do not restart a pre-release transfer campaign or treat an old visual anchor as current release authority.

## Production and cleanup

- Exact installed SHA/tree and approved release ref govern deployment; use current PROJECT_STATE rather than a branch name.
- On 2026-11-05 SSH inspection, live source matched the approved deployed tree but the checkout was named `main` tracking historical `origin/main`. Do not pull it, rename it or reconfigure production during inspection.
- Deployment, migrations, feature activation and production mutation require separate explicit Owner authorization. No GitHub Actions. Preserve production environment, rewrite rules, storage and customer data.
- Cleanup requires ownership/reference checks and preservation of unique work. Consolidate current guidance, repair links and retire obsolete chronology through Git history; preserve contracts and operational recovery instructions.
