# Staging Certification Runbook: PR #3 Doctor Scheduling & Schedule Impact

**Target PR SHA:** `ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5` (HEAD)  
**Current remote main / PR #2 integration SHA:** `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`  
**Document Status:** Active Staging Certification Runbook  
**Execution Authority:** Codex executes test harness independently on disposable MariaDB (`127.0.0.1:3307`). All test pass claims require Codex verification.  
**Merge Authority:** Repository Owner controls PR #3 merge decision. No auto-deploy, no code integration, and no migration authorized.

---

## 1. Scope and Evidence Boundaries

This runbook defines the isolated staging certification procedure for PR #3 (Dynamic Doctor Scheduling & Schedule Impact Hardening) at exact SHA `ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5`.

### 1.1 Verified Test Suites in Repository
The test suite structure for PR #3 staging certification consists of:
1. **`tests/unit/PR3StagingCertificationTest.php`** (AGY Harness):
   - `testRepresentativeSyntheticScaleBenchmarkAndHistoricalPreservation`: 50,000 historical + 5,000 future appointments + 50,000 charges + 2,002 patients. 4,999 active items reported (omits 07:00 elapsed; classifies 07:45 ongoing as non-reschedulable). Byte-identical snapshot hash across 6 tables.
   - `testChargeQueryExplainAndScanAssessment`: EXPLAIN plan assessment of batch charge query against populated 50k-charge fixture. SHOW INDEX inventory without hard assertions on missing composite index.
   - `testAuditReconstructionAndSyntheticRestoreRehearsal`: 320 affected appointments with long Arabic reasons (>64 KiB bundle). Full export of compact pointer + all part rows to JSON file with SHA256. Loss simulation (deletion of pointer + all parts) confirms fail-closed `null`. Complete restore under checked transaction verifies bit-for-bit SHA-256 equality.
   - `testRollbackRehearsalConfigurationFailureAndOperationalPreservation`: Second-part audit failure fault injection triggers full rollback (`transDepth = 0`) with preserved proof token for retry. Operational rollback verifies `cannotDisable` protection when provider appointments exist.
   - `testLegacyReadinessUnresolvedProvidersAndDisambiguation`: Direct non-overlapping seeding; verifies NULL provider retains NULL and chair snapshot without auto-backfilling; duplicate doctors disambiguated by numeric ID; completed visit is non-reschedulable (`reschedulable = false`).
2. **`tests/unit/PR3StagingConcurrencyTest.php`** (Root Harness):
   - 2 tests, 159 assertions.
   - Verifies 8 concurrent booking workers across shared global roles and disjoint clinics without deadlock.
3. **`tests/unit/PR3StagingRollbackTest.php`** (Root Harness):
   - 2 tests, 22 assertions.
   - Proves canonical restoration of previous working hours through fresh preview/apply and new immutable audit evidence.
4. **`tests/unit/ScheduleImpactWorkflowTest.php` & `PR3HistoricalIsolationTest.php`**:
   - Routed EN/AR preview/apply/recheck, CSRF protection, and historical write lock independence.
5. **`tests/js/ScheduleImpactMobileTest.cjs`**:
   - 198 assertions on headless Chromium at 320px, 360px, and 390px viewports.

### 1.2 Explicitly Excluded & Gated
- **No Production Access:** Zero access to Hostinger hosting, production databases, or customer SQL dumps.
- **No Direct DB Testing by AGY:** DB test execution belongs exclusively to Codex via `scripts/safe_test_runner.php`.
- **No Schema / Index DDL:** No migrations or index mutations are applied in this harness.
- **Human Pilot Gate Remains OPEN:** Automated test execution does not substitute for staff training, legacy appointment reconciliation, or patient/doctor communication.

---

## 2. Staging Matrix: Synthetic Scale vs. Real Clinic Distribution

### 2.1 Synthetic Test Specification
The certification test class `tests/unit/PR3StagingCertificationTest.php` implements the following synthetic distribution:

