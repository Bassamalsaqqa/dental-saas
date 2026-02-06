# P5-ATT-01 Verification

## Summary
- Added patient attachments tab with upload/list/delete.
- Enforced plan feature gate and per-file size limit.
- Stored metadata (category/notes) and added storage cap warning.

## Commands & Evidence

### PHP Lint
```bash
php -l app/Controllers/Patient.php
```
Output:
```
No syntax errors detected in app/Controllers/Patient.php
```

```bash
php -l app/Services/StorageService.php
```
Output:
```
No syntax errors detected in app/Services/StorageService.php
```

```bash
php -l app/Models/FileAttachmentModel.php
```
Output:
```
No syntax errors detected in app/Models/FileAttachmentModel.php
```

```bash
php -l app/Views/patient/show.php
```
Output:
```
No syntax errors detected in app/Views/patient/show.php
```

```bash
php -l app/Database/Migrations/2026-02-05-120000_AddAttachmentMetadataToFileAttachments.php
```
Output:
```
No syntax errors detected in app/Database/Migrations/2026-02-05-120000_AddAttachmentMetadataToFileAttachments.php
```

### Routes
```bash
php spark routes | findstr /i "attachments"
```
Output:
```
| POST   | patient/([0-9]+)/attachments/upload                     | ?    | \App\Controllers\Patient::uploadAttachment/$1                     | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| POST   | patient/([0-9]+)/attachments/([0-9]+)/delete            | ?    | \App\Controllers\Patient::deleteAttachment/$1/$2                  | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| POST   | patients/([0-9]+)/attachments/upload                    | ?    | \App\Controllers\Patient::uploadAttachment/$1                     | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| POST   | patients/([0-9]+)/attachments/([0-9]+)/delete           | ?    | \App\Controllers\Patient::deleteAttachment/$1/$2                  | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
```

## Manual Checks
1. Open a patient profile and confirm “Attachments” tab appears when the plan enables `attachments.enabled`.
2. Upload a PDF/JPG/DOCX file under the max size; confirm it appears in the list.
3. Upload a file > max size; confirm upload is blocked with an error.
4. Download and delete actions should work and remain scoped to the active clinic.
5. When storage usage exceeds 80% of `storage_gb`, a warning banner appears.
