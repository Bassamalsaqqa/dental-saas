<?php

namespace App\Services;

use App\Libraries\IonAuth;
use App\Models\ControlPlaneAuditModel;

class ControlPlaneAuditService
{
    protected $auditModel;

    public function __construct()
    {
        $this->auditModel = new ControlPlaneAuditModel();
    }

    public function logEvent(string $eventType, array $context = []): void
    {
        try {
            $request = service('request');
            $ionAuth = new IonAuth();

            $actorUserId = $ionAuth->loggedIn() ? $ionAuth->getUserId() : null;
            $route = $context['route'] ?? $this->resolveRoute($request ? $request->getPath() : '');
            $method = $context['method'] ?? ($request ? $request->getMethod() : null);
            $metadata = $context['metadata'] ?? null;

            $data = [
                'actor_user_id' => $actorUserId,
                'event_type' => $eventType,
                'route' => $route,
                'method' => $method,
                'ip' => $request ? $request->getIPAddress() : null,
                'user_agent' => $request ? $request->getUserAgent()->getAgentString() : null,
                'metadata_json' => $metadata ? json_encode($metadata, JSON_UNESCAPED_SLASHES) : null,
            ];

            $this->auditModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'ControlPlaneAuditService failure: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function resolveRoute(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '/';
        }

        return '/' . ltrim($trimmed, '/');
    }
}