| Metric | Synthetic Fixture Volume | Verification Target |
| :--- | :--- | :--- |
| **Historical Appointments** | 50,000 rows | Dates prior to review horizon (`2020-01-08`), completed status, cycling 4 providers. |
| **Future Appointments** | 5,000 rows | Starting `2030-01-07 06:00 UTC` (`08:00 local`). Same-day ended (07:00, omitted), ongoing (07:45, non-reschedulable), upcoming (08:00 exact start, 09:00..), plus multi-week future (2030-01-08..). Exactly 4,999 active items reported. |
| **Historical Charges** | 50,000 rows | Associated with historical patient/appointments, completed status. |
| **Active Encounter Charge** | 1 row | Accepted charge line linked to future visit (`Active encounter lock`). |
| **Examinations / Finances** | 1 exam / 1 payment | Linked to historical visits; byte-identical preservation verified. |
| **Patients** | 2,002 rows | 1 base fixture patient + 1 historical + 2,000 generated patients. |
| **Providers** | 4 doctors | Canonical clinic doctor roles with duplicate names (`Duplicate Doctor`). |
| **Timezone Reference** | UTC `2030-01-07 06:00:00` | Clinic timezone `Asia/Hebron` (UTC+2 in winter, `08:00:00` local). |

### 2.2 Unknown Real Clinic Variance & Measurement Risks
Real dental clinics exhibit operational patterns that deviate from uniform synthetic fixtures:
1. **Horizon Depth & Point Locks:** Real clinics may book appointments months into the future. Point-locking active future visits scales linearly with horizon depth:
   $$\text{Lock Queries} = N_{\text{future active appointments}}$$
   For 5,000 future visits (with 1 elapsed omitted), `lockState()` executes 4,999 point-lock queries (`SELECT id FROM appointments WHERE id = ? AND clinic_id = ? FOR UPDATE`).
2. **Preceding-Date & Midnight-Crossing Intervals:** The review horizon begins on clinic-local `today`. Unsupported preceding-date intervals or midnight-crossing appointments require a read-only preflight scan before pilot deployment.
3. **Volatile Reference Second:** Preview tokens compute state digests without hashing volatile seconds; however, if a visit transitions from upcoming to ongoing during preview, the token becomes stale and requires re-preview.

---

## 3. Charge-Query EXPLAIN Assessment & Index Scan Trade-Offs

### 3.1 Existing Schema State
Inspection of `clinical_charge_lines` schema from migration `2026-05-20-120000_CreateClinicalChargeLinesTable.php` and runtime `SHOW INDEX`:
- **Primary Key:** `id`
- **Existing Indexes:**
  - `idx_ccl_patient_status` (`clinic_id`, `patient_id`, `status`)
  - `idx_ccl_examination` (`clinic_id`, `examination_id`)
  - `idx_ccl_treatment` (`clinic_id`, `treatment_id`)
  - `idx_ccl_finance` (`clinic_id`, `finance_id`)
  - `idx_ccl_source_entity` (`clinic_id`, `source_entity_type`, `source_entity_id`)
  - `idx_ccl_provider` (`clinic_id`, `provider_type`, `provider_user_id`)
  - `idx_ccl_charge_date` (`clinic_id`, `charge_date`)
- **Composite Index Status:** There is currently no composite index on `(clinic_id, appointment_id)`. The test records the present index inventory without hard-asserting absence to allow future additive index migrations.

### 3.2 Query Plan Analysis
During report generation, `AppointmentReschedulabilityService::batchEvaluate()` queries:
```sql
SELECT DISTINCT appointment_id 
FROM clinical_charge_lines 
WHERE clinic_id = ? AND appointment_id IN (?, ?, ..., ?)
```
- **Optimizer Path:** The database optimizer selects an available key prefixed with `clinic_id` (typically `idx_ccl_patient_status` or `idx_ccl_examination`).
- **Execution Cost:** The captured plan estimates clinic-prefix work and reports its selected key and filtering strategy. EXPLAIN estimates do not measure actual examined rows or establish a universal optimizer path.
- **Scalability Finding:** Report dependencies are batched, while scan work may grow with clinic charge volume. Compare captured query counts and plans; do not infer constant work from batching.

