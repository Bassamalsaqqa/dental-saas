# Clinical File Access & Authorization Contract (2026-10-16)

## 1. Executive Summary

- **Task ID:** `task_58394c77442642449f444d484b3018d3`
- **Component:** `app/Controllers/FileController.php` (routes: `file/download/(:num)` and `file/inline/(:num)`)
- **Status:** Candidate passed the 17-test authorization baseline and subsequent focused directory-alias certification (4 tests, 57 assertions, no skips). Source integration and release approval remain pending.
- **Scope:** Tenant Plane file delivery only. No schema changes, no migrations, no dependency upgrades.
- **Test Execution Status:** Codex independently verified the authorization baseline: **17 tests, 96 assertions**, PHP 8.2.12 / PHPUnit 11.5.55, on 2026-10-16 at 19:43:28-19:43:33 UTC. After the additional alias correction, only four affected tests were run on 2026-10-17 at 04:24:37-04:24:49 UTC: EN/AR normal delivery, existent-file confinement, and an actual Windows directory junction between two newly created fixture clinics. Result: **4 tests, 57 assertions, no skips**. Both runs used only reserved synthetic `127.0.0.1:3307` (`democa_dental_saas_test`), with unchanged physical ownership before and after and complete preservation of all 70 table schema/data/count maps. Scheduling remained OFF. The complete 18-method suite was not rerun after the alias correction.
- **Final Tested File SHA256 after PR #7 cache correction:** `app/Controllers/FileController.php`: `01edb5fa505e98de53635bc6c70802dbfb7670639e3eb26241b2e5f070c3b38f`; `tests/unit/ClinicalFileAuthorizationTest.php`: `ca772ffa59ab82976086b8661490046bf742a688218eb7b483cb5f6e273fcfd7`.
- **Independent PR #7 Feedback Correction:** Review `5556622145` requested explicit no-store on clinical inline delivery. The controller now calls its framework `noCache()` before authorization: successful inline responses and controller-generated permission/MIME 403 responses emit `Cache-Control: no-store, max-age=0, no-cache`. Existing download responses retain their own framework no-store policy. Four focused EN/AR, denied-permission and forged-MIME tests passed **4 tests / 47 assertions / no skips** on 2026-10-17 at 06:42:50-06:42:58 UTC. All 70 schema/data/count maps, migration history, scheduling flags and identity were preserved. Evidence: `denta-clinical-f039468e8f4d413f9f14bc8ecc7e3ac5`. Earlier filters and framework-created exceptions are outside this controller response policy; this correction does not claim global error-cache certification. AGY's bounded correction attempt timed out without edits; Codex completed the minimal three-line controller change and independently executed the focused tests.

---

## 2. Review Findings & Implemented Candidate Corrections

Following independent review, schema evidence from `synthetic-schema-metadata.json` and `hostinger-schema-preservation-before.json`, and subsequent runtime hardening, the following corrections were implemented:

### 2.1. Canonical Export Permissions Enforcement
- **Finance Exports (`entity_id = 0`):** Purposes `finance_export`, `finance_ledger_export`, and `invoice_print` strictly require canonical permission `finance:export`. No view shortcut or fallback (`finance:view` alone is denied with HTTP 403 Forbidden).
- **Report Exports (`entity_id = 0`):** Allowed report purposes `report_html`, `report_excel`, `report_csv`, and `background_report` strictly require `reports:export`. No view shortcut (`reports:view` alone is denied with HTTP 403 Forbidden).
- **Strict Report Object ID & Purpose Validation:** Any non-zero `entity_id` on reports or unlisted/empty purpose fails closed with HTTP 404 `PageNotFoundException`.
- **Entity Module Permissions:** Each clinical module enforces its own canonical module view permission via `ENTITY_MAP`:
  - `patient` -> `patients:view`
  - `examination` -> `examinations:view`
  - `appointment` -> `appointments:view`
  - `treatment` -> `treatments:view`
  - `prescription` -> `prescriptions:view`
  - `contract` -> `patients:view`
  - `finance` (record, `entity_id > 0`) -> `finance:view`
  - `inventory_usage` / `inventory` -> `inventory:view`

