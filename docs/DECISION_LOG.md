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
- Rationale: As a global component that handles arbitrary messages (including those from database or external APIs), the toast system is a high-impact XSS vector. Strict adherence to safe DOM APIs is required.

## 2026-01-09: Odontogram List Remediation (P2-03)
- **Decision:** All dynamic `innerHTML` usage is banned in the `odontogram/list.php` view.
- **Enforcement:** Table row rendering, pagination, and notifications must use `createElement` and `textContent`. Button state transitions must use `cloneNode` caching to preserve icons.
- Rationale: The Odontogram list processes sensitive patient data from AJAX responses. Safe DOM construction is mandatory to prevent XSS.

## 2026-01-09: Inventory List Remediation (P2-04)
- **Decision:** All dynamic `innerHTML` and `outerHTML` usage is banned in the `inventory/index.php` view.
- **Enforcement:** Notifications, table updates, and error messages must use `createElement` and `textContent`. Print functionality must use DOM cloning instead of `outerHTML` serialization.
- Rationale: Prevents XSS when rendering inventory item names, descriptions, or error messages from the server.

## 2026-01-09: Notifications UI Remediation (P3-01)
- **Decision:** The global notifications UI in `main_auth.php` must not use `innerHTML` or template literals for rendering.
- **Enforcement:** Use `DocumentFragment`, `createElement`, and `textContent` to build the notification list.
- Rationale: Notifications contain user-generated content (names, messages) and are a persistent UI element, making them a critical XSS vector if not handled safely.

## 2026-01-09: RBAC Setup UI Remediation (P3-02)
- **Decision:** All `innerHTML`, `insertAdjacentHTML`, and `outerHTML` usage is banned in `app/Views/rbac/setup.php`.
- **Enforcement:** Status updates, notifications, and modal dialogs must be constructed using `createElement` and `textContent`. Button state changes must use child node caching (`cloneNode`) instead of string replacement.
- Rationale: The setup page handles system configuration and status reporting. While currently mostly internal, using safe DOM methods ensures resilience against future changes that might introduce user-controlled data.

## 2026-01-09: Inventory Usage History Remediation (P3-03)
- **Decision:** All dynamic `innerHTML` and `outerHTML` usage is banned in `inventory/usage_history.php`.
- **Enforcement:** Details panels, print views, and error messages must be constructed using `createElement` and `textContent`.
- **Rationale:** The usage history view displays detailed records including user notes and item names. Safe DOM construction prevents XSS from these potentially user-controlled fields.


