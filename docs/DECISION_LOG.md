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