### 2.2. Clinic Logo Reference Policy
- **Proof of Active Setting Reference:** For attachments with `entity_type = 'logo'`, the controller verifies that the attachment ID matches the current clinic's active setting `setting_key = 'clinic_logo_path'` in the `settings` table (matching `'file/download/{id}'`, `'file/inline/{id}'`, or `'{id}'`).
- **Referenced Logos Allowed:** Ordinary active tenant users can view and download the active branding logo (`200 OK`).
- **Orphan Logos Fail Closed:** Any logo attachment not actively referenced in the clinic's settings fails closed with HTTP 404 `PageNotFoundException`.

### 2.3. Schema-Verified Soft-Delete & Linkage Integrity
- **Verified Soft-Delete Columns:** Grounded by actual schema metadata (`synthetic-schema-metadata.json` and `hostinger-schema-preservation-before.json`), `deleted_at` exists on these seven protected clinical/financial entity tables:
  1. `patients`
  2. `appointments`
  3. `examinations`
  4. `treatments`
  5. `prescriptions`
  6. `treatment_contracts`
  7. `finances`
- **Controller Enforcement:**
  - `ENTITY_MAP` configures `soft_delete => true` (`deleted_at IS NULL`) for `patient`, `examination`, `appointment`, `treatment`, `prescription`, and `contract`.
  - `finances` record query (`$entityId > 0`) explicitly requires `deleted_at IS NULL`.
  - `patientExistsInClinic()` helper explicitly requires `deleted_at IS NULL`.
  - `inventory_usage` verified to lack a `deleted_at` column; configured with `soft_delete => false` (no queries against non-existent `deleted_at`).
- **Cross-Clinic & Soft-Deleted Patient Linkage:** Any clinical entity record containing a `patient_id` must have a corresponding active patient in the same clinic (`clinic_id = active_clinic_id AND deleted_at IS NULL`). Cross-clinic or soft-deleted parent patient linkages fail closed with HTTP 404 `PageNotFoundException`.

### 2.4. Detected MIME Inspection & Active Upload Hardening
- **Physical MIME Verification (`finfo_file`):** Database MIME metadata alone is never trusted. The physical file is inspected using PHP `finfo_file`.
- **Active Upload / Forged MIME Rejection:** Files with detected active types (HTML, SVG, JavaScript, PHP, XML, shell scripts, executables) or MIME mismatches are rejected on `/file/inline/{id}` with HTTP 403 Forbidden.
- **Content Security Policy:** Previewable files served via `inline` include:
  - `Content-Security-Policy: default-src 'none'; base-uri 'none'; sandbox`
  - `X-Content-Type-Options: nosniff`
- **Safe Downloads:** Downloads of active or untrusted types are forced to `Content-Type: application/octet-stream` with `X-Content-Type-Options: nosniff`.

### 2.5. Treatment Contracts Generated HTML Artifact Disclosure
- **Observed Writer Behavior:** The current contract writer (`TreatmentContracts::print`) persists generated HTML views (`view('contracts/print')`, MIME `text/html`, purpose `contract_pdf`).
- **Controller Delivery:** Under the hardened `FileController`, download is available safely as `application/octet-stream` with `nosniff`, but inline preview is rejected with HTTP 403 Forbidden.
- **Workflow Consideration:** This download-available / inline-preview-denied posture is intentional fail-closed security behavior to prevent active HTML execution / stored XSS in the browser. Transitioning contract generation from HTML string persistence to direct binary PDF rendering (via Dompdf, print CSS, or headless Chrome) is an open workflow recommendation requiring product review.

### 2.6. Filesystem Confinement & Clinic-Directory Alias Rejection
- **Clinic-Specific Directory Alias Rejection:** The controller rejects native symlinks with `is_link($rawClinicDir)` and additionally requires the resolved clinic directory to equal `realpath(trusted uploads root)/clinic_N`. The comparison respects Windows case-insensitivity and POSIX case-sensitivity. The latter identity check is essential: PHP's `is_link()` did not detect the independently created Windows junction. A trusted root uploads directory may be shared or aliased, while `clinic_N` itself must not redirect to another clinic. Rejected aliases return HTTP 404.
- **Per-File Confinement:** The controller strictly enforces that the real canonical path of any accessed file resides within `$tenantUploadDir . DIRECTORY_SEPARATOR`. Paths resolving outside the tenant directory log a critical security event and fail closed with HTTP 404 `PageNotFoundException`.

---

## 3. Entity Authorization Matrix

