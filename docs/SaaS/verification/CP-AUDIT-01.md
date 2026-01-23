# CP-AUDIT-01 Verification

## Scope
- Add control plane activity feed alongside plan enforcement audits in Operations.

## Evidence

### Controller wiring
```bat
rg -n "ControlPlaneAuditModel|controlPlaneAudits|planAudits" app/Controllers/ControlPlane/Operations.php app/Views/control_plane/operations.php
```
```
app/Views/control_plane/operations.php:106:                    <?= count($controlPlaneAudits ?? []) ?> records
app/Views/control_plane/operations.php:109:            <?php if (empty($controlPlaneAudits)): ?>
app/Views/control_plane/operations.php:129:                            <?php foreach ($controlPlaneAudits as $audit): ?>
app/Views/control_plane/operations.php:197:                <?= count($planAudits ?? []) ?> records
app/Views/control_plane/operations.php:200:        <?php if (empty($planAudits)): ?>
app/Views/control_plane/operations.php:220:                        <?php foreach ($planAudits as $audit): ?>
app/Controllers/ControlPlane/Operations.php:9:use App\Models\ControlPlaneAuditModel;
app/Controllers/ControlPlane/Operations.php:34:        $controlPlaneAuditModel = new ControlPlaneAuditModel();
app/Controllers/ControlPlane/Operations.php:43:        $controlPlaneAudits = $controlPlaneAuditModel->orderBy('id', 'DESC')->findAll(10);
app/Controllers/ControlPlane/Operations.php:44:        $planAudits = $planAuditModel->orderBy('id', 'DESC')->findAll(10);
app/Controllers/ControlPlane/Operations.php:53:            'controlPlaneAudits' => $controlPlaneAudits,
app/Controllers/ControlPlane/Operations.php:54:            'planAudits' => $planAudits,
```
