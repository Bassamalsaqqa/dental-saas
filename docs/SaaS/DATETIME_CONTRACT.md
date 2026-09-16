# SaaS Datetime & Subscription Timestamp Contract

**Status:** Canonical Reference  
**Last Updated:** 2026-09-15  
**Governing Modules:** Subscription Lifecycle (`clinic_subscriptions`), Onboarding Subscription Provisioning, PlanGuard, Control Plane Subscription Management  

---

## 1. Scope & Bounded Responsibility

This contract strictly governs subscription lifecycle timestamps stored in the `clinic_subscriptions` table (`start_at`, `end_at`, `canceled_at`, `paused_at`).

### Scope Exclusions
- **Application Audits & Model Callbacks:** General application audit logs (`activity_logs`, `control_plane_audits`) and CodeIgniter model timestamp callbacks (`created_at`, `updated_at`) across unrelated modules continue using their established repository patterns and are not altered by this subscription contract.
- **Clinical & Financial Records:** Clinical appointment times, patient documentation, and financial transaction dates continue to reflect clinic-local business representations.

---

## 2. Core Principle: Canonical UTC Persistence

Subscription boundaries define legal and financial access to tenant features across timezones. Because MySQL `DATETIME` columns store naive timestamp strings without embedded offset metadata, all subscription timestamps in `clinic_subscriptions` **must be stored in canonical UTC (`YYYY-MM-DD HH:MM:SS`)**.

### The Process Timezone Disconnect
1. **Bootstrap / Filter Context:** CodeIgniter 4 begins execution with `App::appTimezone` (`UTC`). All early HTTP route filters (`auth`, `tenant`, `subscription`, `permission`) evaluate before controller initialization.
2. **Controller Context:** `BaseController::initController()` invokes `applyRuntimeTimezone()`, reading clinic/global settings and changing PHP's process timezone via `date_default_timezone_set()` (e.g. to `Asia/Jerusalem`, UTC+3 in September).
3. **The Disconnect:** When subscription records were formatted with `date('Y-m-d H:i:s')` in controller scope, local time (e.g. `19:37:13`) was stored in the database. When subsequently read in filter scope (UTC), `strtotime('19:37:13')` evaluated the local string as 3 hours into the future, triggering `SUBSCRIPTION_STATE_BLOCK (reason=NOT_STARTED)` and blocking tenant dashboard entry with HTTP 404.

---

## 3. Authoritative Implementation (`ClinicSubscriptionModel`)

All parsing, evaluation, date resolution, and display formatting of subscription dates are centralized in `App\Models\ClinicSubscriptionModel`:

### A. Strict UTC Parser (`parseUtcTimestamp`)
`ClinicSubscriptionModel::parseUtcTimestamp(?string $datetime): int|false|null`
- **Grammar:** Strict subset only: `Y-m-d H:i:s`, `Y-m-d\TH:i:s`, `Y-m-d\TH:i:s\Z`, and explicit offset forms (`Y-m-d\TH:i:sP`).
- **Field Reset:** Uses `!` prefix in `createFromFormat` to reset all fields.
- **Strict Error Checking:** Inspects `DateTimeImmutable::getLastErrors()` for warnings/errors.
- **Round-Trip Match:** Compares formatted output back to input string in normalized form.
- **Rejections:** Strictly rejects relative strings (`yesterday`, `tomorrow`, `+1 day`), impossible dates (`2026-02-30`), overflow values (`25:00:00`, `12:60:00`), zero dates (`0000-00-00 00:00:00`), partial dates (`2026-09-15`), and missing seconds.
- **Return Contract:**
  - `null`: Input is `null` or whitespace-only (open-ended boundary).
  - `false`: Input is malformed, impossible, or unsupported (fails closed).
  - `int`: Exact Unix epoch seconds in UTC.

### B. Fail-Closed End Date Resolution (`resolveEndAt`)
`ClinicSubscriptionModel::resolveEndAt(string $duration, ?string $startAtUtc): string|false|null`
- **Allowed Durations:** Strictly `'monthly'`, `'yearly'`, `'lifetime'`. Any other duration returns `false`.
- **Lifetime:** Returns `null` strictly for explicit intentional open-ended subscriptions.
- **Finite Durations:** Parses `$startAtUtc` via `parseUtcTimestamp()`.
- **Fail-Closed Rule:** If `$startAtUtc` is invalid or cannot be parsed, **returns `false`** (never defaults to `null`). This prevents calculation failures from granting unlimited lifetime access.