| Entity Type (`entity_type`) | Object Scope | Required Canonical Permission | Soft Delete Check | Object Ownership & Linkage Validation |
|---|---|---|---|---|
| `patient` | `entity_id > 0` | `patients:view` | `deleted_at IS NULL` | Must exist in `patients` table where `clinic_id = active_clinic_id`. |
| `examination` | `entity_id > 0` | `examinations:view` | `deleted_at IS NULL` | Must exist in `examinations` where `clinic_id = active_clinic_id`. Related `patient_id` must exist in same clinic with `deleted_at IS NULL`. |
| `appointment` | `entity_id > 0` | `appointments:view` | `deleted_at IS NULL` | Must exist in `appointments` where `clinic_id = active_clinic_id`. Related `patient_id` must exist in same clinic with `deleted_at IS NULL`. |
| `treatment` | `entity_id > 0` | `treatments:view` | `deleted_at IS NULL` | Must exist in `treatments` where `clinic_id = active_clinic_id`. Related `patient_id` must exist in same clinic with `deleted_at IS NULL`. |
| `prescription` | `entity_id > 0` | `prescriptions:view` | `deleted_at IS NULL` | Must exist in `prescriptions` where `clinic_id = active_clinic_id`. Related `patient_id` must exist in same clinic with `deleted_at IS NULL`. |
| `contract` | `entity_id > 0` | `patients:view` | `deleted_at IS NULL` | Must exist in `treatment_contracts` where `clinic_id = active_clinic_id`. Related `patient_id` must exist in same clinic with `deleted_at IS NULL`. |
| `finance` (Record) | `entity_id > 0` | `finance:view` | `deleted_at IS NULL` | Must exist in `finances` where `clinic_id = active_clinic_id`. If `patient_id` present, must exist in same clinic with `deleted_at IS NULL`. |
| `finance` (Export) | `entity_id === 0` | `finance:export` | N/A | Purpose must be in `['finance_export', 'finance_ledger_export', 'invoice_print']`. |
| `inventory_usage` / `inventory` | `entity_id > 0` | `inventory:view` | N/A (no column) | Must exist in `inventory_usage` where `clinic_id = active_clinic_id`. |
| `report` | `entity_id === 0` | `reports:export` | N/A | Purpose must be in `['report_html', 'report_excel', 'report_csv', 'background_report']`. |
| `logo` | Scoped to clinic | Active membership | N/A | Attachment ID must match active clinic setting `clinic_logo_path` (`file/download/{id}`, `file/inline/{id}`, or `{id}`). Orphan logo fails 404. |
| *Other / Unknown* | Any | None | N/A | Fail closed with HTTP 404 `PageNotFoundException`. |

---

## 4. Test Suite Decoupling & Isolation Guard

The routed integration test suite in `tests/unit/ClinicalFileAuthorizationTest.php` contains **18 focused test methods** (17 previously certified + 1 newly added regression):

1. **Pre-Connect Isolation Pre-Check:**
   - Validates `Config\Database` `tests` group configuration **BEFORE** invoking `Database::connect`:
     - `hostname === '127.0.0.1'` (strictly rejects `localhost`)
     - `port === 3307`
     - `database === 'democa_dental_saas_test'`
     - `DBDriver === 'MySQLi'`
     - `database !== default['database']` (non-business database)
     - `defaultGroup === 'tests'`
   - Retains SQL `SELECT DATABASE()` proof before fixture writes.
   - Root adds physical instance ownership proof externally prior to running tests.

2. **Schema-Accurate Fixtures & Elimination of Hardcoded IDs:**
   - Grounded in `synthetic-schema-metadata.json` and `hostinger-schema-preservation-before.json`:
     - `users`: `created_on` and `updated_at` are integer timestamps (`time()`, `INT NOT NULL`), and `created_at` (`INT NULL`).
     - `patients`: populates mandatory `phone`, `date_of_birth`, `gender`.
     - `treatments`: populates `treatment_name` (`VARCHAR(100) NOT NULL`), `treatment_type`, and `treatment_description`.
     - `prescriptions`: populates mandatory `dosage`, `frequency`, `duration`, `instructions`, `prescribed_date`, `expiry_date`.
     - `finances`: populates mandatory `payment_method`, `service_type`, `transaction_date`.
     - `inventory_usage`: `recorded_by` strictly requires a positive integer ID referencing a created fixture user (no hardcoded 1 fallback); `items_used` set to `[]`.
     - `roles`: `clinic_id` set to active clinic.
     - `appointments`: `provider_user_id` populated.
     - `clinic_subscriptions`: inserted with `status = 'active'` to satisfy `SubscriptionFilter`.

