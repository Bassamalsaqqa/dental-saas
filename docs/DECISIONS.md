# Architectural Decision Log

Append-only. Do not edit previous entries.

## 2026-06-25 - Font Awesome Is Vendored and Validated App-Wide

- **Decision:** DentaCare loads Font Awesome from the local vendored package under `public/assets/vendor/fontawesome`, not from CDN links in individual views or layouts.
- **Rationale:** CDN-only icon loading caused inconsistent icon failures across login, header, control-plane, clinic-selection, kiosk, and other standalone pages. Some failures were also caused by unsupported Font Awesome class names and broad app typography rules overriding icon pseudo-elements.
- **Impact:** New UI work must reuse the local Font Awesome asset, must not add `cdnjs.cloudflare.com/ajax/libs/font-awesome` links, and must use icon classes present in the vendored Font Awesome Free 6.4.0 CSS. Layout-level icon font guards must include both the icon element and `::before` pseudo-element so app font rules cannot break glyph rendering.

## 2026-05-19 - Clinical Finance Traceability Requires Charge Capture Layer

- **Decision:** DentaCare will treat clinical-to-finance traceability as a charge-capture architecture problem. Clinical records, treatments, contracts, inventory usage, invoices, payments, provider payouts, and reports must be linked through canonical billable charge lines and later invoice/payment allocation rows, rather than guessing financial impact from document-level invoice totals.
- **Rationale:** Professional dental systems separate production from collections, allocate payments to providers/procedures, and keep patient ledgers traceable to clinical source records. Current DentaCare finance rows can link to patient/examination and provider payouts, but this is not enough for multi-provider invoices, guest doctors, treatment-plan acceptance, partial payments, adjustments, refunds, and production-vs-collection reporting.
- **Impact:** Future work should follow `docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`. Do not mix supplier payables with provider payouts, do not count contracts as revenue, do not treat appointments as production except explicit fees, and do not place finance math in frontend/views. The product UI must stay simple for clinic managers: show charges, payments, balance due, provider share, and linked records instead of accounting jargon.

## 2026-05-06 - Entity Workspaces Use Contextual Quick-Create Drawers

- **Decision:** Major entity detail pages may become command workspaces with contextual quick-create drawers. The Patient Profile is the reference implementation. Drawers can start related work from the current entity, but canonical modules remain responsible for real workflow ownership.
- **Rationale:** Clinic users think in entity context: "I am working on this patient/provider/appointment and need to add related work." The UI should preserve that context without duplicating full modules or scattering random popups.
- **Impact:** For current PHP pages, drawers should submit to existing canonical web routes when possible so CSRF, tenant context, permissions, subscription gates, validation, activity logging, redirects, and backend-owned calculations remain unchanged. Future pages should follow `docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` before adding entity quick actions.

## 2026-05-04 - React Frontend Uses First-Party Session App API

- **Decision:** Future React tenant frontend will use `/app-api/v1/*` as a first-party, same-origin, session-cookie JSON API. Existing `/api/v1/*` remains public/stateless and continues to require Bearer auth plus `X-Clinic-Id`. Future React control-plane work reserves and recommends `/controlplane-app-api/v1/*` to preserve the tenant/control-plane airlock.
- **Rationale:** The current UX/workflow issues are frontend and app-contract problems, not a reason to replace the backend immediately. `app/Config/Filters.php` excludes `api/*` from CSRF, so the first-party React API must not live under `/api/*`. `/app-api/v1/*` can use the existing CI4 session, httpOnly cookie, CSRF, server-controlled `active_clinic_id`, RBAC, plan gates, and tenant filters without weakening the public API contract.
- **Impact:** CI4 and MySQL stay as the backend for now. Laravel migration is deferred. Lovable may generate mock React UI only; Codex must import/stitch it to backend-owned app API contracts and must not trust Lovable for auth, tenant isolation, finance calculations, provider payouts, supplier payables, inventory COGS, WhatsApp dispatch, file access, or odontogram persistence.

## 2026-05-04 — Session-Based Navigation Stack Replaces `return_to` Query Params