### 3.3 Future Migration Proposal (Staging Gate)
- **Recommendation:** Propose adding composite index:
  ```sql
  ALTER TABLE clinical_charge_lines ADD INDEX idx_ccl_clinic_appointment (clinic_id, appointment_id);
  ```
- **Staging Gate Conditions:**
  - Must evaluate index maintenance overhead on routine billing writes (`insert`, `update`, `void`).
  - Must verify DDL table lock duration on large production tables (`ALGORITHM=INPLACE, LOCK=NONE` support in MariaDB).
  - This index proposal is **advisory only** and must NOT be created as part of PR #3.

---

## 4. Cross-Clinic Shared Roles & Writer Concurrency

### 4.1 Serialization Architecture
To prevent deadlocks between concurrent booking and schedule configuration transactions, `SchedulingAuthorityLocks::acquire()` enforces strict global lock ordering:
1. **Clinic Row Lock:** `SELECT id, status FROM clinics WHERE id = ? FOR UPDATE` (clinic serialization anchor).
2. **Clinic-Owned Memberships:** Query `clinic_users` under the clinic lock.
3. **Global User Point Locks:** Execute one primary-key query per user in ascending numeric order:
   ```sql
   SELECT id FROM users WHERE id = ? FOR UPDATE
   ```
4. **Global Role Point Locks:** Execute one primary-key query per role in ascending numeric order:
   ```sql
   SELECT id FROM roles WHERE id = ? FOR UPDATE
   ```
5. **Additional Authority Rows:** The helper also locks each user's `user_roles` and each role's `role_permissions` in ID order; review the helper and captured worker SQL for the complete order.
6. **Snapshot Reads:** Policy reads (settings, provider profiles, active appointments) occur only after authority locks are held.

### 4.2 Concurrency Evidence
- Tested and verified in `PR3StagingConcurrencyTest`: 8 concurrent workers across shared global roles and disjoint clinics execute without deadlock.
- Clinics sharing global users or roles intentionally serialize on shared entities to maintain consistent ordering. These samples do not establish universal deadlock freedom.
- Unrelated clinical and financial record mutations do not acquire scheduling authority locks.

---

## 5. Audit Storage Restore & Missing-Part Detection

### 5.1 Storage Architecture
Audit evidence is split across two existing tables without requiring schema additions:
1. **Settings Pointer:** `settings` table stores a compact review reference (`schedule_impact_latest_audit`) containing `version`, `review_id`, `bundle_hash`, `parts`, `first_id`, `last_id`, `actor_user_id`, and `applied_at`. Size is bounded to <1,000 bytes.
2. **Multipart Evidence:** `control_plane_audits` stores serialized chunks under `event_type = 'schedule_impact_bundle_part'`.
   - Chunk size: 40,000 base64 characters.
   - Part payload cap: 60,000 bytes (within `TEXT` column budget).
   - Maximum bundle parts: 4,096 parts.

### 5.2 Fail-Closed Integrity Rehearsal
- **Corruption / Deletion Behavior:** If any single audit part in the sequence is missing, deleted, or has corrupted JSON/hash:
  - `ScheduleImpactAuditStore::read()` returns `null`.
  - `ScheduleImpactService::getLatestAuditRecord()` returns `null`.
  - Partial or incomplete evidence is **never trusted**.
- **Synthetic Audit Reconstruction Rehearsal:**
  - Full export of exact compact pointer row + all part rows to `tmp/pr3-staging-audit-export.json` with SHA-256 verification.
  - Complete deletion of pointer and all audit parts confirms both read `null`.
  - Full restoration of pointer and parts under a checked transaction restores the bundle bit-for-bit with matching SHA-256 hash.
  - Appointment rows are byte-identical before and after this recovery case. The separate load case checks six appointment/clinical/financial tables across read-only impact generation.
  - **Explicit Distinction:** This test proves checked synthetic export/reinsert and reconstruction mechanics. It does not test storage-engine crash durability, consistent live backups or disaster recovery.

