# Deployment Guide (Production-Safe Rollouts)

This guide documents a safe, repeatable process for rolling out updates to the Dental SaaS without disrupting current clinics, plans, or data.

## Goals
- No downtime or minimal disruption
- No breaking schema changes
- Feature rollout controlled by plan flags
- Clear rollback path

## 1) Pre-Deploy Checklist
- Back up the production database (Hostinger export).
- Confirm the current production `.env` is preserved (never overwrite it).
- Confirm `writable/` is preserved (sessions, uploads, cache).
- Verify the new build on staging with the same configuration except base URL.

## 2) Schema & Migrations (Fail-Closed)
- Only additive migrations in production (new tables/columns with safe defaults).
- Never drop columns/tables in the same release you still need to support.
- New columns must be nullable or have a safe default.
- Ensure any new foreign keys are added only after data is backfilled.

## 3) Feature Flags (Plans)
- Any new feature must be **OFF by default** in plan settings.
- Roll out to a single clinic first.
- After validation, enable per plan as needed.

## 4) Deployment Steps
1. Upload new code (Git or FTP).
2. Preserve `.env` and `writable/`.
3. Run migrations (or apply SQL change set).
4. Clear cache (if needed).
5. Smoke test critical flows:
   - Login
   - Patient create
   - Appointment create
   - Finance create

## 5) Post-Deploy Validation
- Confirm no fatal errors on home/dashboard.
- Confirm tenant isolation (no data leaks).
- Validate new features remain disabled by default.

## 6) Rollback Plan
- Keep a copy of previous code release.
- Restore database backup if schema changes caused issues.
- If only code issue: revert code to last stable release.

## 7) Hostinger-Specific Notes
- `.htaccess` must use `RewriteBase /`.
- SQL imports must be UTF-8 (utf8mb4) and **no CREATE DATABASE**.
- Upload required artifacts:
  - `.env`
  - `democa_dental_saas_schema_superadmin.sql`

## 8) Common Pitfalls
- Importing UTF-16 dumps (causes unexpected character errors).
- Overwriting `.env` or `writable/`.
- Enabling new features globally by default.

## 9) Hostinger Troubleshooting Playbook (Control Plane / Session / Static Assets)
Use this section if post-deploy behavior is inconsistent (for example: `POST /controlplane/enter` or `POST /controlplane/exit` intermittently failing in browser, or static files like `favicon.ico` returning 404/500).

### A) Verify routes are actually present in deployed code
```bash
php spark routes | grep -E "controlplane/(enter|exit|impersonation/exit|settings)|api/notifications"
```
Expected:
- `POST controlplane/enter`
- `POST controlplane/exit`
- `POST controlplane/impersonation/exit`

If missing, deployment is partial/old.

### B) Validate HTTP method behavior correctly
Do not use `HEAD` for POST-only routes.
```bash
curl -i -X POST https://<domain>/controlplane/enter
curl -i -X POST https://<domain>/controlplane/exit
curl -i -X POST https://<domain>/controlplane/impersonation/exit
```
Expected: redirect response (`303`) when route is reachable.

### C) Ensure writable runtime paths exist and are writable
Missing writable folders causes silent operational instability and no logs.
```bash
mkdir -p writable/logs writable/cache writable/debugbar writable/session
chmod -R 775 writable
find writable -type d -exec chmod 775 {} \;
find writable -type f -exec chmod 664 {} \;
```
Quick test:
```bash
php -r "file_put_contents('writable/logs/test.log', date('c').\" host log test\n\", FILE_APPEND); echo \"ok\n\";"
tail -n 5 writable/logs/test.log
```

### D) Confirm environment/session settings
```bash
grep -nE "CI_ENVIRONMENT|app.baseURL|app.forceGlobalSecureRequests|session.driver|session.savePath|session.cookieName" .env
```
Minimum expected:
- `CI_ENVIRONMENT = production`
- `app.baseURL = 'https://<domain>/'`
- `session.driver = 'CodeIgniter\Session\Handlers\FileHandler'`
- `session.savePath = writable/session`

### E) Handle favicon/static path mismatch
If `public/favicon.ico` exists but `https://<domain>/favicon.ico` returns 404, docroot or edge path is mismatched.
Temporary mitigation:
```bash
cp -f public/favicon.ico favicon.ico
cp -f public/favicon.png favicon.png
```
Permanent fix:
- Ensure domain document root points to the intended front controller path and static asset location.
- Recheck Hostinger panel settings and CDN/edge cache behavior.

### F) If browser shows intermittent 405/500 but server curl is healthy
Usually indicates edge/cache or stale deployment state.
Actions:
1. Re-deploy fully (not partial file copy).
2. Clear application cache:
   ```bash
   php spark cache:clear
   ```
3. Re-run the POST curl checks above from server.
4. Capture latest `writable/logs/*.log` and hosting error logs at incident time.

---

If you need a shorter checklist for non-technical staff, create a separate `DEPLOY_CHECKLIST.md`.
