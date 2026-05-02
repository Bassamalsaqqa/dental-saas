# CP-DASH-01 Verification

## Scope
- Prevent control plane dashboard and plan-request surfaces from failing tenant scope checks.
- Ensure PlanRequestModel queries run without tenant scoping in global mode.

## Evidence

### Control plane PlanRequestModel usage
```bat
rg -n "PlanRequestModel" app/Controllers/ControlPlane
```
```
app/Controllers/ControlPlane\Dashboard.php:9:use App\Models\PlanRequestModel;
app/Controllers/ControlPlane\Dashboard.php:107:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
app/Controllers/ControlPlane\Plans.php:8:use App\Models\PlanRequestModel;
app/Controllers/ControlPlane\Plans.php:31:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
app/Controllers/ControlPlane\PlanRequests.php:9:use App\Models\PlanRequestModel;
app/Controllers/ControlPlane\PlanRequests.php:30:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
app/Controllers/ControlPlane\PlanRequests.php:72:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
app/Controllers/ControlPlane\PlanRequests.php:103:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
app/Controllers/ControlPlane\PlanRequests.php:151:        $planRequestModel = (new PlanRequestModel())->withoutTenantScope();
```