### 5.3 Retention Policy Warning
Platform-level audit log pruning scripts must be configured to respect active scheduling review pointers. Pruning audit parts referenced by a clinic's `schedule_impact_latest_audit` will cause the clinic's review history to become unavailable, requiring a fresh operator review before subsequent schedule changes.

---

## 6. Operational & Code Rollback Boundaries

### 6.1 Transaction Rollback on Failure
When an audit write or downstream constraint fails during `ClinicSchedulingConfigurationService::configureHours()`:
1. The transaction rolls back completely (`$db->transRollback()`).
2. Staged configuration changes in `settings` are reverted.
3. Appointments remain in their pre-attempt state.
4. The generated preview proof token hash is preserved in session (`schedule_impact_proofs`), allowing the operator to retry without re-submitting preview forms.

### 6.2 Operational Rollback & cannotDisable Protection
- **Default-Off State:** `dynamic_doctor_scheduling_enabled` defaults to `'0'` (disabled).
- **Legacy Mode:** When disabled, booking context exposes no dynamic providers. Legacy booking handling ignores provider input and stores a NULL provider; this is not a promise that all requests containing a provider field are rejected.
- **Preservation of Populated Providers:** Existing appointments with populated `provider_user_id` are never wiped, nulled, or altered when viewing or managing legacy settings.
- **Protection Against Accidental Disabling:** If any appointment in the clinic has a non-null `provider_user_id`, `configureHours()` explicitly throws `cannotDisable` and refuses to disable dynamic scheduling:
  ```php
  if (!$enabled && $schemaReady && $db->table('appointments')->where('clinic_id', $clinicId)->where('provider_user_id IS NOT NULL', null, false)->countAllResults() > 0) {
      throw new \DomainException('cannotDisable');
  }
  ```
- **Canonical Hours Restoration:** Proved in `PR3StagingRollbackTest`: restoring previous working hours requires a fresh preview and produces new immutable audit evidence while preserving existing appointment records.

