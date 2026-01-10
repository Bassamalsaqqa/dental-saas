# Architectural Decision Log - DentaCare Pro Security Hardening

This log records significant architectural and security decisions made during the hardening process.

## 2026-01-09: Secrets & Dump Management (P0-06)
- **Decision:** Untrack `.env` and all `.sql` dumps from the repository. Provide a comprehensive `.env.example` and a local setup guide.
- **Rationale:** Prevent accidental exposure of credentials and sensitive data in the version control history.
- **Constraint:** Developers must manually create `.env` from the example and obtain data dumps through secure, out-of-band channels.

## 2026-01-09: Database Debug Policy (P1-04)
- **Decision:** `DBDebug` in `app/Config/Database.php` is now strictly linked to the `CI_ENVIRONMENT` constant. It is `true` only if `ENVIRONMENT === 'development'`.
- **Rationale:** Prevent database schema, query, or connection details from leaking in error messages on production or testing environments.

## 2026-01-09: Script Access Control (P1-04)
- **Decision:** All scripts in the `/scripts` directory are now "CLI-Only" and blocked at both the web server level (`.htaccess`) and the application level (PHP SAPI check).
- **Rationale:** Direct HTTP access to utility or setup scripts is a high-risk attack vector. Fail-closed security requires multiple layers of blocking.
- **Impact:** These scripts can only be executed via the command line (e.g., `php scripts/init_database.php`).

## 2026-01-09: XSS Prevention Policy (P2-01)
- **Decision:** `innerHTML` and jQuery `.html()` are banned for rendering user-controlled, database, or API-sourced strings.
- **Enforcement:** Use `textContent` for plain text, `setAttribute` for attributes, and explicit `document.createElement`/`appendChild` for building complex nodes.
- **Rationale:** Prevent DOM-based and Persistent XSS by ensuring data is always treated as literal text rather than executable markup.
- **Exception:** Static markup with NO variable interpolation is allowed but discouraged.

## 2026-01-09: Toast Component Hardening (P2-02)
- **Decision:** All `innerHTML` and `.html()` usage is banned in the `toast.php` component.
- **Enforcement:** The component must build its DOM nodes explicitly using `createElement` and `textContent`.
- **Rationale:** As a global component that handles arbitrary messages (including those from database or external APIs), the toast system is a high-impact XSS vector. Strict adherence to safe DOM APIs is required.

## 2026-01-09: Odontogram List Remediation (P2-03)
- **Decision:** All dynamic `innerHTML` usage is banned in the `odontogram/list.php` view.
- **Enforcement:** Table row rendering, pagination, and notifications must use `createElement` and `textContent`. Button state transitions must use `cloneNode` caching to preserve icons.
- **Rationale:** The Odontogram list processes sensitive patient data from AJAX responses. Safe DOM construction is mandatory to prevent XSS.

## 2026-01-09: Inventory List Remediation (P2-04)
- **Decision:** All dynamic `innerHTML` and `outerHTML` usage is banned in the `inventory/index.php` view.
- **Enforcement:** Notifications, table updates, and error messages must use `createElement` and `textContent`. Print functionality must use DOM cloning instead of `outerHTML` serialization.
- **Rationale:** Prevents XSS when rendering inventory item names, descriptions, or error messages from the server.

## 2026-01-09: Notifications UI Remediation (P3-01)
- **Decision:** The global notifications UI in `main_auth.php` must not use `innerHTML` or template literals for rendering.
- **Enforcement:** Use `DocumentFragment`, `createElement`, and `textContent` to build the notification list.
- **Rationale:** Notifications contain user-generated content (names, messages) and are a persistent UI element, making them a critical XSS vector if not handled safely.

## 2026-01-09: RBAC Setup UI Remediation (P3-02)
- **Decision:** All `innerHTML`, `insertAdjacentHTML`, and `outerHTML` usage is banned in `app/Views/rbac/setup.php`.
- **Enforcement:** Status updates, notifications, and modal dialogs must be constructed using `createElement` and `textContent`. Button state changes must use child node caching (`cloneNode`) instead of string replacement.
- **Rationale:** The setup page handles system configuration and status reporting. While currently mostly internal, using safe DOM methods ensures resilience against future changes that might introduce user-controlled data.

## 2026-01-09: Inventory Usage History Remediation (P3-03)
- **Decision:** All dynamic `innerHTML` and `outerHTML` usage is banned in `inventory/usage_history.php`.
- **Enforcement:** Details panels, print views, and error messages must be constructed using `createElement` and `textContent`.
- **Rationale:** The usage history view displays detailed records including user notes and item names. Safe DOM construction prevents XSS from these potentially user-controlled fields.

## 2026-01-09: User Management Permissions Remediation (P3-04)
- **Decision:** All dynamic `innerHTML` usage is banned in `user_management/index.php`.
- **Enforcement:** Loading indicators, error messages, and the permission interface must be constructed using `createElement` and `textContent`.
- **Rationale:** User management views handle sensitive administrative functions. Using safe DOM methods prevents XSS in the context of user roles and permissions management.

## 2026-01-09: Appointment Creation UI Remediation (P3-05)
- **Decision:** All dynamic `innerHTML` usage is banned in `appointment/create.php`.
- **Enforcement:** Time slot options, loading indicators, and error messages must be constructed using `createElement`, `textContent`, and `replaceChildren`.
- **Rationale:** Ensures that dynamic data returned from the time slot API is rendered safely, preventing potential XSS vectors in the appointment scheduling flow.

