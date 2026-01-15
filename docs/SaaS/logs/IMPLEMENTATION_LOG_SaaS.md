### Task: P5-09a-FIX Notification Governance Corrections
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Fixed logic errors in NotificationService recipient validation and ensured ledger timestamps are populated.
- **Actions:**
    - **Service:** Corrected `isUserInClinic` argument order and used `PatientModel::findByClinic` in `NotificationService`.
    - **Model:** Enabled timestamps in `NotificationModel` to satisfy `created_at` NOT NULL constraint.
- **Verification:** Updated `docs/SaaS/verification/P5-09a.md`. Guardrails passed.