### C. Authoritative Standing Evaluation (`evaluateStanding`)
`ClinicSubscriptionModel::evaluateStanding(?array $sub, ?int $nowUtc = null): array`
- Evaluates active standing canonically against UTC Unix epoch seconds.
- Rejects missing subscription (`no_valid_subscription`).
- Rejects non-active statuses (`PAUSED`, `CANCELED`, etc.).
- Evaluates `start_at`:
  - Malformed date -> `['is_valid' => false, 'reason' => 'MALFORMED_START_AT']`.
  - Future start (`start_at > now`) -> `['is_valid' => false, 'reason' => 'NOT_STARTED']`.
  - Current/past start (`start_at <= now`) -> valid.
- Evaluates `end_at`:
  - Malformed date -> `['is_valid' => false, 'reason' => 'MALFORMED_END_AT']`.
  - Expired period (`end_at <= now`) -> `['is_valid' => false, 'reason' => 'EXPIRED']`.
  - Future end or null (lifetime) -> valid.
- Returns `['is_valid' => true, 'reason' => 'ACTIVE']` only when all checks pass.

---

## 4. UI Input Normalization & Display Round-Trip

### Input Normalization (`ControlPlane\Clinics::normalizeDateTime`)
- **Reset Omitted Seconds:** Format `!Y-m-d\TH:i` resets seconds explicitly to `:00`.
- **Error Checking:** `DateTimeImmutable::getLastErrors()` checks for warnings and errors.
- **DST Gap / Fold Policy:**
  - **Spring-Forward Gaps:** When an operator submits a local time falling inside a DST gap (e.g. `2026-03-08T02:30` in `America/New_York` where clocks jump 02:00 -> 03:00), round-trip comparison (`$dt->format(...) !== $input`) detects the jump to `03:30` and **rejects the nonexistent time as invalid (`null`)**.
  - **Fall-Back Folds:** Ambiguous local times during fall-back repeat hours (e.g. `2026-11-01T01:30` in `America/New_York` where 01:00-02:00 occurs twice, once in EDT UTC-4 and once in EST UTC-5) are detected via transition inspection and **strictly rejected (`null`)** unless the operator provides an explicit ISO offset (e.g. `-04:00` or `-05:00`).
- **Fail-Closed:** Invalid timezones, calendar overflows, ambiguous times, or relative dates return `null`, allowing controller validation to reject the submission without database mutation.

### Display Formatting (`ClinicSubscriptionModel::formatDisplay`)
- Converts stored UTC instants into target display timezone (`date_default_timezone_get()`) for rendering in views (e.g. `clinic_detail.php`).

---

## 5. Unexecuted & Unapproved Historical Repair Procedure (Reference Only)

> [!WARNING]
> **DO NOT EXECUTE IN THIS PHASE.**
> This procedure is documented strictly as a reviewed technical proposal for operator review during Finalization/Deployment. It has not been executed and must never be applied without explicit operator authorization.

### Context & Evidence
- **Clinic 26, Subscription 35** was created on 2026-09-15 at 16:37:13 UTC.
- Because `OnboardingService` was executed in controller context with `Asia/Jerusalem` (UTC+3), `start_at` was stored as `2026-09-15 19:37:13` (local time without offset).
- At 16:40:30 UTC, the operator extended the subscription to 1 year; `end_at` was updated to `2027-10-15 19:37:13`.
- Although `start_at <= now` became true once UTC time passed 19:37:13, the stored database values retain a 3-hour local skew relative to canonical UTC.

### Safety Invariants
1. **No Mass Shifts:** Under no circumstances should bulk SQL updates (`UPDATE clinic_subscriptions SET ...`) be run across historical records. Older records may have been seeded under different assumptions or represent intentional future dates.
2. **Concurrency Protection:** The repair must verify the exact current state before updating to prevent overwriting operator modifications made after the snapshot.
3. **Audit Trail:** The repair must record an explicit audit log in the same transaction.
4. **No Copy/Paste Silent Execution:** Procedures must enforce exact version matching and row-count verification (`ROW_COUNT() = 1`) before committing, aborting with `ROLLBACK` if stale state is encountered.
5. **Operator Evidence Required:** Do not infer timestamp conversions solely from `created_at` or `updated_at`, as legacy rows may share local offset skews. Operator evidence confirming intended instants is required before any authorized execution.

