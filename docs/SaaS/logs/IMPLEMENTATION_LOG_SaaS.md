### Task: P5-09a-FIX Notification Governance Corrections
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Fixed logic errors in NotificationService recipient validation and ensured ledger timestamps are populated.
- **Actions:**
    - **Service:** Corrected `isUserInClinic` argument order and used `PatientModel::findByClinic` in `NotificationService`.
    - **Model:** Enabled timestamps in `NotificationModel` to satisfy `created_at` NOT NULL constraint.
- **Verification:** Updated `docs/SaaS/verification/P5-09a.md`. Guardrails passed.

### Task: P5-09b SMTP Email Delivery (Clinic-Scoped)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Enabled outbound email delivery using per-clinic SMTP configuration via a dedicated dispatch job.
- **Actions:**
    - **Service:** Added `dispatchPendingEmails` to `NotificationService` to handle SMTP transport, recipient resolution, and ledger updates.
    - **Job:** Created `SendEmailNotificationsJob` (extending `TenantJob`) as the execution entry point.
    - **Logic:** Implemented strict checks for missing emails, blocked cross-tenant recipients, and recorded all outcomes (sent/failed/blocked) in the ledger.
- **Verification:** `docs/SaaS/verification/P5-09b.md`. Verified blocked (missing email) and failed (SMTP error) states via CLI simulation.
- **Guardrails:** Green.