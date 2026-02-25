<?php

namespace App\Commands;

use App\Libraries\ApiRuntimeContext;
use App\Models\ClinicNotificationChannelModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanModel;
use App\Models\PlatformSecretModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WhatsAppSeedLocalTest extends BaseCommand
{
    protected $group = 'SaaS';
    protected $name = 'whatsapp:seed-local-test';
    protected $description = 'Seeds local WhatsApp test prerequisites (platform secrets, clinic channel config, and plan feature enablement).';
    protected $usage = 'whatsapp:seed-local-test --clinic-id [id] --wa-access-token [token] --wa-app-secret [secret] --wa-verify-token [token] --wa-phone-number-id [id]';
    protected $options = [
        '--clinic-id' => 'Required test clinic ID.',
        '--wa-access-token' => 'Required Meta system user token.',
        '--wa-app-secret' => 'Required Meta app secret.',
        '--wa-verify-token' => 'Required webhook verify token.',
        '--wa-phone-number-id' => 'Required Meta phone number ID.',
        '--plan-id' => 'Optional explicit plan ID to patch features_json (defaults to clinic current subscription plan).',
        '--no-plan-patch' => 'Optional flag: skip updating plan features_json.',
    ];

    public function run(array $params)
    {
        $clinicId = (int) (CLI::getOption('clinic-id') ?? 0);
        $accessToken = (string) (CLI::getOption('wa-access-token') ?? '');
        $appSecret = (string) (CLI::getOption('wa-app-secret') ?? '');
        $verifyToken = (string) (CLI::getOption('wa-verify-token') ?? '');
        $phoneNumberId = (string) (CLI::getOption('wa-phone-number-id') ?? '');
        $explicitPlanId = (int) (CLI::getOption('plan-id') ?? 0);
        $skipPlanPatch = CLI::getOption('no-plan-patch') !== null;

        if ($clinicId <= 0) {
            CLI::error('FAIL-CLOSED: --clinic-id is required.');
            return;
        }

        if (trim($accessToken) === '' || trim($appSecret) === '' || trim($verifyToken) === '' || trim($phoneNumberId) === '') {
            CLI::error('FAIL-CLOSED: all WhatsApp secret options are required.');
            return;
        }

        $secrets = new PlatformSecretModel();
        $channelModel = new ClinicNotificationChannelModel();
        $subscriptionModel = new ClinicSubscriptionModel();
        $planModel = new PlanModel();

        $db = \Config\Database::connect();
        $clinic = $db->table('clinics')->where('id', $clinicId)->get()->getRowArray();
        if (!$clinic) {
            CLI::error("Clinic {$clinicId} not found.");
            return;
        }

        $secrets->setSecret('wa_access_token', $accessToken, null, 'Local WhatsApp test setup');
        $secrets->setSecret('wa_app_secret', $appSecret, null, 'Local WhatsApp test setup');
        $secrets->setSecret('wa_verify_token', $verifyToken, null, 'Local WhatsApp test setup');
        $secrets->setSecret('wa_phone_number_id', $phoneNumberId, null, 'Local WhatsApp test setup');

        // Bootstrap tenant context for tenant-scoped channel writes.
        $session = \Config\Services::session();
        ApiRuntimeContext::setClinicId($clinicId);
        $session->set('active_clinic_id', $clinicId);

        try {
            // Store clinic-scoped WhatsApp config in encrypted channel config.
            $channelModel->setConfig($clinicId, 'whatsapp', [
                'phone_number_id' => $phoneNumberId,
            ]);

            $channel = $channelModel
                ->withoutTenantScope()
                ->where('clinic_id', $clinicId)
                ->where('channel_type', 'whatsapp')
                ->first();

            if (!$channel) {
                CLI::error('Failed to create/read whatsapp channel config row.');
                return;
            }

            $channelModel->withoutTenantScope()->update((int) $channel['id'], [
                'enabled_by_superadmin' => 1,
                'configured_by_clinic' => 1,
                'validated' => 1,
                'provider_type' => 'meta_cloud_api',
                'last_tested_at' => date('Y-m-d H:i:s'),
            ]);
        } finally {
            ApiRuntimeContext::clear();
        }

        $patchedPlanId = null;
        if (!$skipPlanPatch) {
            $planId = $explicitPlanId;
            if ($planId <= 0) {
                $sub = $subscriptionModel->withoutTenantScope()->getCurrentSubscription($clinicId);
                $planId = (int) ($sub['plan_id'] ?? 0);
            }

            if ($planId > 0) {
                $plan = $planModel->find($planId);
                if ($plan) {
                    $features = json_decode((string) ($plan['features_json'] ?? '{}'), true);
                    if (!is_array($features)) {
                        $features = [];
                    }

                    if (!isset($features['notifications']) || !is_array($features['notifications'])) {
                        $features['notifications'] = [];
                    }
                    if (!isset($features['notifications']['email']) || !is_array($features['notifications']['email'])) {
                        $features['notifications']['email'] = [];
                    }
                    if (!isset($features['notifications']['whatsapp']) || !is_array($features['notifications']['whatsapp'])) {
                        $features['notifications']['whatsapp'] = [];
                    }

                    // Keep email enabled if absent and explicitly enable WhatsApp.
                    if (!array_key_exists('enabled', $features['notifications']['email'])) {
                        $features['notifications']['email']['enabled'] = true;
                    }
                    $features['notifications']['whatsapp']['enabled'] = true;

                    $planModel->update($planId, [
                        'features_json' => json_encode($features, JSON_UNESCAPED_UNICODE),
                    ]);
                    $patchedPlanId = $planId;
                }
            }
        }

        CLI::write('Local WhatsApp test setup complete.', 'green');
        CLI::write('- Clinic ID: ' . $clinicId . ' (' . ($clinic['name'] ?? 'Unknown') . ')');
        CLI::write('- Platform secrets seeded: wa_access_token, wa_app_secret, wa_verify_token, wa_phone_number_id');
        CLI::write('- Clinic whatsapp channel configured/enabled/validated');
        if ($patchedPlanId !== null) {
            CLI::write('- Plan features_json patched to enable notifications.whatsapp.enabled (Plan ID: ' . $patchedPlanId . ')');
        } elseif ($skipPlanPatch) {
            CLI::write('- Plan patch skipped (--no-plan-patch)');
        } else {
            CLI::write('- Plan patch not applied (no plan found for clinic).', 'yellow');
        }
    }
}