- **Decision:** A `NavigationService` (session-based, 20-entry stack) auto-tracks every GET HTML page visit. A `nav_back($fallback)` helper function checks the stack first, then falls back to the provided default URL. The old `return_to` query parameter system is stripped from all view links (kept only in form hidden inputs for post-submit redirects and language switch).
- **Rationale:** The `return_to` query parameter approach was fragile — easily lost on redirects, auth, and clinic switching. Multiple entry points to the same page meant different back behaviors. The session stack works across any navigation path without manual plumbing.
- **Impact:** All back buttons in hero headers and page headers now use `nav_back()` or `service('navigation')->previous()`. A breadcrumb bar in the layout shows the navigation trail. New pages get smart back navigation automatically — no `return_to` query params needed in links.

## 2026-05-03 — Patient Hero Card Pattern Standardized Across App

- **Decision:** All detail/show pages (patient, examination, treatment, prescription, finance) share the same hero header pattern: `patient-hero-card` with CSS variable gradients, `patient-hero-overlay` orbs, `patient-avatar-badge`, `patient-hero-chip` info capsules, and `patient-header-action` buttons in a fixed-width right column.
- **Rationale:** Consistent visual identity across the app. Users instantly recognize the page type and available actions. The CSS is shared across views (each includes the full set in a `<style>` block).
- **Impact:** 5 show views use this pattern. The hero card CSS is ~170 lines duplicated per view (trade-off: self-contained views vs shared CSS file).

## 2026-05-02 — Unified Providers Directory (No New Master Table)

- **Decision:** `/providers` queries `suppliers` and `provider_payouts` (GROUP BY provider) separately, merges in PHP, sorts by name, and paginates with `array_slice`. No new database tables or migrations. The supplier quick-create modal reuses the existing `inventory/suppliers/quick-create` endpoint.
- **Rationale:** Data volumes are small enough that in-memory merge is simpler than UNION queries or a materialized directory. Supplier and provider concepts remain separate at the database level — the unified directory is a read/workspace convenience, not a new entity.
- **Impact:** Providers controller uses OR permission logic (`inventory:view` OR `finance:view`). Sidebar uses an extended permission map format for OR conditions. No new provider master table exists yet — this can be revisited if supplier/provider workflows need deeper unification.

## 2026-05-02 — Provider Shares Stay Separate From Supplier Payables

- **Decision:** Tenant finance keeps provider payouts as the lane for clinic users or outside providers who receive a share from a patient transaction. Supplier, lab, product, inventory, rent, and other vendor bills stay in expenses/payables.
- **Rationale:** Clinics need a simple operational model: pay providers from patient-treatment collections, and pay suppliers/vendors from bills. Mixing suppliers into provider split rows creates reporting ambiguity and possible double-counting.
- **Impact:** Provider dropdowns can remain broad enough for active clinic users and guest/outside providers. Supplier directory linkage is preferred for vendor bills. Finance reports continue to separate provider dues from supplier/payable balances.

## 2026-03-20 — WhatsApp Product Flows Stay Template-First and Ledger-Backed

- **Decision:** Appointment confirmations, queued appointment reminders, completed-exam follow-up, and invoice payment reminders enqueue WhatsApp notifications through `NotificationService` with `message_template_key` / `template_vars`; domain workflows do not call the provider directly.
- **Rationale:** Preserves fail-closed governance, plan/channel gating, webhook correlation, retry policy, and auditable delivery state through the unified notification ledger.
- **Impact:** New product flows use `WhatsAppWorkflowService` plus `whatsapp:queue-product-flows`; future WhatsApp business events should follow the same enqueue-only pattern.

## 2026-03-20 — Tenant WhatsApp Workflow Toggles Live in Tenant Policy

- **Decision:** Clinic-level enable/disable switches for WhatsApp workflow types are stored in `whatsapp_tenant_policies.trigger_toggles_json`, not in channel config or per-user notification preferences.
- **Rationale:** These switches govern tenant-wide automated patient messaging behavior, so they belong with tenant policy controls rather than personal alert preferences or transport credentials.
- **Impact:** Tenant settings can disable appointment, follow-up, or payment WhatsApp flows independently while keeping the WhatsApp channel configured and available.

## 2026-03-20 — WhatsApp Remains a Commercial Plan Feature

- **Decision:** WhatsApp must remain exposed in the control-plane plan editor under `notifications.whatsapp.enabled`.
- **Rationale:** Tenant-side operational controls must not bypass commercial entitlement; feature availability remains plan-governed.

## 2026-03-20 — No-Show Follow-Up Is the Next Valid Workflow Toggle

