<?php

namespace App\Filters;

use App\Libraries\IonAuth;
use App\Models\ClinicUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiTenant implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if ($session->get('global_mode') === true) {
            return $this->error('tenant_plane_required', 'Tenant mode is required for this resource.', 403);
        }

        $clinicId = (int) ($session->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $userId = (int) ((new IonAuth())->getUserId() ?: 0);
        if ($userId <= 0) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        $membership = (new ClinicUserModel())->getMembership($userId, $clinicId);
        if (! $membership) {
            $session->remove(['active_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);

            return $this->error('tenant_context_forbidden', 'Access denied to this clinic.', 403);
        }

        if ($session->get('active_clinic_role_id') != ($membership['role_id'] ?? null)) {
            $session->set('active_clinic_role_id', $membership['role_id'] ?? null);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function error(string $code, string $message, int $status): ResponseInterface
    {
        return service('response')
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
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
