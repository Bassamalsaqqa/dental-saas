# CP-01 Control Plane & Entitlements Contract

Status: Active
Scope: Web + API v1 control-plane, subscription standing, feature entitlements, and quota enforcement.
Authority: This contract binds implementation. If code or other docs diverge, code/docs are wrong.

Sources (evidence-backed):
- docs/API/CONTRACT_V1.md
- docs/API/README.md
- docs/SaaS/SAAS_PROGRAM.md
- app/Services/PlanGuard.php
- app/Filters/ApiV1AuthFilter.php
- app/Helpers/api_helper.php

Non-negotiables
- Web concealment: entitlement failures return HTTP 404 (PageNotFoundException).
- API v1 must not conceal: entitlement failures return JSON with explicit error codes.
- API v1 is stateless: no session reads in v1 execution paths. Actor attribution uses ApiRuntimeContext.
- Control-plane airlock: when global_mode is active, tenant routes fail closed; control-plane routes require global_mode.

## 1) Subscription Lifecycle State Machine

Authoritative fields: clinic_subscriptions.status, start_at, end_at.
Trial is date-based only: no trialing status.
end_at is exclusive.

State machine (subscription standing)
| State | Definition | Valid for access | Allowed transitions | Trigger |
| --- | --- | --- | --- | --- |
| active | status=active AND (start_at is null OR now >= start_at) AND (end_at is null OR now < end_at) | YES | active -> paused | Admin pause |
| active | same as above | YES | active -> canceled | Admin cancel |
| active | same as above | YES | active -> expired | end_at reached |
| paused | status=paused | NO | paused -> active | Admin resume |
| paused | status=paused | NO | paused -> canceled | Admin cancel |
| paused | status=paused | NO | paused -> expired | end_at reached |
| canceled | status=canceled | NO | canceled -> active | Admin reactivation (manual) |
| expired | status=expired OR status=active with end_at <= now | NO | expired -> active | Admin reactivation (manual) |

Access rule (binding): access is valid only when the subscription is active AND within the start/end window.

## 2) Entitlement Evaluation Algorithm (Binding)

Evaluation order is strict; all failures are fail-closed.

| Step | Surface | Check | Data source | Result on failure |
| --- | --- | --- | --- | --- |
| 1 | API v1 | Bearer token present + valid | api_tokens.token_hash | 401 unauthenticated |
| 2 | API v1 | X-Clinic-Id header present + integer | request header | 422 tenant_context_missing/invalid |
| 3 | API v1 | Clinic exists and active | clinics | 422 tenant_context_invalid / 403 tenant_context_forbidden |
| 4 | API v1 | Tenant membership active | clinic_users | 403 tenant_context_forbidden |
| 5 | API v1 | Subscription standing valid | clinic_subscriptions + PlanGuard | 403 subscription_inactive |
| 6 | API v1 | Permission check | PermissionFilter + PermissionService | 403 forbidden |
| 7 | API v1 | Feature gate (if used) | plans.features_json + PlanGuard | 403 feature_disabled |
| 8 | API v1 | Quota gate (if used) | plans.limits_json + PlanGuard | 429 plan_quota_exceeded |
| 1 | Web | Auth + tenant filters | session + filters | redirect or 404 per plane |
| 2 | Web | Subscription standing | SubscriptionFilter + PlanGuard | 404 |
| 3 | Web | Permission check | PermissionFilter + PermissionService | 404 |
| 4 | Web | Feature/Quota gate | PlanGuard | 404 |

Caching rules
- No cache of entitlement decisions is permitted in API v1.
- Web may cache read-only pages but cannot cache entitlement outcomes.

## 3) Quota Definition Table (Binding)

Quota enforcement is active only where PlanGuard::assertQuota is called.
If a quota is not used by an endpoint family, it is considered UNENFORCED.

| Metric key | Unit | Scope | Enforcement points | Response contract |
| --- | --- | --- | --- | --- |
| patients_active_max | active patients count | per clinic | Web patient creation | Web: 404 (concealment). API v1: 429 plan_quota_exceeded when used. |
| <other metrics> | n/a | n/a | None currently | Unenforced. Must not be advertised as enforced. |

## 4) API Error Semantics Truth Table (Binding)

All API v1 errors are JSON from api_error().

| Condition | HTTP status | error.code | message rule | details |
| --- | --- | --- | --- | --- |
| Missing/invalid Bearer | 401 | unauthenticated | fixed string | no details |
| Missing X-Clinic-Id | 422 | tenant_context_missing | fixed string | no details |
| Malformed X-Clinic-Id | 422 | tenant_context_invalid | fixed string | no details |
| Clinic not found | 422 | tenant_context_invalid | fixed string | no details |
| Not a clinic member | 403 | tenant_context_forbidden | fixed string | no details |
| Clinic inactive | 403 | tenant_context_forbidden | fixed string | no details |
| Subscription invalid | 403 | subscription_inactive | fixed string | no details |
| Permission denied | 403 | forbidden | fixed string | no details |
| Feature disabled | 403 | feature_disabled | fixed string | optional details (feature key) |
| Quota exceeded | 429 | plan_quota_exceeded | fixed string | optional details (metric/limit/usage) |

Error JSON shape (binding):
```json
{
  "error": {
    "code": "unauthenticated",
    "message": "Authentication required.",
    "details": {}
  }
}
```

## 5) Audit Actor Attribution Rules (Binding)

| Surface | actor_user_id | actor_token_id | clinic_id | rule |
| --- | --- | --- | --- | --- |
| API v1 | required (ApiRuntimeContext user_id) | optional | required | Must not read session in v1 |
| Web | required (session user_id) | null | required | Session required |
| Jobs/CLI | 0 only with reason_code=system | null | required in payload | No session reliance |

No session reads in v1 rule (binding)
- ApiRuntimeContext is the sole source of actor identity in v1.
- Any session() usage in a v1 execution path is a contract violation.

## 6) Control Plane Contract (Binding)

Control plane invariants
- Entry route is unfiltered; all protected control-plane routes require global_mode.
- Enter action must set global_mode=true, purge tenant keys, and regenerate session.
- Tenant routes must fail closed when global_mode=true.

Routing evidence (requirements)
- Control-plane routes are grouped under controlplane filter.
- API v1 routes are grouped under apiv1 filter + permission:*.

## 7) Evidence (verbatim)

Filter chain (v1)
```
php spark filter:check GET api/v1/patients
... Before Filters: apiv1 permission:patients
```

Session usage scan
```
rg -n "session\\(" app/Services/PlanGuard.php
49:            $session = session();
```

Risk note
- The session() call exists in PlanGuard; it must not execute in API v1 paths.
- ApiRuntimeContext is set before PlanGuard in ApiV1AuthFilter to avoid session usage in v1.