3. **Genuine Binary Test Payloads & Active Upload Verification:**
   - Valid 1x1 PNG binary bytes (`SYNTHETIC_PNG_BYTES`).
   - Valid PDF 1.4 binary bytes (`SYNTHETIC_PDF_BYTES`).
   - Active HTML payload (`SYNTHETIC_HTML_BYTES`) used to verify inline rejection of forged MIME uploads.

4. **18 Focused Test Methods Summary:**
   1. `testAuthorizedPatientFileDownloadAndInlineEnglish`: English locale download (200, attachment) and inline (200, inline, CSP sandbox, nosniff). *(Verified PASS)*
   2. `testAuthorizedPatientFileDownloadAndInlineArabic`: Arabic locale (`ui_locale = 'ar'`) download and inline. *(Verified PASS)*
   3. `testCrossClinicAttachmentAccessFailsNotFound`: Cross-clinic isolation (404 on download and inline). *(Verified PASS)*
   4. `testNoViewPermissionFailsForbidden`: Role lacking module permission returns 403 Forbidden. *(Verified PASS)*
   5. `testInactiveRoleFailsUnauthorizedOrForbidden`: Role with `is_active = 0` rejected (403 or redirect). *(Verified PASS)*
   6. `testGlobalControlPlaneModeFailsNotFound`: Control-plane `global_mode = true` returns 404 Airlock. *(Verified PASS)*
   7. `testFinanceExportRequiresExportPermissionNotView`: `finance:view` denied 403, `finance:export` permitted 200. *(Verified PASS)*
   8. `testReportExportRequiresExportPermissionNotView`: `reports:view` denied 403, `reports:export` permitted 200. *(Verified PASS)*
   9. `testReportPositiveEntityIdFailsNotFound`: Report with positive `entity_id` fails closed with 404. *(Verified PASS)*
   10. `testReferencedLogoAllowedAndOrphanLogoDenied`: Referenced logo returns 200; unreferenced orphan logo fails 404. *(Verified PASS)*
   11. `testInlineActiveUploadAndForgedMimeDenied`: Active HTML upload denied inline (403), downloaded safely as `application/octet-stream`. *(Verified PASS)*
   12. `testAllValidEntityFamiliesAuthorized`: Exercises `examination`, `appointment`, `treatment`, `prescription`, `contract`, `finance` record, and `inventory_usage`. *(Verified PASS)*
   13. `testCrossClinicParentLinkageFailsNotFound`: Entity in Clinic A referencing Patient in Clinic B fails 404. *(Verified PASS)*
   14. `testFilesystemConfinementViolationFailsNotFound`: Creates an owned synthetic file under `writable/cache/` (outside tenant upload directory), asserts physical file exists before routed request, and verifies 404 confinement enforcement. *(Verified PASS)*
   15. `testUnknownEntityTypeFailsNotFound`: Attachment with unknown/unsupported `entity_type` fails 404. *(Verified PASS)*
   16. `testGeneratedHtmlContractDownloadOctetStreamAndInlineDenied`: Generated HTML contract downloads as `application/octet-stream` (200) and denies inline preview (403). *(Verified PASS)*
   17. `testSoftDeletedEntitiesAndSoftDeletedParentPatientDenied`: Exercises soft-deleted records across all seven soft-deletable families (`patient`, `examination`, `appointment`, `treatment`, `prescription`, `contract`, `finance`) and soft-deleted parent patient linkage, verifying all fail closed with 404. *(Verified PASS)*
   18. `testClinicDirectoryAliasCannotAuthorizeFileDelivery`: Proves a clinic-specific alias pointing at another created clinic cannot authorize either file route. Windows executes an actual directory-junction fixture and removes only that owned link without recursion; POSIX uses `symlink()`. The target synthetic bytes remain unchanged. *(Verified PASS on Windows, no skip)*

---

## 5. Precise Schema Assumptions & Evidence

