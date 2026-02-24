<?php

namespace App\Services\NotificationDrivers;

class WhatsAppDriver
{
    /**
     * Phase 2 stub: fail closed until outbound MVP (Phase 3).
     */
    public function send(array $notification, int $clinicId, array $context = []): array
    {
        return [
            'notification_status' => 'failed',
            'failure_reason' => 'WHATSAPP_DRIVER_NOT_IMPLEMENTED',
            'attempt_status' => 'failed_permanent',
            'error_code' => 'WHATSAPP_DRIVER_NOT_IMPLEMENTED',
            'error_message' => 'WhatsApp outbound transport is not implemented yet (Phase 3).',
            'retryable' => false,
        ];
    }
}
