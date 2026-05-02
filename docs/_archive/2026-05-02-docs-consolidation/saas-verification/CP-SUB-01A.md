# CP-SUB-01A Verification

## Scope
Normalize control plane subscription reads and metrics to honor start_at/end_at standing and expired transitions.

## Evidence

### Normalized subscription reads + standing-aware metrics
Command:
`rg -n "getNormalizedSubscription|start_at IS NULL|end_at IS NULL" app\\Controllers\\ControlPlane app\\Models\\ClinicSubscriptionModel.php`

Output:
```
app\\Models\\ClinicSubscriptionModel.php:48:    public function getNormalizedSubscription(int $clinicId): ?array
app\\Controllers\\ControlPlane\\Dashboard.php:48:                ->where('start_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Dashboard.php:52:                ->where('end_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Dashboard.php:60:                ->where('start_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Dashboard.php:64:                ->where('end_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Dashboard.php:76:                ->where('start_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Clinics.php:43:            $subscription = $subscriptionModel->getNormalizedSubscription((int) $clinic['id']);
app\\Controllers\\ControlPlane\\Clinics.php:87:        $subscription = $subscriptionModel->getNormalizedSubscription((int) $clinic['id']);
app\\Controllers\\ControlPlane\\Clinics.php:125:        $current = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:162:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:190:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:218:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:267:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:301:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\Clinics.php:334:        $subscription = $subscriptionModel->getNormalizedSubscription($id);
app\\Controllers\\ControlPlane\\PlanRequests.php:78:        $current = $subscriptionModel->getNormalizedSubscription($clinicId);
app\\Controllers\\ControlPlane\\PlanRequests.php:110:        $current = $subscriptionModel->getNormalizedSubscription((int) $request['clinic_id']);
app\\Controllers\\ControlPlane\\Operations.php:46:                ->where('start_at IS NULL', null, false)
app\\Controllers\\ControlPlane\\Operations.php:50:                ->where('end_at IS NULL', null, false)
```
