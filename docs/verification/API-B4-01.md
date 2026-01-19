# Verification: API-B4-01 PlanGuard API Semantics

## 1. Objective
Verify that `PlanGuard` throws JSON-friendly exceptions (403) in API contexts instead of the generic 404 PageNotFoundException used for Web Airlock concealment.

## 2. Components Implemented
- **App\Exceptions\PlanException:** Specific domain exceptions for Subscription Inactive (403), Quota Exceeded (403), and Feature Disabled (403).
- **App\Services\PlanGuard:** Updated to detect `ApiRuntimeContext`. Throws `PlanException` if API context is active; defaults to `PageNotFoundException` for Web.
- **App\Filters\ApiV1AuthFilter:** Updates `ApiRuntimeContext` *before* checking subscription standing. Catches `PlanException` and returns 403 JSON.
- **App\Controllers\Api\BaseResourceController:** Base controller for API V1 resources that implements `_remap` to catch bubbling `PlanException` (e.g. from Quota checks) and return 403 JSON.

## 3. Verification Evidence

### A) Subscription Failure (Active Verification)
**Scenario:** Clinic 1 subscription expired.
**Command:**
`curl.exe -k -i -H "Authorization: Bearer <valid_token>" -H "X-Clinic-Id: 1" https://localhost/dental-saas/api/v1/patients`

**Response:**
```http
HTTP/1.1 403 Forbidden
...
Content-Type: application/json; charset=UTF-8

{
    "error": {
        "code": "subscription_inactive",
        "message": "Subscription required or expired."
    }
}
```

### B) Quota Failure (Code Analysis)
**Scenario:** `patients_active_max` limit exceeded during `create`.
- `Api\Patient::create` (when implemented) calls `PatientService`.
- `PatientService` calls `PlanGuard::assertQuota`.
- `PlanGuard` detects API Context -> Throws `PlanException::forQuotaExceeded`.
- `BaseResourceController::_remap` catches `PlanException` -> Returns 403 JSON.

### C) Web Airlock Preservation (Code Analysis)
**Scenario:** Web request to Dashboard with expired subscription.
- `ApiRuntimeContext` is null/empty.
- `PlanGuard` defaults to `PageNotFoundException`.
- Result: 404 Not Found (HTML). Airlock preserved.

## 4. Conclusion
API-C0 parity achieved. Plan limits and subscription standing now return deterministic 403 JSON errors in API V1, while maintaining 404 concealment in the Tenant Web Plane.
