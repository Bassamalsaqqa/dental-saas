<?php

namespace App\Controllers\AppApi\V1;

use App\Libraries\IonAuth;
use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\ClinicUserModel;
use App\Models\PlanModel;
use App\Services\ClinicService;
use App\Services\LocaleResolverService;
use App\Services\PermissionService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Foundation extends Controller
{
    private IonAuth $ionAuth;
    private ClinicService $clinicService;
    private ClinicUserModel $clinicUserModel;
    private ClinicModel $clinicModel;
    private ClinicSubscriptionModel $subscriptionModel;
    private LocaleResolverService $localeResolver;

    public function __construct()
    {
        $this->ionAuth = new IonAuth();
        $this->clinicService = new ClinicService();
        $this->clinicUserModel = new ClinicUserModel();
        $this->clinicModel = new ClinicModel();
        $this->subscriptionModel = new ClinicSubscriptionModel();
        $this->localeResolver = service('localeResolver');
        helper(['form', 'url']);
    }

    public function csrf(): ResponseInterface
    {
        return $this->ok([
            'csrf' => $this->csrfPayload(),
        ]);
    }

    public function login(): ResponseInterface
    {
        $identity = trim((string) $this->input('identity'));
        $password = (string) $this->input('password');
        $remember = filter_var($this->input('remember') ?? false, FILTER_VALIDATE_BOOL);

        $fieldErrors = [];
        if ($identity === '') {
            $fieldErrors['identity'] = ['Identity is required.'];
        }
        if ($password === '') {
            $fieldErrors['password'] = ['Password is required.'];
        }
        if ($fieldErrors !== []) {
            return $this->error('validation_failed', 'Please correct the highlighted fields.', 422, [
                'fields' => $fieldErrors,
            ]);
        }

        if (! $this->ionAuth->login($identity, $password, $remember)) {
            return $this->error('unauthenticated', 'Invalid credentials.', 401);
        }

        $userId = (int) $this->ionAuth->getUserId();
        $this->clinicService->clearContext();
        $memberships = $this->clinicService->getMemberships($userId);

        if (count($memberships) === 1) {
            $this->clinicService->setContext($memberships[0]);
        }

        return $this->ok($this->statePayload(), [
            'message' => 'Signed in.',
            'csrf' => $this->csrfPayload(),
        ]);
    }

    public function logout(): ResponseInterface
    {
        $this->ionAuth->logout();

        return $this->ok([
            'authenticated' => false,
        ], [
            'message' => 'Signed out.',
        ]);
    }

    public function bootstrap(): ResponseInterface
    {
        if (! $this->ionAuth->loggedIn()) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        return $this->ok($this->statePayload());
    }

    public function switchClinic(): ResponseInterface
    {
        if (! $this->ionAuth->loggedIn()) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        $clinicId = (int) ($this->input('clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->error('validation_failed', 'Please correct the highlighted fields.', 422, [
                'fields' => [
                    'clinic_id' => ['Clinic is required.'],
                ],
            ]);
        }

        $userId = (int) $this->ionAuth->getUserId();
        $membership = $this->clinicService->getMembership($userId, $clinicId);
        if (! $membership) {
            return $this->error('forbidden', 'You cannot access this clinic.', 403);
        }

        session()->remove('user_permissions');
        $this->clinicService->setContext($membership);
        session()->regenerate(false);

        return $this->ok($this->statePayload(), [
            'message' => 'Clinic selected.',
            'csrf' => $this->csrfPayload(),
        ]);
    }

    private function statePayload(): array
    {
        $user = $this->ionAuth->user()->row();
        $userId = $user ? (int) $user->id : 0;
        $memberships = $userId > 0 ? $this->clinicService->getMemberships($userId) : [];
        $activeClinicId = (int) (session()->get('active_clinic_id') ?? 0);
        $globalMode = session()->get('global_mode') === true;
        $activeClinic = $activeClinicId > 0 ? $this->clinicModel->find($activeClinicId) : null;
        $appState = $this->resolveAppState($memberships, $activeClinic);
        $locale = $this->resolveLocale();

        return [
            'app_state' => $globalMode ? 'controlplane_ready' : $appState,
            'user' => $this->userSummary($user),
            'clinics' => array_map(fn (array $membership): array => $this->clinicSummaryFromMembership($membership), $memberships),
            'active_clinic' => $this->clinicSummary($activeClinic),
            'session' => [
                'authenticated' => $this->ionAuth->loggedIn(),
                'global_mode' => $globalMode,
                'impersonating' => session()->get('is_impersonating') === true,
            ],
            'locale' => [
                'code' => $locale,
                'direction' => $this->localeResolver->isRtl($locale) ? 'rtl' : 'ltr',
                'available' => ['en', 'ar'],
            ],
            'permissions' => $this->permissionSummary($userId, $globalMode, $appState),
            'features' => $this->featureSummary($activeClinicId, $appState),
            'navigation' => [],
            'csrf' => $this->csrfPayload(),
        ];
    }

    private function resolveAppState(array $memberships, ?array $activeClinic): string
    {
        $activeClinicId = (int) (session()->get('active_clinic_id') ?? 0);

        if ($activeClinicId <= 0) {
            return $memberships === [] ? 'no_clinic' : 'clinic_required';
        }

        if (! $activeClinic || ($activeClinic['status'] ?? '') !== 'active') {
            return 'suspended';
        }

        $membership = $this->clinicUserModel->getMembership((int) $this->ionAuth->getUserId(), $activeClinicId);
        if (! $membership) {
            return $memberships === [] ? 'no_clinic' : 'clinic_required';
        }

        if (! $this->hasActiveSubscription($activeClinicId)) {
            return 'subscription_inactive';
        }

        return 'tenant_ready';
    }

    private function hasActiveSubscription(int $clinicId): bool
    {
        $subscription = $this->subscriptionModel->withoutTenantScope()->getCurrentSubscription($clinicId);
        if ($subscription) {
            $subscription = $this->subscriptionModel->withoutTenantScope()->markExpiredIfNeeded($subscription);
        }
        if (! $subscription || ($subscription['status'] ?? '') !== 'active') {
            return false;
        }

        $now = time();
        $startOk = empty($subscription['start_at']) || strtotime($subscription['start_at']) <= $now;
        $endOk = empty($subscription['end_at']) || strtotime($subscription['end_at']) > $now;

        return $startOk && $endOk;
    }

    private function permissionSummary(int $userId, bool $globalMode, string $appState): array
    {
        if ($userId <= 0 || ($appState !== 'tenant_ready' && ! $globalMode)) {
            return [];
        }

        try {
            $grouped = (new PermissionService())->getUserPermissions($userId);
            $summary = [];
            foreach ($grouped as $module => $actions) {
                foreach (array_keys($actions) as $action) {
                    $summary[$module . ':' . $action] = true;
                }
            }
            return $summary;
        } catch (\Throwable $e) {
            log_message('error', 'App API permission summary failed: ' . $e->getMessage());
            return [];
        }
    }

    private function featureSummary(int $clinicId, string $appState): array
    {
        if ($clinicId <= 0 || $appState !== 'tenant_ready') {
            return [];
        }

        try {
            $subscription = $this->subscriptionModel->withoutTenantScope()->getCurrentSubscription($clinicId);
            if (! $subscription || empty($subscription['plan_id'])) {
                return [];
            }

            $plan = (new PlanModel())->find((int) $subscription['plan_id']);
            if (! is_array($plan)) {
                return [];
            }

            return json_decode((string) ($plan['features_json'] ?? '{}'), true) ?: [];
        } catch (\Throwable $e) {
            log_message('error', 'App API feature summary failed: ' . $e->getMessage());
            return [];
        }
    }

    private function userSummary(?object $user): ?array
    {
        if (! $user) {
            return null;
        }

        $name = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        return [
            'id' => (int) $user->id,
            'name' => $name !== '' ? $name : (string) ($user->email ?? ''),
            'email' => (string) ($user->email ?? ''),
        ];
    }

    private function clinicSummary(?array $clinic): ?array
    {
        if (! $clinic) {
            return null;
        }

        return [
            'id' => (int) $clinic['id'],
            'name' => (string) ($clinic['name'] ?? ''),
            'status' => (string) ($clinic['status'] ?? ''),
        ];
    }

    private function clinicSummaryFromMembership(array $membership): array
    {
        return [
            'id' => (int) $membership['clinic_id'],
            'name' => (string) ($membership['clinic_name'] ?? ''),
            'status' => 'active',
            'role_id' => isset($membership['role_id']) ? (int) $membership['role_id'] : null,
        ];
    }

    private function resolveLocale(): string
    {
        $locale = strtolower((string) (session()->get('ui_locale') ?? session()->get('cp_ui_locale') ?? 'en'));
        return in_array($locale, ['en', 'ar'], true) ? $locale : 'en';
    }

    private function input(string $key)
    {
        $json = null;

        try {
            $json = $this->request->getJSON(true);
        } catch (\Throwable $e) {
            $json = null;
        }

        if (is_array($json) && array_key_exists($key, $json)) {
            return $json[$key];
        }

        return $this->request->getPost($key);
    }

    private function csrfPayload(): array
    {
        $security = config('Security');

        return [
            'header' => $security->headerName,
            'name' => csrf_token(),
            'hash' => csrf_hash(),
        ];
    }

    private function ok(array $data, array $meta = []): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $data,
            'meta' => $meta + ['request_id' => $this->requestId()],
        ]);
    }

    private function error(string $code, string $message, int $status, array $details = []): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => $details,
                ],
                'meta' => [
                    'request_id' => $this->requestId(),
                ],
            ]);
    }

    private function requestId(): string
    {
        return 'req_' . bin2hex(random_bytes(8));
    }
}
