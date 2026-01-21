<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LegacyApiGateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $mode = $this->resolveMode();
        if ($mode !== 'deny') {
            return null;
        }

        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $ip = $request->getIPAddress();
        $agent = $request->getUserAgent();
        $ua = $agent ? $agent->getAgentString() : '';
        $ua = str_replace('"', '\\"', $ua);

        log_message('info', "LEGACY_API_GATE_DENY route={$path} method={$method} ip={$ip} ua=\"{$ua}\"");

        $response = service('response');
        return $response->setStatusCode(403)->setJSON([
            'error' => [
                'code' => 'legacy_api_disabled',
                'message' => 'Legacy API is disabled. Use /api/v1/*.'
            ]
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op.
    }

    private function resolveMode(): string
    {
        $mode = getenv('LEGACY_API_GATE_MODE');
        if ($mode) {
            return strtolower(trim($mode));
        }

        return (ENVIRONMENT === 'production') ? 'observe' : 'deny';
    }
}
