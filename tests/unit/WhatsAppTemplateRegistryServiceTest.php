<?php

namespace Tests\Unit;

use App\Models\SettingsModel;
use App\Services\WhatsAppTemplateRegistryService;
use CodeIgniter\Test\CIUnitTestCase;

final class WhatsAppTemplateRegistryServiceTest extends CIUnitTestCase
{
    protected SettingsModel $settingsModel;
    protected WhatsAppTemplateRegistryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingsModel = new SettingsModel();
        $this->service = new WhatsAppTemplateRegistryService();
    }

    public function testGlobalConfigDefaultsToArabic(): void
    {
        $config = $this->service->getGlobalConfig();

        $this->assertIsArray($config);
        $this->assertSame('ar', (string) ($config['fallback_template']['language'] ?? ''));
        $this->assertSame('ar', (string) ($config['trigger_templates']['appointment_reminder']['language'] ?? ''));
        $this->assertSame('template_only', (string) ($config['trigger_templates']['appointment_reminder']['strategy'] ?? ''));
        $this->assertSame(1, (int) ($config['trigger_toggles']['appointment_reminder'] ?? 0));
    }

    public function testSaveGlobalConfigNormalizesArabicTemplates(): void
    {
        $originalTriggers = $this->settingsModel->getGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TEMPLATES, null);
        $originalFallback = $this->settingsModel->getGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_FALLBACK_TEMPLATE, null);
        $originalToggles = $this->settingsModel->getGlobalSetting(WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TOGGLES, null);

        try {
            $saved = $this->service->saveGlobalConfig(
                [
                    'appointment_reminder' => [
                        'name' => 'appointment_reminder_ar_v2',
                        'language' => 'en_US',
                        'header_param_key' => 'clinic_name',
                        'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time', 'clinic_name'],
                        'button_type' => '',
                        'button_index' => '0',
                        'button_param_key' => '',
                    ],
                ],
                [
                    'name' => 'general_reengagement_ar',
                    'language' => 'en_US',
                    'components' => [],
                ],
                [
                    'appointment_reminder' => 1,
                    'appointment_confirmation' => 0,
                ],
            );

            $this->assertTrue($saved);

            $config = $this->service->getGlobalConfig();
            $this->assertSame('appointment_reminder_ar_v2', (string) ($config['trigger_templates']['appointment_reminder']['name'] ?? ''));
            $this->assertSame('ar', (string) ($config['trigger_templates']['appointment_reminder']['language'] ?? ''));
            $this->assertSame(
                ['patient_name', 'appointment_date', 'appointment_time', 'clinic_name'],
                array_values($config['trigger_templates']['appointment_reminder']['body_param_keys'] ?? [])
            );
            $this->assertSame('general_reengagement_ar', (string) ($config['fallback_template']['name'] ?? ''));
            $this->assertSame('ar', (string) ($config['fallback_template']['language'] ?? ''));
            $this->assertSame(1, (int) ($config['trigger_toggles']['appointment_reminder'] ?? 0));
            $this->assertSame(0, (int) ($config['trigger_toggles']['appointment_confirmation'] ?? 1));
        } finally {
            $this->settingsModel->setGlobalSetting(
                WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TEMPLATES,
                is_array($originalTriggers) ? $originalTriggers : [],
                'json',
                'phpunit-restore'
            );
            $this->settingsModel->setGlobalSetting(
                WhatsAppTemplateRegistryService::SETTINGS_KEY_FALLBACK_TEMPLATE,
                is_array($originalFallback) ? $originalFallback : [],
                'json',
                'phpunit-restore'
            );
            $this->settingsModel->setGlobalSetting(
                WhatsAppTemplateRegistryService::SETTINGS_KEY_TRIGGER_TOGGLES,
                is_array($originalToggles) ? $originalToggles : [],
                'json',
                'phpunit-restore'
            );
        }
    }

    public function testTriggerReadinessMarksMissingTemplateAsNotReady(): void
    {
        $readiness = $this->service->getTriggerReadiness([
            'trigger_templates' => [
                'appointment_reminder' => [
                    'name' => '',
                    'language' => 'ar',
                    'strategy' => 'template_only',
                    'header_param_key' => '',
                    'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                    'button_type' => '',
                    'button_index' => '0',
                    'button_param_key' => '',
                ],
            ],
            'trigger_toggles' => [
                'appointment_reminder' => 1,
            ],
            'fallback_template' => [
                'name' => 'general_reengagement_ar_v1',
                'language' => 'ar',
                'components' => [],
            ],
        ]);

        $this->assertFalse((bool) ($readiness['appointment_reminder']['ready'] ?? true));
        $this->assertContains(
            'Approved Meta template name is required.',
            $readiness['appointment_reminder']['errors'] ?? []
        );
    }

    public function testValidateTriggerTemplatesRejectsInvalidVariableKey(): void
    {
        $errors = $this->service->validateTriggerTemplates([
            'appointment_reminder' => [
                'name' => 'appointment_reminder_ar_v1',
                'language' => 'ar',
                'strategy' => 'template_only',
                'header_param_key' => '',
                'body_param_keys' => ['not_a_real_var'],
                'button_type' => '',
                'button_index' => '0',
                'button_param_key' => '',
            ],
        ]);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Appointment Reminder:', $errors[0]);
    }
}
