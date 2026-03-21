# DentaCare SaaS (Dental Clinic Management + Control Plane)

This repository contains a multi-tenant dental clinic SaaS application with a Control Plane for platform administration and a Tenant Plane for clinic operations. It is designed around strict tenant isolation, plan gating, and audit‑grade operational logging.

The source of truth for SaaS behavior and guardrails is `docs/SaaS/SAAS_PROGRAM.md`. If this README conflicts with that document, the README is wrong.

---

## Table of Contents
1. Overview
2. Architecture
3. Planes & Access Model
4. Feature Map
5. Tech Stack
6. Local Setup (Windows / XAMPP)
7. Environment Configuration
8. Database & Migrations
9. Superadmin Seed Dump
10. Running the App
11. Control Plane
12. Tenant Plane
13. Notifications (Email / SMS / WhatsApp)
14. Storage & Attachments
15. Contracts & Signatures
16. Kiosk Check‑In
17. Printing & Export
18. Security Model
19. Guardrails & Verification
20. Deploying (Hostinger Notes)
21. Troubleshooting
22. Contributing & Workflow
23. License

---

## 1) Overview
DentaCare SaaS provides:
- A **Control Plane** for managing plans, clinics, global defaults, governance, and platform‑level operations.
- A **Tenant Plane** for clinic staff workflows: patients, examinations, appointments, treatments, prescriptions, finance, reports, and documents.

Key design principles:
- **Fail‑closed** tenant isolation.
- **Plan‑gated features** with centralized enforcement.
- **Audit‑grade logging** for sensitive operations.
- **No public tenant files** (private storage with streaming access).

---

## 2) Architecture
The application is a CodeIgniter 4 app running in a single codebase with a path‑based multi‑tenant model:
- **Control Plane:** `/controlplane/*`
- **Tenant Plane:** all other authenticated clinic routes

Database is **shared** but **tenant‑scoped** with a `clinic_id` column on tenant‑owned tables. `TenantAwareModel` enforces automatic scoping and fail‑closed behavior.

---

## 3) Planes & Access Model
### Control Plane
Used by platform admins only.
- Requires `global_mode = true`.
- Rejects any `active_clinic_id`.
- Uses `main_control_plane` layout.

### Tenant Plane
Used by clinic staff.
- Requires `active_clinic_id`.
- Rejects `global_mode = true`.

### Impersonation
Control Plane operators can impersonate a clinic. Session is regenerated on transition and audit logs are captured.

---

## 4) Feature Map
### Core Clinic Workflows
- Patients
- Appointments
- Examinations
- Treatments
- Prescriptions
- Inventory
- Finance (invoices, payments, revenue tracking)
- Reports & analytics

### SaaS Extensions
- Plan‑gated features
- Patient attachments
- Treatment contracts + signature capture
- Kiosk self check‑in

### Control Plane
- Clinics onboarding and lifecycle actions
- Plans & plan requests
- Global settings and defaults
- Email template library

---

## 5) Tech Stack
- **Backend:** PHP 8.x, CodeIgniter 4
- **Database:** MySQL 8.x
- **Frontend:** PHP views + Tailwind CSS
- **JS:** Vanilla + jQuery (some pages)
- **Container:** Docker MySQL available for dump/export workflows (optional)

---

## 6) Local Setup (Windows / XAMPP)
1. Clone repo into XAMPP htdocs:
   `C:\xampp\htdocs\dental-saas`
2. Start XAMPP Apache + MySQL.
3. Create the database:
   - `democa_dental_saas`
4. Import SQL (see section 9).
5. Use XAMPP MySQL as the default local runtime DB:
   - host: `127.0.0.1`
   - port: `3306`
   - user: `root`
   - password: blank

Docker MySQL remains optional for regenerating dumps or comparing data, but it is not required to run the app locally on this machine.

---

## 7) Environment Configuration
Copy `.env.example` to `.env` and set:
```
CI_ENVIRONMENT = development
app.baseURL = 'https://localhost/dental-saas/'
database.default.hostname = 127.0.0.1
database.default.database = democa_dental_saas
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
encryption.key = <your_key>
session.cookieName = 'ci_session_saas'
```
Notes:
- `app.baseURL` must match your docroot mapping.
- `session.cookieName` is set to avoid collisions.
- If you intentionally use the Docker MySQL container instead, switch the DB port back to `3307` and password to `root`.

---