---

### Unexecuted Architecture: Application CLI Repair Command (Recommended Specification)

Rather than raw manual SQL which lacks safety guards, any future repair should be implemented as an authenticated, audited CLI command (e.g. `php spark subscription:repair-tz`) that reuses canonical models and services.

> [!IMPORTANT]
> **Authentication Requirement:** Supplying an integer operator ID argument does NOT make a command authenticated. Any future implementation must explicitly verify that the operator exists in the `users` table, is active (`active = 1`), and holds verified Super Admin role authority (`PermissionService::isSuperAdmin()`) before executing.

```php
// Application CLI Command Design (Specification / Reference Only - UNEXECUTED)
public function run(array $params): int
{
    $clinicId = (int) ($params['clinic_id'] ?? 0);
    $subscriptionId = (int) ($params['subscription_id'] ?? 0);
    $operatorUserId = (int) ($params['operator_id'] ?? 0);
    $expectedUpdatedAt = (string) ($params['expected_updated_at'] ?? '');
    $reason = (string) ($params['reason'] ?? '');

    if ($operatorUserId <= 0 || empty($expectedUpdatedAt) || empty($reason)) {
        $this->logger->error('Explicit operator_id, expected_updated_at, and reason are strictly required.');
        return EXIT_USER_INPUT;
    }

    // Explicit authentication & authorization verification
    $userModel = new \App\Models\UserModel();
    $operator = $userModel->find($operatorUserId);
    $permissionService = new \App\Services\PermissionService();
    if (!$operator || (int) $operator['active'] !== 1 || !$permissionService->isSuperAdmin($operatorUserId)) {
        $this->logger->error('Unauthorized: operator does not exist, is inactive, or lacks Super Admin authority.');
        return EXIT_ERROR;
    }

    $db = \Config\Database::connect();
    if ($db->transDepth !== 0) {
        $this->logger->error('Repair command requires an independent transaction.');
        return EXIT_ERROR;
    }

    if (!$db->transBegin()) {
        $this->logger->error('Failed to begin database transaction.');
        return EXIT_ERROR;
    }

    try {
        // 1. Lock and inspect current row version
        $row = $db->query(
            "SELECT id, clinic_id, status, start_at, end_at, updated_at
             FROM clinic_subscriptions
             WHERE id = ? AND clinic_id = ? FOR UPDATE",
            [$subscriptionId, $clinicId]
        )->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Subscription record not found.');
        }

        // 2. Strict concurrency / version verification
        if ($row['status'] !== 'active'
            || $row['start_at'] !== '2026-09-15 19:37:13'
            || $row['end_at'] !== '2027-10-15 19:37:13'
            || $row['updated_at'] !== $expectedUpdatedAt
        ) {
            throw new \RuntimeException('Stale row state detected. Aborting repair.');
        }

        // 3. Checked update with full predicate
        $newUpdatedAt = ClinicSubscriptionModel::nowUtc();
        $db->query(
            "UPDATE clinic_subscriptions
             SET start_at = '2026-09-15 16:37:13',
                 end_at   = '2027-10-15 16:37:13',
                 updated_at = ?
             WHERE id = ?
               AND clinic_id = ?
               AND status = 'active'
               AND start_at = '2026-09-15 19:37:13'
               AND end_at = '2027-10-15 19:37:13'
               AND updated_at = ?",
            [$newUpdatedAt, $subscriptionId, $clinicId, $expectedUpdatedAt]
        );

        if ($db->affectedRows() !== 1) {
            throw new \RuntimeException('Expected exactly 1 affected row; got ' . $db->affectedRows());
        }

        // 4. Required audit record in same transaction
        $auditService = new \App\Services\ControlPlaneAuditService(new \App\Models\ControlPlaneAuditModel($db));
        if (!$auditService->logEvent('subscription_repair_utc_mismatch', [
            'actor_user_id' => $operatorUserId,
            'route' => 'cli/subscription:repair-tz',
            'metadata' => [
                'clinic_id' => $clinicId,
                'subscription_id' => $subscriptionId,
                'previous' => $row,
                'repaired' => [
                    'start_at' => '2026-09-15 16:37:13',
                    'end_at' => '2027-10-15 16:37:13',
                    'updated_at' => $newUpdatedAt,
                ],
                'reason' => $reason,
            ],
        ], true)) {
            throw new \RuntimeException('Failed to insert repair audit record.');
        }

        // 5. Checked commit status
        if ($db->transStatus() === false || !$db->transCommit()) {
            throw new \RuntimeException('Database transaction commit failed.');
        }

        return EXIT_SUCCESS;
    } catch (\Throwable $e) {
        if ($db->transDepth > 0) {
            $db->transRollback();
        }
        $this->logger->error('Repair aborted and rolled back: ' . $e->getMessage());
        return EXIT_ERROR;
    }
}
```

