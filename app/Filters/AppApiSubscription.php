<?php

namespace App\Filters;

use App\Models\ClinicSubscriptionModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiSubscription implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $subscriptionModel = new ClinicSubscriptionModel();
        $subscription = $subscriptionModel->withoutTenantScope()->getCurrentSubscription($clinicId);
        if ($subscription) {
            $subscription = $subscriptionModel->withoutTenantScope()->markExpiredIfNeeded($subscription);
        }

        $valid = false;
        if ($subscription && ($subscription['status'] ?? '') === 'active') {
            $now = time();
            $startOk = empty($subscription['start_at']) || strtotime($subscription['start_at']) <= $now;
            $endOk = empty($subscription['end_at']) || strtotime($subscription['end_at']) > $now;
            $valid = $startOk && $endOk;
        }

        if (! $valid) {
            return $this->error('subscription_inactive', 'Subscription required or expired.', 403);
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
