### Task: P5-09a-FIX Notification Governance Corrections
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Fixed logic errors in NotificationService recipient validation and ensured ledger timestamps are populated.
- **Actions:**
    - **Service:** Corrected `isUserInClinic` argument order and used `PatientModel::findByClinic` in `NotificationService`.
    - **Model:** Enabled timestamps in `NotificationModel` to satisfy `created_at` NOT NULL constraint.
- **Verification:** Updated `docs/SaaS/verification/P5-09a.md`. Guardrails passed.

### Task: P5-09a Notification Governance (Registry + Ledger)
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Implemented notification governance without delivery integration.
- **Actions:**
    - **Schema:** Created `clinic_notification_channels` and `notifications` tables.
    - **Models/Service:** Added `ClinicNotificationChannelModel`, `NotificationModel`, and `NotificationService::enqueue` for tenant-scoped governance.
    - **UI/Controllers:** Added `Settings::channels` with global_mode gating for enable/disable and clinic-only config + validation.
- **Verification:** `docs/SaaS/verification/P5-09a.md` (includes P5-09a-FIX recipient validation + ledger timestamp fixes).
- **Guardrails:** Green (DOM=0, Group=0, Raw=8).
