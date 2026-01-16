### Task: P5-11-UX Control Plane UX & Profile
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Restored profile routes and added Control Plane visibility/dashboard.
- **Actions:**
    - **Routes:** Restored `/profile` routes (GET/POST) and added `/controlplane/dashboard`.
    - **Controller:** Created `ControlPlane\Dashboard` and updated `BaseController` to inject superadmin status.
    - **View:** Created `control_plane/dashboard.php` and updated `main_auth.php` navigation logic.
- **Verification:** `docs/SaaS/verification/P5-11-UX.md` confirms route availability and navigation logic.
- **Guardrails:** Green.

### Task: P5-11-UX-FIX Control Plane Enter Redirect
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Fixed redirect target after entering Global Mode.
- **Actions:**
    - **Controller:** Updated `ControlPlane::enter()` to redirect to `/controlplane/dashboard`.
- **Verification:** Appended to `docs/SaaS/verification/P5-11-UX.md`.
- **Guardrails:** Green.

### Task: P5-11-UX-FIX2 ControlPlaneFilter Permission Guard
- **Date:** 2026-01-16
- **Status:** Completed
- **Description:** Fixed null `PermissionService` usage in `ControlPlaneFilter` to prevent fatal error when entering Global Mode.
- **Actions:**
    - **Filter:** Added fallback instantiation of `PermissionService` if `service('permission')` returns null.
    - **Verification:** Appended to `docs/SaaS/verification/P5-11-UX.md`.
- **Guardrails:** Green.
