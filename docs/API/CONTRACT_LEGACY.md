# API Contract (Legacy Session-Based)

Status: Deprecated (Frozen)
Scope: `/api/search/*`
Policy: No new features, no behavior changes except observability and gating.

This contract exists to document legacy behavior until removal.

---

## 1) Authentication and Tenant Context
- Session-based via IonAuth.
- Tenant context via session `active_clinic_id`.
- Filters typically include: `auth tenant subscription permission:*`
- CSRF applies (legacy pipeline).
- May emit `ci_session_saas` cookies.

---

## 2) Error Semantics
- Behavior is governed by web/session filters and controllers.
- Responses may vary by controller; stability is NOT guaranteed.
- This is one reason the surface is deprecated.

---

## 3) Deprecation and Removal Policy (Locked Order)
1) Freeze: doc-only; no feature work.
2) Observe: structured access logs/events for every call.
3) Gate: config flag (observe-only vs deny-with-log); default deny in dev/test.
4) Migrate: UI moves to `/api/v1/*`.
5) Deny: legacy denied by default.
6) Remove: delete routes after telemetry shows zero reliance.

Reference: `docs/API/README.md`