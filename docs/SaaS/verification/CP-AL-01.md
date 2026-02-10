# CP-AL-01 Control Plane Activity Export

## Scope
- Add CSV export for control plane activity log
- Support user name/email filters
- Wire export button in UI

## Evidence

### 1) Routes
```
php spark routes | findstr /I "controlplane/activity-log"
```

### 2) PHP Syntax
```
php -l app/Controllers/ControlPlane/ActivityLog.php
```

### 3) UI Wiring
```
rg -n "controlplane/activity-log/export|exportActivities|user:" app/Views/control_plane/activity_log.php
```

