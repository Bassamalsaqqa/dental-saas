# CP-PLAN-01 Verification

## Scope
- Enforce plan seat and storage limits via PlanGuard.
- Apply storage checks on uploads and export storage.
- Apply user seat checks on tenant user creation.

## Evidence

### Plan limit wiring (users_max, storage_gb)
```bat
rg -n "users_max|storage_gb" app/Services/PlanGuard.php app/Services/StorageService.php app/Controllers/UserManagementController.php app/Models/ClinicUserModel.php app/Models/FileAttachmentModel.php
```
```
app/Services/PlanGuard.php:207:        if ($canonicalKey === 'users_max') {
app/Services/PlanGuard.php:223:        if ($canonicalKey === 'storage_gb') {
app/Controllers/UserManagementController.php:140:            $planGuard->assertQuota((int) $clinicId, 'users_max', 1, ['action' => 'user_create']);
app/Services/StorageService.php:32:        $this->planGuard->assertQuota($clinicId, 'storage_gb', (int) $file->getSize(), [
app/Services/StorageService.php:72:        $this->planGuard->assertQuota($clinicId, 'storage_gb', (int) strlen($content), [
```

### Model helpers for live counts
```bat
rg -n "countActiveByClinic|getTotalSizeByClinic" app/Models/ClinicUserModel.php app/Models/FileAttachmentModel.php
```
```
app/Models/ClinicUserModel.php:54:    public function countActiveByClinic(int $clinicId): int
app/Models/FileAttachmentModel.php:60:    public function getTotalSizeByClinic(int $clinicId): int
```

## Notes
- Plan limits remain stored in `limits_json` (price, duration, trial, patients_active_max, users_max, storage_gb, exports, notifications_email).
- No schema changes required for CP-PLAN-01.
