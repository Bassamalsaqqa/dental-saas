### Task: P5-10-FIX3 Active Patient Quota Compliance
- **Date:** 2026-01-15
- **Status:** Completed
- **Description:** Refined active patient quota logic to dynamically support tables with or without a status column, while strictly enforcing soft-delete checks.
- **Actions:**
    - **Model:** Updated `PatientModel::countActivePatientsByClinic` to conditionally apply `status` check based on schema.
    - **Verification:** Verified SQL generation and logic flows (soft-delete release, status inactive release) via `verify:p5-10-fix3`.
- **Guardrails:** Green.
