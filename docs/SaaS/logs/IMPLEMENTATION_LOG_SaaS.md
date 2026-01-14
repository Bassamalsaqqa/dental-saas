
### Task: S4-02d Appointments DataTables scoping
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Scoped `Appointment` controller read endpoints to the active clinic. Refactored `AppointmentModel` to include clinic-scoped methods with explicit join guards for the `patients` table. Verified no raw queries exist in the controller.
- **Files Changed:**
    - `app/Models/AppointmentModel.php`
    - `app/Controllers/Appointment.php`
- **Verification:** `docs/SaaS/verification/S4-02d.md`