1. **Soft Delete (`deleted_at`):**
   - Verified present on: `patients`, `appointments`, `examinations`, `treatments`, `prescriptions`, `treatment_contracts`, `finances`.
   - Verified absent on: `inventory_usage`, `file_attachments` (uses hard delete / cleanup), `settings`.
2. **Column Requirements:**
   - `treatments.treatment_name` is `varchar(100) NOT NULL`.
   - `users.created_on` and `users.updated_at` are `int(11) NOT NULL` (integer Unix timestamp). `users.created_at` is `int(11) NULL`.
   - `patients` requires `phone`, `date_of_birth`, `gender`.
   - `prescriptions` requires `dosage`, `frequency`, `duration`, `instructions`, `prescribed_date`, `expiry_date`.
   - `finances` requires `payment_method`, `service_type`, `transaction_date`.
   - `inventory_usage` requires `recorded_by` (valid user ID) and `items_used` (`longtext` JSON).
3. **Storage & Pathing:**
   - Tenant files are stored under `WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId . DIRECTORY_SEPARATOR`.
   - Clinic-specific directory must resolve to its own expected identity under the canonical trusted uploads root; `is_link()` alone is insufficient for Windows junctions.
   - Physical MIME detection via PHP `finfo_file`.

---

## 6. Verification Commands for Root Acceptance

These commands were executed in the isolated certification worktree at base `0986fefea865e5e0acb858ee57eabd2e69c7a7d2`, using the reviewed `phpunit.staging-certification.xml` from pending PR #4. The separate three-file clinical PR is packaged on main `8be7ad67a4a2810daea55f23c307b206e09cfdf4`; that main tree does not contain this XML, and this PR does not add configuration. Reproduction requires the separately reviewed certification harness (or an explicitly reviewed equivalent temporary XML), isolated credentials and physical/database ownership proofs. These commands do not certify the published main-only branch's test configuration by themselves. The original controller blob is identical on both base commits: `fa81d1c7cb113240505065430f117a90edf69a76`.

```bash
# 1. PHP Syntax / Lint check (verified clean in this workspace):
php -l app/Controllers/FileController.php
php -l tests/unit/ClinicalFileAuthorizationTest.php

# 2. Focused execution inside the verified, reserved synthetic staging environment:
# Full suite execution:
php scripts/safe_test_runner.php -c phpunit.staging-certification.xml --no-coverage --no-logging --do-not-cache-result tests/unit/ClinicalFileAuthorizationTest.php

# Focused execution on newly added clinic directory alias regression:
php scripts/safe_test_runner.php -c phpunit.staging-certification.xml --no-coverage --no-logging --do-not-cache-result --filter testClinicDirectoryAliasCannotAuthorizeFileDelivery tests/unit/ClinicalFileAuthorizationTest.php
```

---

## 7. Open Items & Remaining Decisions

- **Independent Execution Evidence:** Baseline run `denta-clinical-bb846d32d3e24cc4b3d79f6173b07764` verified 17 tests / 96 assertions. Final affected-test run `denta-clinical-b78db6c3d7ec4522b383794c10413a20` verified 4 tests / 57 assertions against the final source/test hashes above. Both include exact PHPUnit output, physical identity proofs and before/after health snapshots. Codex separately checked that all three preservation maps contain exactly 70 equal entries.
- **Contract HTML Artifact Compatibility:** `TreatmentContracts::sign()` renders `contracts/print` and persists `.html` content with MIME `text/html` under the historical purpose `contract_pdf`; it does not generate a binary PDF. `contracts/show.php` already provides a neutral **Download** link to the signed attachment and a separate **Print** link to `/contracts/{id}/print`, whose route requires `patients:view`. The candidate preserves both workflows and existing downloadable bytes/filenames. It denies active HTML at `/file/inline`, which these UI links do not use. No PDF dependency, artifact conversion or HTML execution exception is introduced. A genuine binary PDF download is not an existing supported behavior and would require a separate implementation.
- **Contract Workflow Release Gate:** Operator/browser confirmation of the existing Print and signed-HTML Download workflows remains required before production release. Safe download is available as `application/octet-stream`; direct attachment inline preview remains HTTP 403. Source inspection establishes compatibility of the current links, not completion of that browser/operator gate.
- **Runtime Prerequisite:** Hostinger's enabled `fileinfo` extension must remain part of release runtime verification. If unavailable, calls to the inspection functions fail before delivery; the candidate has no permissive MIME fallback for a missing extension.