### 6.3 Code Rollback Requires Separate Review
Provider IDs and provider profiles were introduced by Phase B (PR #2), already present on current remote main. PR #3 adds the pre-pilot impact workflow; it does not introduce that schema.

Do not assume compatibility with an arbitrary older revision. Review the exact rollback candidate against post-pilot records and authorization/audit behavior. Preserve additive schema, stable doctor IDs, existing appointments and clinical/financial relationships. Never clear provider IDs, delete appointments or down-migrate to force feature-off. Once any provider-linked appointment exists, including historical or cancelled visits, disabling is refused. Fresh audited hours restoration is demonstrated; old-code deployment rollback is not rehearsed or authorized.

---

## 7. Manual Operator & Communication Readiness (EN/AR & Legacy)

### 7.1 Automated Workflow Evidence Citations
The following existing automated test suites exercise the technical implementation of localized workflows:
1. `ScheduleImpactWorkflowTest::testRealRoutedEnglishArabicPreviewApplyAndRecheckWithCsrfAndPermissionChecks`:
   - Real routed HTTP POST `/settings/preview-schedule-impact` and `/settings/apply-schedule-impact`.
   - CSRF token validation and permission enforcement (403 on denied settings permission).
   - Arabic (`ar`) and English (`en`) locale strings, directionality (`dir="rtl"` / `dir="ltr"`).
2. `ScheduleImpactWorkflowTest::testLosslessArabicBundleOver64KiBHasByteBoundedPartsAndCompactPointer`:
   - Lossless serialization and reconstruction of Arabic unicode strings.
3. `tests/js/ScheduleImpactMobileTest.cjs`:
   - 198 assertions using headless Chromium at mobile viewports (**320px**, **360px**, **390px**) verifying responsive table rendering, Arabic typography, and acknowledgment form submission.

### 7.2 Human Operator Sign-Off (Gate Remains OPEN)
Automated testing cannot replace clinic operator judgment:
- **Legacy Unresolved Bookings:** Historical visits with `provider_user_id IS NULL` display their historical `chair_name` (e.g. `Historical Room 3`). The application **never infers or auto-backfills provider identities**. Clinic staff must review each unresolved appointment manually.
- **Duplicate-Named Doctors:** Doctors sharing identical names (e.g. `Duplicate Doctor`) are disambiguated in the UI by their unique numeric user IDs. Operators must visually confirm physician assignments.
- **Communications Dispatch:** Applying schedule impact changes does **NOT** dispatch WhatsApp, SMS, or email messages to patients. Clinic staff must coordinate patient rescheduling through authorized clinical channels.

---

## 8. Unverified Checks & Authoritative Release Blockers

### 8.1 Status of Certification Checks

Final Codex run: **41 PHP tests / 643 assertions**, plus **198 EN/AR mobile Chromium assertions**. The five-test AGY class independently passed 106 assertions after corrections. Measurements, artifact hashes, scope and blockers: [production-readiness report](PRODUCTION_READINESS_2026-10-16.md) and [evidence JSON](evidence/PR3_STAGING_2026-10-16.json).

| Category | Check Description | Certification Status | Responsible Reviewer |
| :--- | :--- | :--- | :--- |
| **Harness Code** | PR3StagingCertificationTest syntax & structure | **VERIFIED (PHP -l clean)** | AGY (Implementer) |
| **Concurrency Suite** | 8 concurrent workers shared/disjoint | **VERIFIED (2 tests, 159 assertions)** | Codex (Runner) |
| **Hours Restoration** | Previous-hours restoration & default-off | **VERIFIED (2 tests, 22 assertions)** | Codex (Runner) |
| **Scale Benchmark** | 50k historical + 5k future locked preview execution | **PASSED in final focused suite** | Codex (Runner) |
| **Charge Index Scan** | EXPLAIN plan on populated clinical_charge_lines | **PASSED in final focused suite** | Codex (Runner) |
| **Audit Rehearsal** | Multipart restore & corrupt part rejection | **PASSED in final focused suite** | Codex (Runner) |
| **Rollback Rehearsal**| Second-part fault injection & cannotDisable | **PASSED in final focused suite** | Codex (Runner) |
| **Legacy Readiness** | Unresolved NULL providers & name disambiguation | **PASSED in final focused suite** | Codex (Runner) |
| **Hostinger Parity** | Production runtime, extensions, & MariaDB match | **UNVERIFIED (Gated)** | Ops / Staging Gate |
| **Operator Pilot** | Clinic staff walkthrough & Arabic UI sign-off | **UNVERIFIED (Gated)** | Clinic Operator |
| **Communications** | Patient messaging & reminder queue integration | **UNVERIFIED (Gated)** | Staff Pilot Gate |

### 8.2 Authoritative Release Blockers Not Waived by PR #3
Approval of PR #3 is limited strictly to scheduling impact and provider profile isolation. The following repository release blockers remain active and are **not waived**:
1. **Clinical File Permissions:** Tenant isolation on patient document uploads and file attachments.
2. **Finance & Settlement Integrity:** Ledger balance validation and payout settlement reconciliation.
3. **Framework & Dependency Vulnerabilities:** Baseline P0 security findings and CodeIgniter framework patches.
4. **Production Schema Parity:** Verification that production Hostinger database matches canonical migrations and contracts without manual drift.

---

## 9. Test Execution Instructions (For Codex)

Codex must execute the certification harness on the dedicated test runner without coverage logging:
```bash
php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result tests/unit/PR3StagingCertificationTest.php
```

### Expected Output Artifacts
- `tmp/pr3-staging-scale.json`: Scale metrics, query counts, memory usage, and EXPLAIN plans.
- `tmp/pr3-charge-index-assessment.json`: Charge-query index scan analysis and migration assessment.
- `tmp/pr3-staging-audit-export.json`: Exported fixture audit parts with SHA-256 verification hash.
- `tmp/agy-staging-findings.md`: Summary of implementer verification and open staging gates.
