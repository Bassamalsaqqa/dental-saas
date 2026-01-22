# SaaS v1 Reset - Execution Plan (Approval-Grade)

Objective
Make `/api/v1/*` the authoritative product surface (stateless) while freezing, observing, gating, and removing legacy session-based APIs (`/api/search/*`) without destabilizing tenant UI.

This document is execution-grade: tasks include DoD, verification commands, and rollback notes.


Current status (2026-01-21)
- Legacy UI calls migrated to `/api/v1/search/*` (no `/api/search/*` usage in app).
- Legacy routes removed (`/api/search/*` returns 404; no routes registered).
- Legacy observe logging was implemented before removal; gate phase is now skipped as obsolete.


---

## 0) Current-State Truth (Evidence Anchors)

Surfaces
- Tenant Web UI (session)
  - Filters: csrf forcehttps auth tenant subscription permission:*
  - Evidence: `php spark routes` + `php spark filter:check GET dashboard`
- Control Plane (session + global_mode)
  - Filters: csrf forcehttps controlplane
  - Evidence: `php spark routes` + `php spark filter:check GET controlplane/dashboard`
- API v1 (stateless)
  - Filters: apiv1 + permission:* only
  - Evidence: `php spark filter:check GET api/v1/patients` and `GET api/v1/search/patients`
