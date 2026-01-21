<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LegacyApiObserveFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        $userId = $session->get('user_id') ?: 'none';
        $clinicId = $session->get('active_clinic_id') ?: 'none';
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $ip = $request->getIPAddress();
        $agent = $request->getUserAgent();
        $ua = $agent ? $agent->getAgentString() : '';
        $ua = str_replace('"', '\\"', $ua);

        log_message('info', "LEGACY_API_ACCESS route={$path} method={$method} user_id={$userId} clinic_id={$clinicId} ip={$ip} ua=\"{$ua}\"");

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op.
    }
}
