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

---

If you need a shorter checklist for non-technical staff, create a separate `DEPLOY_CHECKLIST.md`.
