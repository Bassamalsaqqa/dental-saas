<?php

namespace App\Services;

use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\ControlPlaneAuditModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class ControlPlaneSubscriptionService
{
    protected ConnectionInterface $db;
    protected ClinicSubscriptionModel $subscriptionModel;
    protected ClinicModel $clinicModel;
    protected PlanModel $planModel;
    protected PlanRequestModel $planRequestModel;
    protected ControlPlaneAuditService $auditService;

    /**
     * Optional hook invoked right before commit during an active transaction.
     * Useful for deterministic testing of broken transStatus or simulated SQL errors.
     */
    public ?\Closure $beforeCommitHook = null;

    public const TOKEN_NULL = '__NULL__';

    /**
     * Compare a nullable database timestamp string against an expected concurrency token.
     *
     * @param string|null $actualDbValue Database value, or null
     * @param string|null $token Submitted token: '__NULL__' or 'NULL' represents explicit SQL NULL
     * @return bool True if token matches actualDbValue, false if mismatch
     */
    public static function matchesNullableToken(?string $actualDbValue, ?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        $trimmedToken = trim($token);
        if ($trimmedToken === self::TOKEN_NULL || strtoupper($trimmedToken) === 'NULL') {
            return $actualDbValue === null;
        }

        if ($actualDbValue === null) {
            return false;
        }

        return trim($actualDbValue) === $trimmedToken;
    }

    public function __construct(
        ?ConnectionInterface $db = null,
        ?ControlPlaneAuditService $auditService = null,
        ?ClinicSubscriptionModel $subscriptionModel = null,
        ?PlanModel $planModel = null,
        ?PlanRequestModel $planRequestModel = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->subscriptionModel = $subscriptionModel ?? (new ClinicSubscriptionModel($this->db))->withoutTenantScope();
        $this->clinicModel = new ClinicModel($this->db);
        $this->planModel = $planModel ?? new PlanModel($this->db);
        $this->planRequestModel = $planRequestModel ?? (new PlanRequestModel($this->db))->withoutTenantScope();
        $this->auditService = $auditService ?? new ControlPlaneAuditService(new ControlPlaneAuditModel($this->db));
    }

    /**
     * Start a new subscription for a clinic, superseding any existing active/paused subscriptions.
     */
    public function startSubscription(
        int $clinicId,
        int $planId,
        string $duration,
        int $actorUserId,
        ?string $route = '/controlplane/clinics/subscription/start'
    ): array {
        // Pre-validation (outside transaction, zero writes)
        if ($clinicId <= 0 || $planId <= 0 || $actorUserId <= 0) {
            return ['success' => false, 'code' => 'invalid_plan'];
        }

        $plan = $this->planModel->where('status', 'active')->find($planId);
        if (!$plan) {
            return ['success' => false, 'code' => 'invalid_plan'];
        }

        if (!in_array($duration, ['monthly', 'yearly', 'lifetime'], true)) {
            return ['success' => false, 'code' => 'invalid_duration'];
        }

        $startAt = ClinicSubscriptionModel::nowUtc();
        $endAt = ClinicSubscriptionModel::resolveEndAt($duration, $startAt);
        if ($endAt === false) {
            return ['success' => false, 'code' => 'invalid_duration'];
        }

        // Owned transaction contract
        if ($this->db->transDepth !== 0) {
            log_message('error', 'startSubscription rejected: caller has active transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Subscription workflow requires an independent transaction.',
            ];
        }

        if (!$this->db->transBegin()) {
            log_message('error', 'startSubscription failed: could not begin transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Could not begin transaction.',
            ];
        }

        try {
            // Lock clinic record
            $clinicRow = $this->db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinicRow) {
                throw new \RuntimeException('CLINIC_NOT_FOUND: Clinic not found.');
            }

            // Re-validate plan under lock to guard concurrent plan deactivation
            $lockedPlan = $this->db->query('SELECT id, status FROM plans WHERE id = ? FOR UPDATE', [$planId])->getRowArray();
            if (!$lockedPlan || ($lockedPlan['status'] ?? '') !== 'active') {
                throw new \RuntimeException('INVALID_PLAN: Plan is no longer active.');
            }

            // Lock existing active/paused subscriptions
            $activeSubs = $this->db->query(
                "SELECT id, status FROM clinic_subscriptions WHERE clinic_id = ? AND status IN ('active', 'paused') FOR UPDATE",
                [$clinicId]
            )->getResultArray();

            foreach ($activeSubs as $currentSub) {
                $canceled = $this->subscriptionModel->update($currentSub['id'], [
                    'status' => 'canceled',
                    'canceled_at' => $startAt,
                    'end_at' => $startAt,
                    'canceled_reason' => 'superseded_by_new_subscription',
                ]);
                if ($canceled === false) {
                    throw new \RuntimeException('SUBSCRIPTION_UPDATE_FAILED: Failed to cancel existing subscription.');
                }
            }

            $newSubId = $this->subscriptionModel->insert([
                'clinic_id' => $clinicId,
                'plan_id' => $planId,
                'status' => 'active',
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);
            if (!$newSubId) {
                throw new \RuntimeException('SUBSCRIPTION_INSERT_FAILED: Failed to create new subscription.');
            }

            // Required fail-closed audit logging
            if (!$this->auditService->logEvent('subscription_start', [
                'actor_user_id' => $actorUserId,
                'route' => $route,
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'plan_id' => $planId,
                    'duration' => $duration,
                    'subscription_id' => (int) $newSubId,
                ],
            ], true)) {
                throw new \RuntimeException('AUDIT_LOG_FAILED: Failed to record subscription audit.');
            }

            if ($this->beforeCommitHook !== null) {
                ($this->beforeCommitHook)($this->db);
            }

            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return [
                'success' => true,
                'code' => 'started',
                'subscription_id' => (int) $newSubId,
            ];
        } catch (\Throwable $e) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            log_message('error', 'startSubscription failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => $this->resolveErrorCode($e),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extend an active subscription by duration, computing end date strictly from the locked row.
     */
    public function extendSubscription(
        int $clinicId,
        string $duration,
        int $actorUserId,
        ?string $expectedEndAt = null,
        ?string $expectedUpdatedAt = null,
        ?string $route = '/controlplane/clinics/subscription/extend'
    ): array {
        if (!in_array($duration, ['monthly', 'yearly'], true)) {
            return ['success' => false, 'code' => 'invalid_duration'];
        }

        // Mandatory concurrency tokens
        if ($expectedEndAt === null || trim($expectedEndAt) === '' || $expectedUpdatedAt === null || trim($expectedUpdatedAt) === '') {
            return ['success' => false, 'code' => 'missing_concurrency_token'];
        }

        if ($this->db->transDepth !== 0) {
            log_message('error', 'extendSubscription rejected: caller has active transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Subscription workflow requires an independent transaction.',
            ];
        }

        if (!$this->db->transBegin()) {
            log_message('error', 'extendSubscription failed: could not begin transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Could not begin transaction.',
            ];
        }

        try {
            $clinicRow = $this->db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinicRow) {
                throw new \RuntimeException('CLINIC_NOT_FOUND: Clinic not found.');
            }

            // Lock latest active subscription
            $lockedSub = $this->db->query(
                "SELECT id, clinic_id, status, start_at, end_at, updated_at FROM clinic_subscriptions WHERE clinic_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1 FOR UPDATE",
                [$clinicId]
            )->getRowArray();

            if (!$lockedSub || ($lockedSub['status'] ?? '') !== 'active') {
                throw new \RuntimeException('NO_ACTIVE_SUBSCRIPTION: No active subscription to extend.');
            }

            // Mandatory concurrency token verification against locked row
            if (!self::matchesNullableToken($lockedSub['end_at'] ?? null, $expectedEndAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription end date has changed.');
            }
            if (!self::matchesNullableToken($lockedSub['updated_at'] ?? null, $expectedUpdatedAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription was updated concurrently.');
            }

            // Recompute end_at strictly from locked row
            $base = $lockedSub['end_at'] ?? ClinicSubscriptionModel::nowUtc();
            $newEndAt = ClinicSubscriptionModel::resolveEndAt($duration, $base);
            if ($newEndAt === false) {
                throw new \RuntimeException('INVALID_DURATION: Failed to resolve extended subscription end date.');
            }

            $updated = $this->subscriptionModel->update($lockedSub['id'], [
                'end_at' => $newEndAt,
            ]);
            if ($updated === false) {
                throw new \RuntimeException('SUBSCRIPTION_UPDATE_FAILED: Failed to update subscription end date.');
            }

            if (!$this->auditService->logEvent('subscription_extend', [
                'actor_user_id' => $actorUserId,
                'route' => $route,
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'subscription_id' => (int) $lockedSub['id'],
                    'duration' => $duration,
                    'new_end_at' => $newEndAt,
                ],
            ], true)) {
                throw new \RuntimeException('AUDIT_LOG_FAILED: Failed to record extension audit.');
            }

            if ($this->beforeCommitHook !== null) {
                ($this->beforeCommitHook)($this->db);
            }

            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return [
                'success' => true,
                'code' => 'extended',
                'new_end_at' => $newEndAt,
            ];
        } catch (\Throwable $e) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            log_message('error', 'extendSubscription failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => $this->resolveErrorCode($e),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel an active or paused subscription.
     */
    public function cancelSubscription(
        int $clinicId,
        int $actorUserId,
        ?string $reason = 'manual_cancel',
        ?string $expectedUpdatedAt = null,
        ?string $route = '/controlplane/clinics/subscription/cancel'
    ): array {
        // Mandatory concurrency token
        if ($expectedUpdatedAt === null || trim($expectedUpdatedAt) === '') {
            return ['success' => false, 'code' => 'missing_concurrency_token'];
        }

        if ($this->db->transDepth !== 0) {
            log_message('error', 'cancelSubscription rejected: caller has active transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Subscription workflow requires an independent transaction.',
            ];
        }

        if (!$this->db->transBegin()) {
            log_message('error', 'cancelSubscription failed: could not begin transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Could not begin transaction.',
            ];
        }

        try {
            $clinicRow = $this->db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinicRow) {
                throw new \RuntimeException('CLINIC_NOT_FOUND: Clinic not found.');
            }

            $lockedSub = $this->db->query(
                "SELECT id, clinic_id, status, start_at, end_at, updated_at FROM clinic_subscriptions WHERE clinic_id = ? AND status IN ('active', 'paused') ORDER BY id DESC LIMIT 1 FOR UPDATE",
                [$clinicId]
            )->getRowArray();

            if (!$lockedSub || !in_array(($lockedSub['status'] ?? ''), ['active', 'paused'], true)) {
                throw new \RuntimeException('NO_CANCELLABLE_SUBSCRIPTION: No active or paused subscription to cancel.');
            }

            if (!self::matchesNullableToken($lockedSub['updated_at'] ?? null, $expectedUpdatedAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription was updated concurrently.');
            }

            $now = ClinicSubscriptionModel::nowUtc();
            $updated = $this->subscriptionModel->update($lockedSub['id'], [
                'status' => 'canceled',
                'canceled_at' => $now,
                'end_at' => $now,
                'canceled_reason' => $reason ?? 'manual_cancel',
            ]);
            if ($updated === false) {
                throw new \RuntimeException('SUBSCRIPTION_UPDATE_FAILED: Failed to cancel subscription.');
            }

            if (!$this->auditService->logEvent('subscription_cancel', [
                'actor_user_id' => $actorUserId,
                'route' => $route,
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'subscription_id' => (int) $lockedSub['id'],
                    'reason' => $reason ?? 'manual_cancel',
                ],
            ], true)) {
                throw new \RuntimeException('AUDIT_LOG_FAILED: Failed to record cancel audit.');
            }

            if ($this->beforeCommitHook !== null) {
                ($this->beforeCommitHook)($this->db);
            }

            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return [
                'success' => true,
                'code' => 'canceled',
            ];
        } catch (\Throwable $e) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            log_message('error', 'cancelSubscription failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => $this->resolveErrorCode($e),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update start and end dates of an active or paused subscription.
     */
    public function updateSubscriptionDates(
        int $clinicId,
        ?string $startAt,
        ?string $endAt,
        int $actorUserId,
        ?string $expectedStartAt = null,
        ?string $expectedEndAt = null,
        ?string $expectedUpdatedAt = null,
        ?string $route = '/controlplane/clinics/subscription/update-dates'
    ): array {
        // Mandatory concurrency tokens
        if (
            $expectedStartAt === null || trim($expectedStartAt) === ''
            || $expectedEndAt === null || trim($expectedEndAt) === ''
            || $expectedUpdatedAt === null || trim($expectedUpdatedAt) === ''
        ) {
            return ['success' => false, 'code' => 'missing_concurrency_token'];
        }

        $startTs = $startAt ? ClinicSubscriptionModel::parseUtcTimestamp($startAt) : null;
        $endTs = $endAt ? ClinicSubscriptionModel::parseUtcTimestamp($endAt) : null;

        if ($startAt !== null && $startTs === false) {
            return ['success' => false, 'code' => 'invalid_dates'];
        }
        if ($endAt !== null && $endTs === false) {
            return ['success' => false, 'code' => 'invalid_dates'];
        }
        if ($startTs !== null && $endTs !== null && $endTs <= $startTs) {
            return ['success' => false, 'code' => 'invalid_dates'];
        }

        if ($this->db->transDepth !== 0) {
            log_message('error', 'updateSubscriptionDates rejected: caller has active transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Subscription workflow requires an independent transaction.',
            ];
        }

        if (!$this->db->transBegin()) {
            log_message('error', 'updateSubscriptionDates failed: could not begin transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Could not begin transaction.',
            ];
        }

        try {
            $clinicRow = $this->db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinicRow) {
                throw new \RuntimeException('CLINIC_NOT_FOUND: Clinic not found.');
            }

            $lockedSub = $this->db->query(
                "SELECT id, clinic_id, status, start_at, end_at, updated_at FROM clinic_subscriptions WHERE clinic_id = ? AND status IN ('active', 'paused') ORDER BY id DESC LIMIT 1 FOR UPDATE",
                [$clinicId]
            )->getRowArray();

            if (!$lockedSub || !in_array(($lockedSub['status'] ?? ''), ['active', 'paused'], true)) {
                throw new \RuntimeException('NO_UPDATABLE_SUBSCRIPTION: No active or paused subscription to update.');
            }

            // Concurrency checks
            if (!self::matchesNullableToken($lockedSub['start_at'] ?? null, $expectedStartAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription start date has changed.');
            }
            if (!self::matchesNullableToken($lockedSub['end_at'] ?? null, $expectedEndAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription end date has changed.');
            }
            if (!self::matchesNullableToken($lockedSub['updated_at'] ?? null, $expectedUpdatedAt)) {
                throw new \RuntimeException('STALE_STATE: Subscription was updated concurrently.');
            }

            $finalStartAt = $startAt !== null ? $startAt : $lockedSub['start_at'];
            $finalEndAt = $endAt !== null ? $endAt : $lockedSub['end_at'];

            $finalStartTs = $finalStartAt ? ClinicSubscriptionModel::parseUtcTimestamp($finalStartAt) : null;
            $finalEndTs = $finalEndAt ? ClinicSubscriptionModel::parseUtcTimestamp($finalEndAt) : null;
            if ($finalStartTs !== null && $finalEndTs !== null && $finalEndTs <= $finalStartTs) {
                throw new \RuntimeException('INVALID_DATES: End date must be after start date.');
            }

            $updated = $this->subscriptionModel->update($lockedSub['id'], [
                'start_at' => $finalStartAt,
                'end_at' => $finalEndAt,
            ]);
            if ($updated === false) {
                throw new \RuntimeException('SUBSCRIPTION_UPDATE_FAILED: Failed to update subscription dates.');
            }

            if (!$this->auditService->logEvent('subscription_dates_update', [
                'actor_user_id' => $actorUserId,
                'route' => $route,
                'method' => 'POST',
                'metadata' => [
                    'clinic_id' => $clinicId,
                    'subscription_id' => (int) $lockedSub['id'],
                    'start_at' => $finalStartAt,
                    'end_at' => $finalEndAt,
                ],
            ], true)) {
                throw new \RuntimeException('AUDIT_LOG_FAILED: Failed to record dates update audit.');
            }

            if ($this->beforeCommitHook !== null) {
                ($this->beforeCommitHook)($this->db);
            }

            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return [
                'success' => true,
                'code' => 'dates_updated',
            ];
        } catch (\Throwable $e) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            log_message('error', 'updateSubscriptionDates failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => $this->resolveErrorCode($e),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Approve a pending plan request atomically, validating locked request and plan.
     */
    public function approvePlanRequest(
        int $requestId,
        int $actorUserId,
        ?int $expectedPlanId = null,
        ?string $expectedDuration = null,
        ?string $route = '/controlplane/plan-requests/approve'
    ): array {
        // Mandatory concurrency tokens
        if ($expectedPlanId === null || $expectedPlanId <= 0 || $expectedDuration === null || trim($expectedDuration) === '') {
            return ['success' => false, 'code' => 'missing_concurrency_token'];
        }

        $initialReq = $this->planRequestModel->find($requestId);
        if (!$initialReq || ($initialReq['status'] ?? '') !== 'pending') {
            return ['success' => false, 'code' => 'not_found'];
        }

        if ($this->db->transDepth !== 0) {
            log_message('error', 'approvePlanRequest rejected: caller has active transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Subscription workflow requires an independent transaction.',
            ];
        }

        if (!$this->db->transBegin()) {
            log_message('error', 'approvePlanRequest failed: could not begin transaction.');
            return [
                'success' => false,
                'code' => 'system_error',
                'error' => 'TRANSACTION_FAILED: Could not begin transaction.',
            ];
        }

        try {
            $lockedReq = $this->db->query(
                'SELECT id, status, clinic_id, requested_plan_id, requested_duration, updated_at FROM plan_requests WHERE id = ? FOR UPDATE',
                [$requestId]
            )->getRowArray();

            if (!$lockedReq || ($lockedReq['status'] ?? '') !== 'pending') {
                throw new \RuntimeException('REQUEST_NOT_PENDING: Plan request was already processed.');
            }

            if ((int) $lockedReq['requested_plan_id'] !== $expectedPlanId) {
                throw new \RuntimeException('STALE_STATE: Plan request plan changed concurrently.');
            }
            if (trim((string) ($lockedReq['requested_duration'] ?? '')) !== trim($expectedDuration)) {
                throw new \RuntimeException('STALE_STATE: Plan request duration changed concurrently.');
            }

            // Re-validate requested plan under lock
            $lockedPlan = $this->db->query(
                'SELECT id, status FROM plans WHERE id = ? FOR UPDATE',
                [(int) $lockedReq['requested_plan_id']]
            )->getRowArray();

            if (!$lockedPlan || ($lockedPlan['status'] ?? '') !== 'active') {
                throw new \RuntimeException('INVALID_PLAN: Requested plan is no longer active.');
            }

            $duration = (string) ($lockedReq['requested_duration'] ?? 'monthly');
            if (!in_array($duration, ['monthly', 'yearly', 'lifetime'], true)) {
                throw new \RuntimeException('INVALID_DURATION: Invalid requested duration.');
            }

            $startAt = ClinicSubscriptionModel::nowUtc();
            $endAt = ClinicSubscriptionModel::resolveEndAt($duration, $startAt);
            if ($endAt === false) {
                throw new \RuntimeException('INVALID_DURATION: Failed to resolve end date.');
            }

            $clinicId = (int) $lockedReq['clinic_id'];
            $clinicRow = $this->db->query('SELECT id FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinicRow) {
                throw new \RuntimeException('CLINIC_NOT_FOUND: Clinic not found.');
            }

            $activeSubs = $this->db->query(
                "SELECT id, status FROM clinic_subscriptions WHERE clinic_id = ? AND status IN ('active', 'paused') FOR UPDATE",
                [$clinicId]
            )->getResultArray();

            foreach ($activeSubs as $currentSub) {
                $canceled = $this->subscriptionModel->update($currentSub['id'], [
                    'status' => 'canceled',
                    'canceled_at' => $startAt,
                    'end_at' => $startAt,
                    'canceled_reason' => 'plan_change',
                ]);
                if ($canceled === false) {
                    throw new \RuntimeException('SUBSCRIPTION_UPDATE_FAILED: Failed to cancel existing subscription.');
                }
            }

            $newSubId = $this->subscriptionModel->insert([
                'clinic_id' => $clinicId,
                'plan_id' => (int) $lockedReq['requested_plan_id'],
                'status' => 'active',
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);
            if (!$newSubId) {
                throw new \RuntimeException('SUBSCRIPTION_INSERT_FAILED: Failed to insert approved subscription.');
            }

            $reqUpdated = $this->planRequestModel->update($requestId, [
                'status' => 'approved',
                'reviewed_by' => $actorUserId,
                'reviewed_at' => $startAt,
            ]);
            if ($reqUpdated === false) {
                throw new \RuntimeException('REQUEST_UPDATE_FAILED: Failed to update plan request status.');
            }

            if (!$this->auditService->logEvent('plan_request_approve', [
                'actor_user_id' => $actorUserId,
                'route' => $route,
                'method' => 'POST',
                'metadata' => [
                    'request_id' => $requestId,
                    'clinic_id' => $clinicId,
                    'subscription_id' => (int) $newSubId,
                    'duration' => $duration,
                ],
            ], true)) {
                throw new \RuntimeException('AUDIT_LOG_FAILED: Failed to record approval audit.');
            }

            if ($this->beforeCommitHook !== null) {
                ($this->beforeCommitHook)($this->db);
            }

            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new \RuntimeException('TRANSACTION_FAILED: Database commit failed.');
            }

            return [
                'success' => true,
                'code' => 'plan_request_approved',
                'subscription_id' => (int) $newSubId,
            ];
        } catch (\Throwable $e) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            log_message('error', 'approvePlanRequest failed: {message}', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'code' => $this->resolveErrorCode($e),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map internal exception message prefixes to user-safe machine outcome codes.
     */
    protected function resolveErrorCode(\Throwable $e): string
    {
        $msg = $e->getMessage();
        if (str_starts_with($msg, 'MISSING_CONCURRENCY_TOKEN')) {
            return 'missing_concurrency_token';
        }
        if (str_starts_with($msg, 'STALE_STATE')) {
            return 'stale_state';
        }
        if (str_starts_with($msg, 'INVALID_PLAN')) {
            return 'invalid_plan';
        }
        if (str_starts_with($msg, 'INVALID_DURATION')) {
            return 'invalid_duration';
        }
        if (str_starts_with($msg, 'INVALID_DATES')) {
            return 'invalid_dates';
        }
        if (
            str_starts_with($msg, 'CLINIC_NOT_FOUND')
            || str_starts_with($msg, 'NO_ACTIVE_SUBSCRIPTION')
            || str_starts_with($msg, 'NO_CANCELLABLE_SUBSCRIPTION')
            || str_starts_with($msg, 'NO_UPDATABLE_SUBSCRIPTION')
            || str_starts_with($msg, 'REQUEST_NOT_PENDING')
        ) {
            return 'not_found';
        }

        return 'system_error';
    }

    /**
     * Normalize date inputs from UI into canonical UTC strings with fail-closed DST gap and fold policies.
     */
    public function normalizeDateTime(?string $input, ?string $sourceTimezone = null): ?string
    {
        if ($input === null) {
            return null;
        }

        $input = trim($input);
        if ($input === '') {
            return null;
        }

        $tzName = $sourceTimezone ?: date_default_timezone_get();
        try {
            $sourceTz = new \DateTimeZone($tzName);
        } catch (\Throwable $e) {
            return null;
        }
        $utcTz = new \DateTimeZone('UTC');

        $formats = [
            '!Y-m-d\\TH:i:s',
            '!Y-m-d\\TH:i',
            '!Y-m-d H:i:s',
            '!Y-m-d H:i',
            '!Y-m-d',
            '!Y-m-d\\TH:i:s\\Z',
            '!Y-m-d\\TH:i:sP',
            '!Y-m-d H:i:sP',
        ];

        foreach ($formats as $fmt) {
            $isExplicitTz = str_contains($fmt, 'Z') || str_contains($fmt, 'P');
            $parseTz = $isExplicitTz ? $utcTz : $sourceTz;

            $dt = \DateTimeImmutable::createFromFormat($fmt, $input, $parseTz);
            if ($dt === false) {
                continue;
            }

            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }

            $testFmt = str_replace('!', '', $fmt);
            $formatted = $dt->format($testFmt);
            if ($formatted !== $input && strcasecmp($formatted, $input) !== 0) {
                continue;
            }

            // Ambiguous DST fold check
            if (!$isExplicitTz && $this->isAmbiguousDstFold($dt, $sourceTz)) {
                return null;
            }

            $year = (int) $dt->format('Y');
            if ($year < 1000 || $year > 9999) {
                return null;
            }

            return $dt->setTimezone($utcTz)->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function isAmbiguousDstFold(\DateTimeImmutable $dt, \DateTimeZone $tz): bool
    {
        $ts = $dt->getTimestamp();
        $transitions = $tz->getTransitions($ts - 86400, $ts + 86400);
        if (empty($transitions)) {
            return false;
        }

        $localStr = $dt->format('Y-m-d H:i:s');
        foreach ([-7200, -3600, -1800, 1800, 3600, 7200] as $delta) {
            $altTs = $ts + $delta;
            $altDt = (new \DateTimeImmutable('@' . $altTs))->setTimezone($tz);
            if ($altDt->format('Y-m-d H:i:s') === $localStr) {
                return true;
            }
        }

        return false;
    }

    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    public function getAuditService(): ControlPlaneAuditService
    {
        return $this->auditService;
    }
}
