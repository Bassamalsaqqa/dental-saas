# React / Lovable Workflow

Last updated: 2026-08-02

## Decision

- Keep CodeIgniter 4 and MySQL as backend truth.
- Lovable develops UI/UX in the separate frontend repository.
- Import only reviewed frontend work; do not let Lovable modify backend code or generated deployment output.
- React remains mock-only until a specific `/app-api/v1/*` integration slice is approved.

## Repository Ownership

| Repository | Owns |
|---|---|
| `Bassamalsaqqa/dental-saas` | CI4 backend, contracts, generated `public/app` assets |
| `Bassamalsaqqa/dentacare-shell-5051486e` | React/Lovable source |

The local frontend repository may be nested at `frontend/`, but the parent must ignore it and track zero files beneath it. It is not a submodule or subtree.

## Deployment Invariants

- npm and `package-lock.json` are canonical; no `bun.lockb`.
- Vite stays `5.4.x` unless explicitly approved.
- Vite `base` is `/app/`.
- Vite `build.outDir` is `../public/app` with `emptyOutDir: true`.
- `BrowserRouter` uses `basename="/app"`; internal links are basename-relative.
- Parent repo commits generated `public/app`; frontend repo commits source/config.

## Lovable Guardrails

Lovable must not add:

- Supabase or another backend/auth system.
- local/session storage auth tokens.
- Bearer or `X-Clinic-Id` for the first-party app.
- direct database access or component-level API calls.
- tenant/RBAC enforcement, finance math, payout/COGS logic, WhatsApp dispatch, or clinical inference.
- backend files, PHP routes/controllers/models, `.htaccess` changes, dependency upgrades, or generated `public/app` output.

## Import Verification

```powershell
git status -sb
git remote -v
Test-Path bun.lockb
npm.cmd ls vite @vitejs/plugin-react-swc lovable-tagger
npm.cmd run lint
npm.cmd run build
npx tsc --noEmit
```

Safety scans:

```powershell
rg -n "supabase|createClient\(|@supabase|localStorage|sessionStorage|Bearer|X-Clinic-Id" src package.json
rg -n "fetch\(|axios\.|/api/v1|/app-api/v1" src
rg -n "to=['\"]\/app|navigate\(['\"]\/app|href=['\"]\/app" src
```

Real integration begins with foundation/bootstrap/auth/clinic context, then Patients, one reviewed contract at a time. The old prompt library remains available in Git history.
