<?php

namespace App\Services\NotificationDrivers;

use App\Models\ClinicUserModel;
use App\Models\PatientModel;
use App\Models\UserModel;

class EmailDriver
{
    protected ClinicUserModel $clinicUserModel;
    protected PatientModel $patientModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->clinicUserModel = new ClinicUserModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
    }

    /**
     * @param array $notification Row from notifications table
     * @param int $clinicId
     * @param array $config Decrypted email channel config
     * @return array
     */
    public function send(array $notification, int $clinicId, array $config): array
    {
        $payload = json_decode($notification['payload_json'] ?? '{}', true) ?? [];
        $recipientEmail = $this->resolveRecipientEmail($notification, $clinicId);

        if (empty($recipientEmail)) {
            return [
                'notification_status' => 'blocked',
                'failure_reason' => ($notification['recipient_type'] ?? '') === 'patient'
                    ? 'PATIENT_EMAIL_MISSING'
                    : 'USER_EMAIL_MISSING',
                'attempt_status' => 'failed_permanent',
                'error_code' => 'RECIPIENT_EMAIL_MISSING',
                'error_message' => 'Recipient email address is missing or invalid in tenant scope.',
                'retryable' => false,
            ];
        }

        $emailService = \Config\Services::email();
        $emailService->initialize([
            'protocol' => 'smtp',
            'SMTPHost' => $config['smtp_host'] ?? '',
            'SMTPUser' => $config['smtp_user'] ?? '',
            'SMTPPass' => $config['smtp_pass'] ?? '',
            'SMTPPort' => (int) ($config['smtp_port'] ?? 587),
            'SMTPCrypto' => $config['smtp_crypto'] ?? 'tls',
            'mailType' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n",
        ]);

        $fromEmail = $config['smtp_from_email'] ?? ($config['smtp_user'] ?? '');
        $fromName = $config['smtp_from_name'] ?? 'Clinic Notification';

        if (empty($fromEmail)) {
            return [
                'notification_status' => 'failed',
                'failure_reason' => 'SMTP_FROM_ADDRESS_MISSING',
                'attempt_status' => 'failed_permanent',
                'error_code' => 'SMTP_FROM_ADDRESS_MISSING',
                'error_message' => 'SMTP from address is not configured.',
                'retryable' => false,
            ];
        }

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($recipientEmail);
        $emailService->setSubject($payload['subject'] ?? 'Notification');
        $emailService->setMessage($payload['body'] ?? ($payload['message'] ?? ''));

        try {
            if ($emailService->send()) {
                return [
                    'notification_status' => 'sent',
                    'failure_reason' => null,
                    'attempt_status' => 'sent',
                    'error_code' => null,
                    'error_message' => null,
                    'retryable' => false,
                ];
            }

            $debugger = $emailService->printDebugger(['headers']);
            $errorMsg = substr(strip_tags($debugger), 0, 255);

            return [
                'notification_status' => 'failed',
                'failure_reason' => 'SMTP_SEND_FAILED: ' . $errorMsg,
                'attempt_status' => 'failed',
                'error_code' => 'SMTP_SEND_FAILED',
                'error_message' => $errorMsg,
                'retryable' => false,
            ];
        } catch (\Throwable $e) {
            $msg = substr($e->getMessage(), 0, 200);

            return [
                'notification_status' => 'failed',
                'failure_reason' => 'SMTP_EXCEPTION: ' . $msg,
                'attempt_status' => 'failed',
                'error_code' => 'SMTP_EXCEPTION',
                'error_message' => $msg,
                'retryable' => false,
            ];
        }
    }

    private function resolveRecipientEmail(array $notification, int $clinicId): ?string
    {
        $type = $notification['recipient_type'] ?? '';
        $recipientId = (int) ($notification['recipient_id'] ?? 0);

        if ($type === 'user') {
            $user = $recipientId ? $this->userModel->find($recipientId) : null;
            if ($user && $this->clinicUserModel->isUserInClinic($clinicId, (int) $user['id'])) {
                return $user['email'] ?? null;
            }
            return null;
        }

        if ($type === 'patient') {
            $patient = $recipientId ? $this->patientModel->findByClinic($clinicId, $recipientId) : null;
            return $patient['email'] ?? null;
        }

        if ($type === 'external') {
            $address = trim((string) ($notification['recipient_address'] ?? ''));
            return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : null;
        }

        return null;
    }
}