- Legacy API (/api/search/*, session)
  - Filters: auth tenant subscription permission:* with csrfjson/toolbar/performance after-filters
  - Evidence: `php spark filter:check GET api/search/patients`

Contracts
- v1: `docs/API/CONTRACT_V1.md`
- legacy: `docs/API/CONTRACT_LEGACY.md`
- control plane + entitlements: `docs/SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md`

---

## 1) Forced Decisions (Locked Policy)

### 1.1 Trial and Grace (Model C: date-based, no trial status)
- `clinic_subscriptions.status` remains: active | paused | canceled | expired
- Trial is purely `start_at/end_at` (no `trialing` status)
- Grace is a fixed integer window `grace_days` (default 0) enforced in code/config (schema change optional later)

Validity rule (locked):
- Subscription is valid only if:
  - status == active AND
  - start_at is NULL OR now >= start_at AND
  - end_at is NULL OR now < end_at (end_at is exclusive) OR (grace_days > 0 AND now < end_at + grace_days)

API behavior:
- invalid standing -> 403 `subscription_inactive` JSON (pre-controller in ApiV1AuthFilter)

Web behavior:
- concealment remains (404) for entitlement failures; UI may show banner only when explicitly in-UX.

### 1.2 Concealment Policy (Web vs API)
- Web UI: MAY conceal (404 PageNotFoundException) for entitlement and cross-plane failures.
- API v1: MUST NOT conceal (no 404) for auth/tenant/subscription/quota/feature failures.

### 1.3 ActorContext (Audit Attribution)
- No silent actor_user_id=0 without a reason_code.
- Actor precedence (locked):
  - API v1: ApiRuntimeContext user_id + token_id
  - Web: session user_id
  - Jobs/CLI: system actor (actor_type=system)

Required fields (minimum):
- actor_user_id
- actor_type: session | bearer | system
- actor_token_id (nullable)
- clinic_id (nullable)
- route, method
- ip, ua
- request_id (preferred)

### 1.4 Control Plane Lifecycle (Routes + Invariants)
Routes (evidence-backed):
- GET  /controlplane                      (Entry)
- POST /controlplane/enter                (Enter global mode)
- POST /controlplane/danger/exit          (Exit global mode)
- (If implemented) POST /controlplane/exit (Exit global mode)

Invariants (locked):
- CP routes require global_mode=true (except Entry and Enter).
- Enter MUST:
  - set global_mode=true
  - purge tenant keys (active_clinic_id, impersonated_clinic_id, etc.)
  - regenerate session if feasible
- Tenant routes MUST fail closed when global_mode=true.

---

## 2) Execution Roadmap (Non-Destructive)

### Phase 0: Documentation + Contracts
Task: DOC-API-00
DoD
- `docs/API/README.md`, `docs/API/CONTRACT_V1.md`, `docs/API/CONTRACT_LEGACY.md` updated to reflect locked policy above.
- `docs/API/CONTRACT_V1.md` and `docs/API/CONTRACT_LEGACY.md` aligned to locked policy.

Verify (verbatim)
- `rg -n "Trial and Grace|ActorContext|Control Plane Lifecycle" docs/API`

Rollback
- Revert docs-only commit.

---

### Phase 1: SEC-CACHE-01 Remove PageCache from authenticated routes
DoD
- `pagecache` absent from:
  - dashboard
  - controlplane/dashboard
  - patients

Allowed files
- `app/Config/Filters.php`

Verify (verbatim)
- `php spark filter:check GET dashboard`
- `php spark filter:check GET controlplane/dashboard`
- `php spark filter:check GET patients`

Rollback
- Re-add `pagecache` to prior filter lists.

---

### Phase 2: API-SEM-01 Resolve API semantics drift (no 404 for plan/quota/feature)
DoD
- API v1 returns JSON with correct status codes:
  - 401 unauthenticated
  - 422 tenant_context_missing/invalid
  - 403 tenant_context_forbidden/subscription_inactive/forbidden
  - 429 plan_quota_exceeded
  - 403 feature_disabled
- No redirects; no HTML; no 404 concealment for these failures.

Verify (verbatim)
- `php spark filter:check GET api/v1/patients`
- `curl.exe -i http://saas.dental.local/api/v1/patients`
- `curl.exe -i -H "Authorization: Bearer <TOKEN>" http://saas.dental.local/api/v1/patients`
- `curl.exe -i -H "Authorization: Bearer <TOKEN>" -H "X-Clinic-Id: <id>" http://saas.dental.local/api/v1/patients`

Rollback
- Revert API error mapping changes.

---

### Phase 3: ACTOR-CTX-01 ActorContext enforcement for audits
DoD
- Plan/subscription audit writers can attribute actor correctly in API context (bearer) and web (session).
- actor_user_id=0 allowed only if actor_type=system with reason_code.

Verify (verbatim)
- `rg -n "ActorContext|actor_type|request_id" app docs`
- Review evidence logs for audit rows containing token-based actor attribution.

Rollback
- Revert audit attribution changes and keep docs.

---

### Phase 4: API-LEGACY-OBSERVE-01 Legacy observability + freeze
DoD
- `/api/search/*` emits structured access logs/events.
- Observe-only mode exists; deny mode exists (next phase).

Status (2026-01-21)
- COMPLETE. Observe logging implemented before legacy removal.

Verify (verbatim)
- `php spark filter:check GET api/search/patients`
- `curl.exe -i http://saas.dental.local/api/search/patients`
- Log pattern: `LEGACY_API_ACCESS route=/api/search/patients method=GET user_id=<id|none> clinic_id=<id|none> ip=<ip> ua="<ua>"`

Rollback
- Disable logging hook.

---

### Phase 5: API-LEGACY-GATE-01 Gate legacy routes
DoD
- Config flag controls observe vs deny.
- Default deny in dev/test; observe in prod.

Status (2026-01-21)
- SKIPPED. Legacy routes were removed after UI migration; gate phase is no longer applicable.

Verify (verbatim)
- `curl.exe -i http://saas.dental.local/api/search/patients`
- `php spark filter:check GET api/search/patients`

Rollback
- Flip flag back to observe-only.

---

### Phase 6: UI-V1-MIGRATE-01 Migrate UI to v1
DoD
- UI no longer calls `/api/search/*`.

Status (2026-01-21)
- COMPLETE. No `/api/search/*` usage remains in app or assets.

Verify (verbatim)
- `rg -n "api/search" app`

Rollback
- Revert UI changes.

---

### Phase 7: API-LEGACY-REMOVE-01 Remove legacy routes
DoD
- `/api/search/*` removed only after telemetry shows no reliance.

Status (2026-01-21)
- COMPLETE. Routes removed; `/api/search/*` returns 404; `php spark routes` shows no legacy endpoints.

Verify (verbatim)
- `php spark routes | findstr /I "api/search"`

Rollback
- Restore routes from prior revision.

---
