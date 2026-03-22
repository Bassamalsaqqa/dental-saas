<?php

namespace Tests\Unit;

use App\Models\ClinicNotificationChannelModel;
use App\Models\PatientModel;
use App\Models\PlatformSecretModel;
use App\Models\SettingsModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\WhatsAppTemplateRegistryService;
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
        $secretModel = new PlatformSecretModel();
        $settingsModel = new SettingsModel();
        $originalConfig = $channelModel->getConfig((int) $clinicId, 'whatsapp');
        $originalPhoneNumberId = $secretModel->getSecret('wa_phone_number_id');
        $originalTriggers = $settingsModel->getGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TEMPLATES, null);
        $originalFallback = $settingsModel->getGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_FALLBACK_TEMPLATE, null);

        try {
            $channelModel->setConfig((int) $clinicId, 'whatsapp', [
                'api_version' => 'v22.0',
            ]);
            $secretModel->setSecret('wa_phone_number_id', '1234567890', 1, 'phpunit');
            $settingsModel->setGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TEMPLATES, [
                'appointment_reminder' => [
                    'name' => 'appointment_reminder_ar_v1',
                    'language' => 'ar',
                    'header_param_key' => 'clinic_name',
                    'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time', 'clinic_name'],
                    'button_type' => '',
                    'button_index' => '0',
                    'button_param_key' => '',
                ],
            ], 'json', 'phpunit');
            $settingsModel->setGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_FALLBACK_TEMPLATE, [
                'name' => 'general_reengagement_ar',
                'language' => 'ar',
                'components' => [],
            ], 'json', 'phpunit');
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
            $this->assertTrue((bool) ($json['config']['managed_by_control_plane'] ?? false));
            $this->assertSame('ar', (string) ($json['config']['fallback_template']['language'] ?? ''));
            $this->assertSame('general_reengagement_ar', (string) ($json['config']['fallback_template']['name'] ?? ''));
            $this->assertSame('template_only', (string) ($json['config']['trigger_templates']['appointment_reminder']['strategy'] ?? ''));
            $this->assertSame(
                ['patient_name', 'appointment_date', 'appointment_time', 'clinic_name'],
                array_values($json['config']['trigger_templates']['appointment_reminder']['body_param_keys'] ?? [])
            );
            $this->assertSame(
                'appointment_reminder_ar_v1',
                (string) ($json['config']['trigger_templates']['appointment_reminder']['name'] ?? '')
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

                if (is_string($originalPhoneNumberId) && $originalPhoneNumberId !== '') {
                    $secretModel->setSecret('wa_phone_number_id', $originalPhoneNumberId, 1, 'phpunit-restore');
                }

                $settingsModel->setGlobalSetting(
                    WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TEMPLATES,
                    is_array($originalTriggers) ? $originalTriggers : [],
                    'json',
                    'phpunit-restore'
                );
                $settingsModel->setGlobalSetting(
                    WhatsAppTemplateRegistryService::SETTINGS_KEY_FALLBACK_TEMPLATE,
                    is_array($originalFallback) ? $originalFallback : [],
                    'json',
                    'phpunit-restore'
                );
            } finally {
                $session->set('active_clinic_id', $originalClinicId);
            }
        }
    }

    public function testSettingsUpdateChannelConfigRejectsWhatsAppTenantTemplateMutation(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for updateChannelConfig test.');
        }

        $response = $this->postWithCsrf(
            '/settings/updateChannelConfig',
            [
                'channel_type' => 'whatsapp',
                'action' => 'save_config',
                'config_json' => json_encode(['template_name' => 'tenant_owned'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
            ['active_clinic_id' => $clinicId],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => false,
            'message' => 'WhatsApp template configuration is managed from control plane. Clinics can only manage trigger enable/disable settings.',
        ]);
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

    public function testWhatsAppTenantPolicyPersistsTriggerToggles(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for WhatsApp trigger toggle test.');
        }

        $policyModel = new WhatsAppTenantPolicyModel();
        $original = $policyModel->getByClinicForControlPlane((int) $clinicId);

        try {
            $saved = $policyModel->upsertForControlPlane((int) $clinicId, [
                'trigger_toggles_json' => json_encode([
                    'appointment_reminder' => 1,
                    'appointment_confirmation' => 0,
                    'follow_up' => 0,
                    'no_show_follow_up' => 1,
                    'payment_reminder' => 1,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $this->assertNotFalse($saved);

            $updated = $policyModel->getByClinicForControlPlane((int) $clinicId);
            $this->assertIsArray($updated);

            $toggles = $updated['trigger_toggles_json'] ?? [];
            if (is_string($toggles)) {
                $toggles = json_decode($toggles, true);
            }

            $this->assertSame(1, (int) ($toggles['appointment_reminder'] ?? 0));
            $this->assertSame(0, (int) ($toggles['appointment_confirmation'] ?? 1));
            $this->assertSame(0, (int) ($toggles['follow_up'] ?? 1));
            $this->assertSame(1, (int) ($toggles['no_show_follow_up'] ?? 0));
            $this->assertSame(1, (int) ($toggles['payment_reminder'] ?? 0));
        } finally {
            if (is_array($original)) {
                $policyModel->upsertForControlPlane((int) $clinicId, $original);
            } else {
                $this->db->table('whatsapp_tenant_policies')->where('clinic_id', $clinicId)->delete();
            }
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

    public function testControlPlaneSendTestRejectsInvalidClinicId(): void
    {
        $beforeCount = $this->db->table('notifications')
            ->where('channel_type', 'whatsapp')
            ->countAllResults();

        $response = $this->postWithCsrf(
            '/controlplane/whatsapp/send-test',
            [
                'clinic_id' => 0,
                'target_number' => '+970599350450',
                'send_mode' => 'text',
                'message' => 'phpunit text',
            ],
            ['user_id' => 1]
        );

        $response->assertStatus(302);

        $afterCount = $this->db->table('notifications')
            ->where('channel_type', 'whatsapp')
            ->countAllResults();

        $this->assertSame($beforeCount, $afterCount);
    }

    public function testControlPlaneSendTestRejectsInvalidTemplateComponentsJsonBeforeEnqueue(): void
    {
        $clinicId = $this->findAnyClinicId();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No clinic row found for control-plane send-test validation.');
        }

        $beforeCount = $this->db->table('notifications')
            ->where('channel_type', 'whatsapp')
            ->countAllResults();

        $response = $this->postWithCsrf(
            '/controlplane/whatsapp/send-test',
            [
                'clinic_id' => $clinicId,
                'target_number' => '+970599350450',
                'send_mode' => 'template',
                'template_name' => 'approved_template',
                'template_language' => 'en_US',
                'template_components_json' => '{"bad-json"',
            ],
            ['user_id' => 1]
        );

        $response->assertStatus(302);

        $afterCount = $this->db->table('notifications')
            ->where('channel_type', 'whatsapp')
            ->countAllResults();

        $this->assertSame($beforeCount, $afterCount);
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
            ['POST', 'settings/updateChannelConfig', 'Settings::updateChannelConfig'],
            ['POST', 'settings/testWhatsAppChannel', 'Settings::testWhatsAppChannel'],
            ['POST', 'patient/(:num)/whatsapp-identity', 'Patient::saveWhatsAppIdentity/$1'],
            ['POST', 'controlplane/whatsapp/rotate-secret', 'ControlPlane\WhatsApp::rotateSecret'],
            ['POST', 'controlplane/whatsapp/send-test', 'ControlPlane\WhatsApp::sendTest'],
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