## 8) Database & Migrations
Run:
```
php spark migrate
```
If you see migration errors:
- Check file encoding (UTF‑8 without BOM).
- Confirm database connection settings.

---

## 9) Superadmin Seed Dump
There is a maintained SQL dump with the superadmin user:
- `democa_dental_saas_schema_superadmin.sql`

Import it into a fresh database if you need a complete schema + admin user.

To regenerate that dump from the local Docker MySQL container:
```bash
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

---

## 10) Running the App
Navigate to:
- Tenant login: `https://localhost/dental-saas/auth/login`
- Control Plane entry: `https://localhost/dental-saas/controlplane`

The Control Plane requires `global_mode` (set via superadmin session).

### Frontend Assets
- Canonical runtime assets live in `public/assets/`.
- Tailwind source is `src/input.css`.
- Tailwind build output is `public/assets/css/tailwind.css`.
- URL paths still use `/assets/...`; the root `.htaccess` aliases that to `public/assets/...`.
- Do not edit a repo-root `assets/` folder. That path is legacy and not the runtime source of truth.
- Hostinger does not run the Node/Tailwind build for you. If you change Tailwind classes or `src/input.css`, run `npm run build` locally and deploy the updated `public/assets/css/tailwind.css` with your PHP changes.

---

## 11) Control Plane
Key features:
- Clinic onboarding
- Plan management
- Plan requests inbox
- Control Plane settings & defaults
- Email templates library

Control Plane uses a dedicated layout to avoid tenant‑heavy assets.

---

## 12) Tenant Plane
Tenant workflows:
- Patient management
- Appointment scheduling
- Examinations
- Treatments
- Prescriptions
- Finance + invoices
- Reports

Tenant access is blocked without `active_clinic_id`.

---

## 13) Notifications (Email / SMS / WhatsApp)
Notification system includes:
- A ledger of outbound notifications.
- Channel configuration per clinic.
- Plan‑gated channel availability.

Email dispatch is implemented via SMTP configuration.
SMS and WhatsApp are configured at settings level but **delivery integrations may be incomplete** depending on task state.

---

## 14) Storage & Attachments
Patient attachments:
- Plan‑gated.
- Size limits enforced server‑side.
- Stored in private tenant storage.
- Download via streaming controllers only.

---

## 15) Contracts & Signatures
Treatment contracts:
- Generate odontogram snapshots.
- Itemized treatment list.
- E‑signature capture.
- Printable output.

---

## 16) Kiosk Check‑In
Kiosk feature:
- Plan‑gated.
- Public check‑in page with PIN.
- Per‑clinic branding support.

---

## 17) Printing & Export
Print views:
- Designed for browser print.
- Clinic branding where applicable.
- Some exports are processed server‑side for PDF‑safe rendering.

---

## 18) Security Model
Non‑negotiables (see `docs/SaaS/SAAS_PROGRAM.md`):
- Tenant isolation via `clinic_id` on every tenant table.
- `TenantAwareModel` enforces scoping and fail‑closed behavior.
- Control Plane access requires `global_mode`.
- No public tenant files or URLs.

---

## 19) Guardrails & Verification
Guardrails:
- DOM sink scans
- Raw tenant query bans in controllers
- Group‑based auth bans

Recommended checks:
```
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\\.html\\(" app/Views
php spark routes | findstr /I "controlplane api/v1 api/search"
bash scripts/ci/saas_guardrails.sh
```

Verification docs live in `docs/SaaS/verification/`.

---

## 20) Deploying (Hostinger Notes)
Common steps:
1. Upload codebase.
2. Upload `.env`.
3. Import SQL dump (`democa_dental_saas_schema_superadmin.sql`).
4. Ensure `.htaccess` `RewriteBase` matches host root (often `/`).

If `/auth/login` 404s:
- Check `RewriteBase` and docroot.

---

## 21) Troubleshooting
**Migration fails with namespace error**
- Ensure migration files are saved as UTF‑8 without BOM.

**404 on tenant routes**
- Confirm `app.baseURL` and `.htaccess`.
- Ensure rewrite rules are active.

**Plan gate blocks features**
- Verify clinic plan and feature toggles.

---

## 22) Contributing & Workflow
Follow instructions in `docs/SaaS/SAAS_PROGRAM.md`.
- Always use Task IDs for scoped changes.
- Append to `docs/SaaS/logs/*` when required.
- Generate verification docs for each Task ID.

---

## 23) License
See `LICENSE`.
