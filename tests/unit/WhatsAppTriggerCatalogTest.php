<?php

namespace Tests\Unit;

use App\Services\WhatsAppTriggerCatalog;
use CodeIgniter\Test\CIUnitTestCase;

final class WhatsAppTriggerCatalogTest extends CIUnitTestCase
{
    public function testCatalogContainsAllWorkflowTriggers(): void
    {
        $catalog = new WhatsAppTriggerCatalog();
        $all = $catalog->all();

        $this->assertSame(
            ['appointment_reminder', 'appointment_confirmation', 'follow_up', 'no_show_follow_up', 'payment_reminder'],
            array_keys($all)
        );

        foreach ($all as $triggerKey => $meta) {
            $this->assertSame('template_only', $meta['strategy'] ?? null, $triggerKey);
            $this->assertNotEmpty($meta['body_param_keys'] ?? [], $triggerKey);
            $this->assertIsArray($meta['available_vars'] ?? [], $triggerKey);
        }
    }

    public function testNormalizeTriggerTemplateConfigPreservesCatalogDefaults(): void
    {
        $catalog = new WhatsAppTriggerCatalog();
        $normalized = $catalog->normalizeTriggerTemplateConfig([
            'appointment_reminder' => [
                'name' => 'appointment_reminder_v1',
                'language' => 'en_US',
                'header_param_key' => 'clinic_name',
                'body_param_keys' => ['patient_name', '', 'appointment_time'],
                'button_type' => 'url',
                'button_index' => '1',
                'button_param_key' => 'confirm_link',
            ],
        ]);

        $this->assertSame('appointment_reminder_v1', $normalized['appointment_reminder']['name']);
        $this->assertSame('en_US', $normalized['appointment_reminder']['language']);
        $this->assertSame('template_only', $normalized['appointment_reminder']['strategy']);
        $this->assertSame(['patient_name', 'appointment_time'], $normalized['appointment_reminder']['body_param_keys']);
        $this->assertSame('url', $normalized['appointment_reminder']['button_type']);
        $this->assertSame('1', $normalized['appointment_reminder']['button_index']);
        $this->assertSame('confirm_link', $normalized['appointment_reminder']['button_param_key']);

        $this->assertSame(
            ['patient_name', 'appointment_date', 'appointment_time'],
            $normalized['appointment_confirmation']['body_param_keys']
        );
        $this->assertSame('ar', $normalized['appointment_confirmation']['language']);
        $this->assertSame('', $normalized['appointment_confirmation']['name']);
    }
}