- **Decision:** Added `no_show_follow_up` as a tenant-configurable WhatsApp workflow. Did not add recall/campaign toggles.
- **Rationale:** `no_show` is a real appointment state with an existing domain event hook; recall/campaign flows are not yet implemented.

## 2026-03-24 — WhatsApp Trigger Enablement Does Not Imply Readiness

- **Decision:** A WhatsApp workflow trigger may be globally or clinic-enabled only in the rollout sense; operational readiness is a separate state that requires a valid approved Meta template mapping.
- **Rationale:** Treating an enabled trigger as implicitly usable hides configuration debt and creates late failures during real sends.
- **Impact:** Control-plane WhatsApp settings show `Ready` vs `Needs config` per trigger. Global template saves fail closed when trigger mappings are invalid.

## 2026-02-28 — Bilingual Rollout Strategy (Foundation-First)

- **Decision:** Ship bilingual support in two layers: (1) locale/RTL infrastructure + shell localization first, (2) module-by-module content translation later.
- **Rationale:** Enables safe adoption with minimal regression risk, preserves current English-first UX.
- **Impact:** Current release includes global locale/dir support and tenant UI language setting. Full in-page/module translation remains an explicit follow-up phase.

## 2026-02-09 — Control Plane Settings Scope

- **Decision:** Control plane settings manage platform identity and governance (retention policies) only; tenant feature availability remains exclusively managed through plan configuration.
- **Rationale:** Prevent inconsistent feature states and keep commercial entitlements centralized.

## 2026-02-09 — Tenant Defaults for New Clinics

- **Decision:** Control plane defines default system preferences for new clinics (timezone, date/time formats, currency, working hours) and applies them only at onboarding.
- **Impact:** New clinics are seeded with platform defaults at creation; existing clinics are unchanged.

## 2026-02-07 — Kiosk Check-In Access Model

- **Decision:** Kiosk check-in uses a clinic-specific URL (`/kiosk/{clinic_uuid}/check-in`) protected by a clinic PIN. Branding strings are configurable per clinic.
- **Rationale:** Static kiosk link is simple for in-clinic devices; PIN prevents unauthorized use if URL leaks.

## 2026-02-07 — Kiosk Feature Gating

- **Decision:** Kiosk check-in controlled by plan feature flag (`features.kiosk.enabled`), fail-closed even if the clinic settings toggle is enabled.

## 2026-02-07 — Revenue Recognition Basis

- **Decision:** Revenue metrics are computed on payments received (standalone + invoice payments) minus refunds. Invoices do not count as revenue until paid.
- **Rationale:** Matches cash-basis expectations in small clinics and supports partial payments without double-counting.

## 2026-01-17 — NEAR-LOCKED Closure + API-B Redesign

- **Decisions:**
  - Web concealment: unauthorized/out-of-plane web access returns 404 (PageNotFoundException), no 403 or redirects.
  - Airlock: when `global_mode=true`, tenant routes fail-closed (404) and do not redirect into tenant UX.
  - API-B stateless: `/api/v1/*` becomes bearer-token stateless; cookie auth is forbidden.
  - API-C0 (no API concealment): API returns deterministic JSON error semantics; no 404 for auth/plan/quota failures.
- **API Error Semantics:**
  - 401 `unauthenticated`
  - 403 `forbidden` (RBAC) + `subscription_inactive` / `plan_quota_exceeded`
  - 422 `tenant_context_missing` / `tenant_context_invalid`
  - 429 `rate_limited`

## 2026-01-13 — Establish SaaS Source of Truth

- **Decision:** `docs/SaaS/SAAS_PROGRAM.md` is the canonical SaaS governance doc. All SaaS tasks must reference it.

## 2026-01-09 — Foundational Security Decisions

- **Secrets & Dumps:** `.env` and `.sql` dumps untracked from repo. `.env.example` and local setup guide provided.
- **DBDebug:** Strictly linked to `CI_ENVIRONMENT`; true only in development.
- **Script Access:** All scripts in `/scripts` are CLI-only, blocked at web server and application level.
- **XSS Prevention:** `innerHTML` and jQuery `.html()` banned for rendering user-controlled, database, or API-sourced strings. Use `textContent`, `setAttribute`, `createElement`/`appendChild`.
- **Branding Centralization:** Override `BaseController::view()` to inject clinic info globally. Logo upload uses deterministic naming, excludes SVG.
