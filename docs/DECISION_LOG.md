## 2026-01-09: Settings Modal Remediation (P3-09)
- **Decision:** `innerHTML` is banned for modal construction in `settings/index.php`.
- **Enforcement:** Use `createElement`, `className`, and `textContent` to build download and restore confirmation modals.
- **Rationale:** Eliminate XSS risks in high-privilege administrative views by ensuring all dynamic content (e.g., filenames) is treated as literal text.