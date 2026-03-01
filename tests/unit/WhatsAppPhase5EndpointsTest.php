<?php

namespace Tests\Unit;

use App\Models\ClinicNotificationChannelModel;
use App\Models\PatientModel;
use App\Models\PlatformSecretModel;
use App\Models\WhatsAppTenantPolicyModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class WhatsAppPhase5EndpointsTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testSettingsTestWhatsAppChannelRequiresAjax(): void
    {
        $response = $this->postWithCsrf(
            '/settings/testWhatsAppChannel',
            [],
            ['active_clinic_id' => 1]
        );

        $response->assertStatus(400);
        $response->assertJSONFragment([
            'success' => false,
            'message' => 'Invalid request.',
        ]);
    }

    public function testSettingsTestWhatsAppChannelReturnsNotConfiguredWhenChannelMissing(): void
    {
        $response = $this->postWithCsrf(
            '/settings/testWhatsAppChannel',
            [],
            ['active_clinic_id' => 99999999],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => false,
            'message' => 'WhatsApp channel is not configured for this clinic.',
        ]);
    }

    public function testSettingsGetChannelConfigReturnsWhatsaAppTemplateDefaults(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for getChannelConfig test.');
        }

        $session = session();
        $originalClinicId = $session->get('active_clinic_id');
        $session->set('active_clinic_id', $clinicId);

        $channelModel = new ClinicNotificationChannelModel();
        $originalConfig = $channelModel->getConfig((int) $clinicId, 'whatsapp');
        try {
            $channelModel->setConfig((int) $clinicId, 'whatsapp', [
                'phone_number_id' => '1234567890',
                'api_version' => 'v22.0',
            ]);
        } finally {
            $session->set('active_clinic_id', $originalClinicId);
        }

        try {
            $response = $this->postWithCsrf(
                '/settings/getChannelConfig',
                ['channel_type' => 'whatsapp'],
                ['active_clinic_id' => $clinicId],
                ['X-Requested-With' => 'XMLHttpRequest']
            );

            $response->assertStatus(200);
            $response->assertJSONFragment(['success' => true]);

            $json = json_decode((string) $response->getJSON(), true);
            $this->assertIsArray($json);
            $this->assertSame('1234567890', (string) ($json['config']['phone_number_id'] ?? ''));
            $this->assertSame('ar', (string) ($json['config']['fallback_template']['language'] ?? ''));
            $this->assertSame(
                'تذكير من العيادة: لديكم موعد بتاريخ {{appointment_date}} الساعة {{appointment_time}}. للمتابعة يرجى الرد على هذه الرسالة.',
                (string) ($json['config']['message_templates']['appointment_reminder'] ?? '')
            );
        } finally {
            $session->set('active_clinic_id', $clinicId);
            try {
                if (is_array($originalConfig)) {
                    $channelModel->setConfig((int) $clinicId, 'whatsapp', $originalConfig);
                } else {
                    $this->db->table('clinic_notification_channels')
                        ->where('clinic_id', $clinicId)
                        ->where('channel_type', 'whatsapp')
                        ->delete();
                }
            } finally {
                $session->set('active_clinic_id', $originalClinicId);
            }
        }
    }

    public function testPatientWhatsAppIdentityRequiresAjax(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for patient WhatsApp identity test.');
        }

        $patientId = $this->createTemporaryPatient((int) $clinicId);
        try {
            $response = $this->postWithCsrf(
                '/patient/' . $patientId . '/whatsapp-identity',
                ['wa_number' => '+970599123456'],
                ['active_clinic_id' => $clinicId]
            );

            $response->assertStatus(400);
            $response->assertJSONFragment([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
        } finally {
            $this->deleteTemporaryPatient((int) $patientId);
        }
    }

    public function testPatientWhatsAppIdentityRejectsInvalidNumber(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for patient WhatsApp identity invalid-number test.');
        }

        $patientId = $this->createTemporaryPatient((int) $clinicId);
        try {
            $response = $this->postWithCsrf(
                '/patient/' . $patientId . '/whatsapp-identity',
                ['wa_number' => '123'],
                ['active_clinic_id' => $clinicId],
                ['X-Requested-With' => 'XMLHttpRequest']
            );

            $response->assertStatus(200);
            $response->assertJSONFragment([
                'success' => false,
            ]);
            $response->assertJSONFragment([
                'message' => 'Enter a valid WhatsApp number in international format (example: +970599999999).',
            ]);
        } finally {
            $this->deleteTemporaryPatient((int) $patientId);
        }
    }

    public function testPatientWhatsAppIdentityNormalizesAndSavesValidNumber(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for patient WhatsApp identity save test.');
        }

        $patientId = $this->createTemporaryPatient((int) $clinicId);
        try {
            $response = $this->postWithCsrf(
                '/patient/' . $patientId . '/whatsapp-identity',
                ['wa_number' => '(970) 599-350-450'],
                ['active_clinic_id' => $clinicId],
                ['X-Requested-With' => 'XMLHttpRequest']
            );

            $response->assertStatus(200);
            $response->assertJSONFragment([
                'success' => true,
                'wa_number' => '+970599350450',
            ]);
        } finally {
            $this->deleteTemporaryPatient((int) $patientId);
            $this->db->table('patient_whatsapp')
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $patientId)
                ->delete();
        }
    }

    public function testControlPlaneRotateSecretRejectsKeyOutsideAllowlist(): void
    {
        $response = $this->postWithCsrf(
            '/controlplane/whatsapp/rotate-secret',
            [
                'secret_key' => 'invalid_key',
                'new_value' => 'x',
                'rotation_note' => 'phpunit',
            ],
            ['user_id' => 1]
        );

        $response->assertStatus(302);
    }

    public function testControlPlaneRotateSecretAllowsAllowlistedKey(): void
    {
        $secretModel = new PlatformSecretModel();
        $key = 'wa_verify_token';
        $original = $secretModel->getSecret($key);
        $newValue = 'phpunit-verify-token-' . substr(bin2hex(random_bytes(4)), 0, 8);

        try {
            $response = $this->postWithCsrf(
                '/controlplane/whatsapp/rotate-secret',
                [
                    'secret_key' => $key,
                    'new_value' => $newValue,
                    'rotation_note' => 'phpunit',
                ],
                ['user_id' => 1]
            );

            $response->assertStatus(302);
            $this->assertSame($newValue, $secretModel->getSecret($key));
        } finally {
            if (is_string($original) && $original !== '') {
                $secretModel->setSecret($key, $original, 1, 'phpunit-restore');
            }
        }
    }

    public function testControlPlanePolicyAndEmergencyPauseUpsertTenantPolicy(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for control-plane policy test.');
        }

        $policyModel = new WhatsAppTenantPolicyModel();
        $original = $policyModel->getByClinicForControlPlane((int) $clinicId);

        try {
            $updateResponse = $this->postWithCsrf(
                '/controlplane/whatsapp/policy',
                [
                    'clinic_id' => $clinicId,
                    'daily_limit' => 55,
                    'monthly_limit' => 1111,
                    'per_patient_daily_limit' => 4,
                    'per_hour_limit' => 22,
                    'reminder_lead_time_minutes' => 60,
                    'suspended' => 0,
                ],
                ['user_id' => 1]
            );

            $updateResponse->assertStatus(302);

            $afterUpdate = $policyModel->getByClinicForControlPlane((int) $clinicId);
            $this->assertIsArray($afterUpdate);
            $this->assertSame(55, (int) $afterUpdate['daily_limit']);
            $this->assertSame(1111, (int) $afterUpdate['monthly_limit']);

            $pauseResponse = $this->postWithCsrf(
                '/controlplane/whatsapp/emergency-pause',
                [
                    'clinic_id' => $clinicId,
                    'suspend' => 1,
                    'suspended_reason' => 'phpunit pause',
                ],
                ['user_id' => 1]
            );

            $pauseResponse->assertStatus(302);

            $afterPause = $policyModel->getByClinicForControlPlane((int) $clinicId);
            $this->assertIsArray($afterPause);
            $this->assertSame(1, (int) $afterPause['suspended']);
            $this->assertSame('phpunit pause', (string) $afterPause['suspended_reason']);
        } finally {
            if (is_array($original) && !empty($original)) {
                $policyModel->upsertForControlPlane((int) $clinicId, [
                    'daily_limit' => (int) ($original['daily_limit'] ?? 50),
                    'monthly_limit' => (int) ($original['monthly_limit'] ?? 1000),
                    'per_patient_daily_limit' => (int) ($original['per_patient_daily_limit'] ?? 3),
                    'per_hour_limit' => (int) ($original['per_hour_limit'] ?? 20),
                    'reminder_lead_time_minutes' => (int) ($original['reminder_lead_time_minutes'] ?? 1440),
                    'suspended' => (int) ($original['suspended'] ?? 0),
                    'suspended_reason' => $original['suspended_reason'] ?? null,
                ]);
            } else {
                $this->db->table('whatsapp_tenant_policies')->where('clinic_id', $clinicId)->delete();
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Config\Database::connect('tests');
    }

    private function findAnyClinicId(): int
    {
        $row = $this->db->table('clinics')->select('id')->orderBy('id', 'ASC')->get(1)->getRowArray();
        return (int) ($row['id'] ?? 0);
    }

    private function createTemporaryPatient(int $clinicId): int
    {
        $patientModel = new PatientModel();
        $session = session();
        $originalClinicId = $session->get('active_clinic_id');
        $session->set('active_clinic_id', $clinicId);

        try {
            $ok = $patientModel->insert([
                'first_name' => 'PHPUnit',
                'last_name' => 'WhatsApp',
                'email' => null,
                'phone' => '0599123456',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'city' => null,
                'status' => 'active',
            ]);
            if ($ok === false) {
                $errors = $patientModel->errors();
                $this->fail('Failed to create temporary patient: ' . json_encode($errors));
            }
            return (int) $patientModel->getInsertID();
        } finally {
            $session->set('active_clinic_id', $originalClinicId);
        }
    }

    private function deleteTemporaryPatient(int $patientId): void
    {
        $this->db->table('patients')->where('id', $patientId)->delete();
    }

    private function withPhase5TestRoutes(): self
    {
        $this->withRoutes([
            ['POST', 'settings/getChannelConfig', 'Settings::getChannelConfig'],
            ['POST', 'settings/testWhatsAppChannel', 'Settings::testWhatsAppChannel'],
            ['POST', 'patient/(:num)/whatsapp-identity', 'Patient::saveWhatsAppIdentity/$1'],
            ['POST', 'controlplane/whatsapp/rotate-secret', 'ControlPlane\WhatsApp::rotateSecret'],
            ['POST', 'controlplane/whatsapp/policy', 'ControlPlane\WhatsApp::updatePolicy'],
            ['POST', 'controlplane/whatsapp/emergency-pause', 'ControlPlane\WhatsApp::setEmergencyPause'],
        ]);

        return $this;
    }

    private function postWithCsrf(string $path, array $data, array $session = [], array $headers = [])
    {
        $security = service('security');
        $securityConfig = config('Security');
        $tokenName = (string) $securityConfig->tokenName;
        $tokenHash = (string) $security->getHash();
        $cookieName = (string) $securityConfig->cookieName;

        $mergedData = array_merge($data, [$tokenName => $tokenHash]);
        $mergedHeaders = array_merge($headers, [
            'Cookie' => $cookieName . '=' . $tokenHash,
            'X-CSRF-TOKEN' => $tokenHash,
        ]);

        return $this
            ->withPhase5TestRoutes()
            ->withSession($session)
            ->withHeaders($mergedHeaders)
            ->post($path, $mergedData);
    }
}
