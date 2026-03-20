<?php

namespace Tests\Unit;

use App\Controllers\ControlPlane\Plans;
use CodeIgniter\Test\CIUnitTestCase;

final class ControlPlanePlansTest extends CIUnitTestCase
{
    public function testDefaultFeaturesIncludeWhatsAppPlanGate(): void
    {
        $controller = new Plans();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('defaultFeatures');
        $method->setAccessible(true);

        $features = $method->invoke($controller);

        $this->assertIsArray($features);
        $this->assertArrayHasKey('notifications', $features);
        $this->assertSame(false, (bool) ($features['notifications']['whatsapp']['enabled'] ?? true));
    }

    public function testPlanFormExposesWhatsAppFeatureToggle(): void
    {
        $source = file_get_contents(APPPATH . 'Views/control_plane/plan_form.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('features[notifications][whatsapp][enabled]', $source);
        $this->assertStringContainsString('WhatsApp Notifications', $source);
    }
}
