<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ApiTokenModel;
use App\Models\ClinicUserModel;
use App\Models\ClinicModel;
use App\Services\PlanGuard;

class ApiV1AuthFilter implements FilterInterface
{
    private function isSearchEndpoint(RequestInterface $request): bool
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        return str_contains($path, 'api/v1/search');
    }

    private function resolveSessionBridgeContext(RequestInterface $request): array
    {
        $sessionConfig = config('Session');
        $cookieName = $sessionConfig->cookieName ?? 'ci_session';
        $sessionId = $request->getCookie($cookieName);
        if (empty($sessionId)) {
            return ['error' => api_error(401, 'unauthenticated', 'Authentication required.')];
        }

        $savePath = rtrim($sessionConfig->savePath ?? '', DIRECTORY_SEPARATOR);
        if ($savePath === '') {
            return ['error' => api_error(401, 'unauthenticated', 'Authentication required.')];
        }

        $sessionFile = $savePath . DIRECTORY_SEPARATOR . $cookieName . $sessionId;
        if (!is_file($sessionFile)) {
            return ['error' => api_error(401, 'unauthenticated', 'Authentication required.')];
        }

        $raw = file_get_contents($sessionFile);
        if ($raw === false) {
            return ['error' => api_error(401, 'unauthenticated', 'Authentication required.')];
        }

        $data = $this->decodeSessionData($raw);
        $userId = (int)($data['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['error' => api_error(401, 'unauthenticated', 'Authentication required.')];
        }

        $sessionClinicId = (int)($data['active_clinic_id'] ?? 0);
        $clinicIdHeader = $request->getHeaderLine('X-Clinic-Id');

        if (empty($clinicIdHeader)) {
            if ($sessionClinicId <= 0) {
                return ['error' => api_error(422, 'tenant_context_missing', 'X-Clinic-Id header is required.')];
            }
            return ['user_id' => $userId, 'clinic_id' => $sessionClinicId];
        }

        if (!is_numeric($clinicIdHeader)) {
            return ['error' => api_error(422, 'tenant_context_invalid', 'X-Clinic-Id must be an integer.')];
        }

        $clinicId = (int)$clinicIdHeader;
        if ($sessionClinicId > 0 && $clinicId !== $sessionClinicId) {
            return ['error' => api_error(403, 'tenant_context_forbidden', 'Access denied to this clinic.')];
        }

        return ['user_id' => $userId, 'clinic_id' => $clinicId];
    }

    private function decodeSessionData(string $data): array
    {
        $result = [];
        $offset = 0;
        $length = strlen($data);

        while ($offset < $length) {
            $pos = strpos($data, '|', $offset);
            if ($pos === false) {
                break;
            }
            $key = substr($data, $offset, $pos - $offset);
            $offset = $pos + 1;

            $serialized = substr($data, $offset);
            $value = @unserialize($serialized);
            if ($value === false && !str_starts_with($serialized, 'b:0;')) {
                break;
            }
            $result[$key] = $value;
            $offset += strlen(serialize($value));
        }

        return $result;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        \App\Libraries\ApiRuntimeContext::clear();
        helper('api');

        // 1. Bearer Authentication
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            $authHeader = $request->getServer('HTTP_AUTHORIZATION');
        }
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }

        $isSearchEndpoint = $this->isSearchEndpoint($request);
        $userId = null;
        $clinicId = null;

        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $rawToken = substr($authHeader, 7);
            $tokenModel = new ApiTokenModel();
            $userId = $tokenModel->validateToken($rawToken);

            if (!$userId) {
                return api_error(401, 'unauthenticated', 'Authentication required.');
            }

            // 2. Tenant Header Mandatory (X-Clinic-Id)
            $clinicIdHeader = $request->getHeaderLine('X-Clinic-Id');
            if (empty($clinicIdHeader)) {
                return api_error(422, 'tenant_context_missing', 'X-Clinic-Id header is required.');
            }

            if (!is_numeric($clinicIdHeader)) {
                return api_error(422, 'tenant_context_invalid', 'X-Clinic-Id must be an integer.');
            }

            $clinicId = (int)$clinicIdHeader;
        } elseif ($isSearchEndpoint) {
            $bridge = $this->resolveSessionBridgeContext($request);
            if (isset($bridge['error'])) {
                return $bridge['error'];
            }
            $userId = $bridge['user_id'];
            $clinicId = $bridge['clinic_id'];
        } else {
            return api_error(401, 'unauthenticated', 'Authentication required.');
        }

        // 3. Validate Clinic Existence
        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        if (!$clinic) {
            return api_error(422, 'tenant_context_invalid', 'Clinic not found.');
        }

        // 4. Tenant Membership Authorization
        $membershipModel = new ClinicUserModel();
        $membership = $membershipModel->where('user_id', $userId)
                                      ->where('clinic_id', $clinicId)
                                      ->where('status', 'active')
                                      ->first();

        if (!$membership) {
            return api_error(403, 'tenant_context_forbidden', 'Access denied to this clinic.');
        }

        if ($clinic['status'] !== 'active') {
            return api_error(403, 'tenant_context_forbidden', 'Clinic is suspended or inactive.');
        }

        // 5. Set Context (Stateless - Runtime Static)
        \App\Libraries\ApiRuntimeContext::setUserId($userId);
        \App\Libraries\ApiRuntimeContext::setClinicId($clinicId);

        // 6. Subscription Standing
        $planGuard = new PlanGuard();
        // PlanGuard returns decision array in API scope
        $decision = $planGuard->assertSubscriptionActive($clinicId);
        
        if (is_array($decision) && array_key_exists('allowed', $decision) && $decision['allowed'] === false) {
             return api_error(
                 $decision['status_code'] ?? 403,
                 $decision['code'] ?? 'forbidden',
                 $decision['message'] ?? 'Access denied.',
                 $decision['details'] ?? []
             );
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        \App\Libraries\ApiRuntimeContext::clear();
    }
}