---

### Non-Executable Procedural Reference Pseudocode (UNEXECUTED)

> [!CAUTION]
> **ILLUSTRATIVE PSEUDOCODE ONLY - NON-EXECUTABLE (REFERENCE ONLY)**
> In MySQL and MariaDB, DDL statements (`CREATE TABLE`) cause an **implicit commit**. Any isolated backup table creation must therefore occur *outside* and *before* the atomic transaction begins.
> Furthermore, raw SQL script execution cannot enforce inline conditional aborts (`IF ROW_COUNT() <> 1 THEN ROLLBACK`) without stored procedure procedural wrappers. The snippets below represent illustrative reference pseudocode demonstrating the required concurrency and integrity predicates; they must not be executed as raw scripts.

#### Step 1: Pre-Execution Verification & Evidence Capture
```sql
-- Observe actual current state (Production Snapshot Evidence observed 2026-09-15 19:40:16 UTC+3)
SELECT id, clinic_id, status, start_at, end_at, updated_at
FROM clinic_subscriptions
WHERE id = 35 AND clinic_id = 26;
```
*Observed production evidence:*
- `id`: `35`
- `clinic_id`: `26`
- `status`: `'active'`
- `start_at`: `'2026-09-15 19:37:13'`
- `end_at`: `'2027-10-15 19:37:13'`
- `updated_at`: `'2026-09-15 19:40:16'` (recorded at operator extension)

#### Step 2: Isolated DDL Backup (Outside Transaction, Checked Existence)
```sql
-- Abort if backup table already exists to prevent accidental reuse:
-- MUST be executed separately prior to transaction due to DDL implicit commit in MySQL.
SELECT COUNT(*) INTO @table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = '_backup_sub35_20260915_194016';

-- If @table_exists > 0 -> ABORT PROCEDURE IMMEDIATELY

CREATE TABLE _backup_sub35_20260915_194016 AS
SELECT * FROM clinic_subscriptions WHERE id = 35 AND clinic_id = 26;

-- Verify exactly 1 row backed up:
SELECT COUNT(*) FROM _backup_sub35_20260915_194016;
-- Result MUST be 1. If not 1 -> DROP TABLE and ABORT PROCEDURE.
```

#### Step 3: Transactional Mutation with Explicit Predicates, ROW_COUNT Assertion, & Audit
```sql
-- Operator User ID must be explicitly supplied (e.g., @operator_user_id := <authenticated_operator_id>)
-- Hardcoded ID 1 is strictly forbidden.

START TRANSACTION;

-- Lock and inspect row version
SELECT id, clinic_id, status, start_at, end_at, updated_at
FROM clinic_subscriptions
WHERE id = 35 AND clinic_id = 26
FOR UPDATE;

-- Update strictly matching all columns including updated_at
UPDATE clinic_subscriptions
SET start_at = '2026-09-15 16:37:13',
    end_at   = '2027-10-15 16:37:13',
    updated_at = UTC_TIMESTAMP()
WHERE id = 35
  AND clinic_id = 26
  AND status = 'active'
  AND start_at = '2026-09-15 19:37:13'
  AND end_at = '2027-10-15 19:37:13'
  AND updated_at = '2026-09-15 19:40:16';

-- Enforce exactly one affected row; abort on stale state or concurrent modification:
-- If ROW_COUNT() <> 1 THEN ROLLBACK; SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stale state: row count != 1';

-- Record audit log within the same transaction
INSERT INTO control_plane_audits (
    actor_user_id,
    event_type,
    metadata_json,
    created_at
) VALUES (
    @operator_user_id,
    'subscription_repair_utc_mismatch',
    '{"clinic_id":26,"subscription_id":35,"old_start_at":"2026-09-15 19:37:13","new_start_at":"2026-09-15 16:37:13","old_end_at":"2027-10-15 19:37:13","new_end_at":"2027-10-15 16:37:13","old_updated_at":"2026-09-15 19:40:16","reason":"correct_3h_local_offset"}',
    UTC_TIMESTAMP()
);

-- Enforce audit insertion success:
-- If ROW_COUNT() <> 1 THEN ROLLBACK; SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Failed to insert audit record';

COMMIT;
```

