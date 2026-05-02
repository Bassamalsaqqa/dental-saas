# Architectural Decision Log

Append-only. Do not edit previous entries.

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