## 2026-01-09: Appointment Edit UI Remediation (P3-06)
- **Decision:** All dynamic `innerHTML` usage is banned in `appointment/edit.php`.
- **Enforcement:** Time slot options and error states must be constructed using `createElement`, `textContent`, and `replaceChildren`.
- **Rationale:** Prevents XSS when rendering dynamic time slot data. Also ensures robust handling of JSON object responses (with CSRF tokens) from the API.

## 2026-01-09: Odontogram Interaction Remediation (P3-07)
- **Decision:** `innerHTML` is banned for button state updates and notifications in `odontogram/index.php`.
- **Enforcement:** Use `replaceChildren` with cached nodes for restoring button states. Use `createElement` and `textContent` for notifications.
- **Rationale:** Ensures that even if notification messages or button text were to become dynamic (e.g., including user input), they would be rendered safely without risk of XSS.

## 2026-01-09: Patient Index Modal Remediation (P3-08)
- **Decision:** `innerHTML` is banned for modal construction in `patient/index_new.php`.
- **Enforcement:** Use `createElement`, `className`, and `appendChild` to build the delete confirmation modal.
- **Rationale:** Eliminate any risk of XSS in the modal construction, ensuring all content is treated as literal text or safe DOM nodes.






## 2026-01-09: Settings Modal Remediation (P3-09)
- **Decision:** `innerHTML` is banned for modal construction in `settings/index.php`.
- **Enforcement:** Use `createElement`, `className`, and `textContent` to build download and restore confirmation modals.
- **Rationale:** Eliminate XSS risks in high-privilege administrative views by ensuring all dynamic content (e.g., filenames) is treated as literal text.

## 2026-01-10: Appointment Index Remediation (P3-10)
- **Decision:** \innerHTML\ is banned for dynamic content in \ppointment/index.php\.
- **Enforcement:** Use \createElement\, \className\, and \	extContent\ to build notification toasts and dynamic appointment cards.
- **Rationale:** Eliminate XSS risks in the high-traffic appointment management interface by ensuring all patient and appointment data is treated as literal text.

## 2026-01-10: Appointment Index Remediation (P3-10) - Correction Append (Final)

- **Decision:** Dynamic DOM-string sinks (innerHTML, insertAdjacentHTML, outerHTML, jQuery .html()) are prohibited in app/Views/appointment/index.php.
- **Rationale:** Appointment index renders user- and database-derived strings in a high-traffic workflow; DOM-string sinks increase DOM XSS risk.
- **Enforcement:** Use createElement, textContent, appendChild, and replaceChildren for all dynamic rendering. Icons must be DOM nodes only.
- **Scope:** Append-only correction. Existing corrupted entries remain unchanged to preserve audit history.

## 2026-01-10: Global DOM-String Sink Remediation (P3-11)
- **Decision:** All dynamic content rendering across allowlisted high and medium priority views must avoid `innerHTML`, `outerHTML`, `insertAdjacentHTML`, and jQuery `.html()`.
- **Enforcement:** Use safe DOM construction methods: `document.createElement`, `textContent`, `appendChild`, and `element.replaceChildren`. For SVG, use `createElementNS`.
- **Rationale:** Standardizing on safe DOM APIs eliminates the primary vector for DOM-based XSS by ensuring all dynamic data (from database, user input, or external APIs) is treated as literal text or safe DOM nodes rather than executable markup.
- **Scope:** Applied to Finance, Prescription, Examination, Appointment, and Inventory view modules.
### 2026-01-10: Branding Centralization (P4-01)
- **Decision:** Override BaseController::view() to inject clinic info globally.
- **Context:** Hard-coded branding strings made the system difficult to white-label.
- **Impact:** All views now have access to a \ array containing name, address, etc. Layouts and print views use this data with safe escaping (esc()).

### CORRECTION APPEND — P4-01
- **Correction:** The previous entry for P4-01 contained a typo '\ array'. It should be ' array'.
- **Clarification:** Clinic info is injected globally only if not already provided by the controller, preserving original merge precedence.

### CORRECTION APPEND — P4-01 Final Fixes
- **Decision:** Inject clinic info in initController() via Services::renderer()->setVar().
- **Context:** Controllers using global view() helper missed injected data.
- **Impact:** Clinic info is now available to all view() calls (both method and helper) while preserving local controller overrides. Sidebar display is safely escaped.

### Decision: P4-02a Branding Centralization
- **Decision:** Store tagline and logo path in settings. 'Professional Suite' is now a DB/Service default, not a layout literal.
- **Context:** Layouts need to be white-label capable without code changes.
- **Impact:** Clinic can customize tagline and logo via UI. Supports relative paths (base_url aware) and absolute URLs.

### Decision: P4-02a Logo and Tagline Safety
- **Decision:** All logo source URLs must be escaped at the point of output. Hard-coded branding/tagline strings (e.g., 'Professional Suite') are prohibited in production views.
- **Context:** To ensure security and full white-label capability.
- **Impact:** System relies entirely on settings for branding identity.

### Decision: P4-02b Secure Logo Upload
- **Decision:** Use deterministic naming (clinic-logo.ext) and exclude SVG support.
- **Rationale:** Deterministic naming prevents filename-based path traversal or attribute injection. SVG exclusion eliminates XSS risks via XML/scripts in images.
- **Impact:** Robust and secure branding customization via the UI.

### Decision: P4-02b Logo Path Source
- **Decision:** clinic_logo_path is only updated upon successful file upload, never directly from POST.
- **Rationale:** Ensures that the stored path is always a system-generated relative path under uploads/clinic, preventing path traversal or attribute injection via manual path input.