#### Step 4: Proposed Rollback Procedure (If Abort Needed)
```sql
-- Rollback requires fresh current-state verification, row lock, ROW_COUNT check, and separate audit record:

START TRANSACTION;

SELECT id, clinic_id, status, start_at, end_at, updated_at
FROM clinic_subscriptions
WHERE id = 35 AND clinic_id = 26
FOR UPDATE;

-- Rollback strictly matching the repaired values
UPDATE clinic_subscriptions
SET start_at = '2026-09-15 19:37:13',
    end_at   = '2027-10-15 19:37:13',
    updated_at = UTC_TIMESTAMP()
WHERE id = 35
  AND clinic_id = 26
  AND status = 'active'
  AND start_at = '2026-09-15 16:37:13'
  AND end_at = '2027-10-15 16:37:13';

-- Enforce exactly one affected row:
-- If ROW_COUNT() <> 1 THEN ROLLBACK; SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Rollback failed: row count != 1';

INSERT INTO control_plane_audits (
    actor_user_id,
    event_type,
    metadata_json,
    created_at
) VALUES (
    @operator_user_id,
    'subscription_repair_rollback',
    '{"clinic_id":26,"subscription_id":35,"restored_start_at":"2026-09-15 19:37:13","restored_end_at":"2027-10-15 19:37:13"}',
    UTC_TIMESTAMP()
);

-- If ROW_COUNT() <> 1 THEN ROLLBACK;

COMMIT;
```

---

## 6. Mandatory Concurrency Token Specification

To prevent stale submissions, double-clicks, or handcrafted requests from bypassing optimistic concurrency boundaries, all lifecycle modification endpoints enforce a mandatory concurrency contract:

| Operation | Endpoint | Mandatory Concurrency Tokens | Nullable Sentinel | Failure Machine Code |
| :--- | :--- | :--- | :--- | :--- |
| **Extend Subscription** | `POST /controlplane/clinics/{id}/subscription/extend` | `expected_end_at`, `expected_updated_at` | `'__NULL__'` for `NULL end_at` | `missing_concurrency_token` |
| **Cancel Subscription** | `POST /controlplane/clinics/{id}/subscription/cancel` | `expected_updated_at` | `'__NULL__'` for `NULL updated_at` | `missing_concurrency_token` |
| **Update Dates** | `POST /controlplane/clinics/{id}/subscription/update-dates` | `expected_start_at`, `expected_end_at`, `expected_updated_at` | `'__NULL__'` for `NULL end_at` | `missing_concurrency_token` |
| **Approve Plan Request** | `POST /controlplane/plan-requests/{id}/approve` | `expected_requested_plan_id`, `expected_requested_duration` | N/A | `missing_concurrency_token` |

### Rules & Mechanics:
1. **Pre-Transaction Fail-Closed:** Missing, null, or empty string tokens are rejected immediately prior to `$db->transBegin()`, resulting in zero database writes, zero audit rows, and zero open transaction depth.
2. **Nullable Sentinel (`__NULL__`):** Where a database timestamp column can legitimately be SQL `NULL` (such as `end_at` for an open-ended lifetime subscription), clients submit the explicit string `'__NULL__'` (or `'NULL'`). `ControlPlaneSubscriptionService::matchesNullableToken()` evaluates `'__NULL__'` as matching `$actualDbValue === null`, distinguishing genuine NULL state from missing form inputs.
3. **Locked Row Re-verification:** All tokens are strictly verified against the locked row under `SELECT ... FOR UPDATE` before any write occurs. If any token does not match, the transaction rolls back immediately and returns machine code `stale_state`.
4. **Sanitized User Feedback:** Controller endpoints translate machine outcome codes to localized language strings (`UI.subscriptionCode_*`) in English and Arabic, preventing raw exception text from leaking to operators.

