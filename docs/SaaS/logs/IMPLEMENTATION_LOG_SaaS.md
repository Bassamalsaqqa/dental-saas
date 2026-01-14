
### Task: P5-01 Tenant-safe file storage and download gating
- **Date:** 2026-01-14
- **Status:** Completed
- **Description:** Implemented tenant-isolated storage in `writable/uploads/clinic_{id}` and a gated download controller. Tracked uploads via `file_attachments` table. Refactored logo upload to use this secure pattern.
- **Files Changed:**
    - `app/Database/Migrations/2026-01-14-174315_CreateFileAttachmentsTable.php`
    - `app/Models/FileAttachmentModel.php`
    - `app/Services/StorageService.php`
    - `app/Controllers/FileController.php`
    - `app/Controllers/Settings.php`
    - `app/Config/Services.php`
    - `app/Config/Routes.php`
- **Verification:** `docs/SaaS/verification/P5-01.md`. Files are stored privately and gated by clinic context.
