### Task: P5-11 Tenant Onboarding (Control Plane)
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Implemented a secure, transactional onboarding flow for Superadmins to create new clinics with admin users and subscriptions.
- **Actions:**
    - **Service:** Created `OnboardingService` to handle multi-table insertion in a single transaction.
    - **Controller/View:** Added Control Plane routes and UI for clinic creation.
    - **Verification:** `verify:onboarding` script confirmed end-to-end creation integrity.
- **Guardrails:** Green.

### Task: P5-11-FIX Tenant Onboarding Hardening
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Fixed runtime errors (slug helper) and double-hash password bug. Added Control Plane filters.
- **Actions:**
    - **Service:** Loaded text helper in `OnboardingService`. Removed `password_hash` call (relied on Model callback).
    - **Routes:** Applied `controlplane` filter to onboarding routes.
    - **Verification:** Updated `docs/SaaS/verification/P5-11.md` with real SQL evidence and route checks.
- **Guardrails:** Green.

### Task: P5-11 Verification Evidence Correction
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Replaced non-existent `verify:onboarding` reference with manual CLI/SQL evidence.
- **Actions:**
    - **Verification:** Captured `php spark routes | findstr /I "controlplane/onboarding"` output and live SQL results via PHP mysqli.
    - **Docs:** Updated `docs/SaaS/verification/P5-11.md` to reflect actual commands and outputs.
- **Guardrails:** Green.
