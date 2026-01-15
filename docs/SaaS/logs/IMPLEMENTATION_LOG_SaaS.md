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

### Task: P5-09e Notification Ledger UI + Retry Governance
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Implemented tenant-scoped ledger UI and secure retry mechanism.
- **Actions:**
    - **Schema:** Added `parent_notification_id` to notifications table.
    - **Controller:** Created `NotificationLedger` for viewing history and triggering retries.
    - **Service:** Added `retryNotification` method implementing Option B (new row, parent link, re-validation).
    - **UI:** Added `notifications/ledger.php` with filters and masked data.
- **Verification:** `docs/SaaS/verification/P5-09e.md` confirms retry lineage and cross-clinic blocking.
- **Guardrails:** Green.
