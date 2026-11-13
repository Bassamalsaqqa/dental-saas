<?php
declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxTrialController.php';
require_once dirname(__DIR__, 2) . '/scripts/DeveloperSandboxExecutor.php';

use PHPUnit\Framework\TestCase;
use Scripts\DeveloperSandboxTrialController;
use Scripts\DeveloperSandboxExecutor;
use App\Config\Permissions;

/**
 * Pure DB-Free Unit Tests for DeveloperSandboxTrialController and Shared Executor Validators
 */
final class DeveloperSandboxTrialControllerTest extends TestCase
{
    // =========================================================================
    // 1. OUTBOUND LOCKDOWN VALIDATOR TESTS (BLOCKER 1 GAPS)
    // =========================================================================

    private function getCleanLockdownEnv(): array
    {
        return [
            'email.protocol' => 'smtp',
            'email.SMTPHost' => '',
            'email.SMTPPort' => '0',
        ];
    }

    private function getCleanLockdownPlan(): array
    {
        return [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => false],
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
    }

    public function testVerifyOutboundLockdownAcceptsCleanLockdown(): void
    {
        $res = DeveloperSandboxTrialController::verifyOutboundLockdown(
            $this->getCleanLockdownPlan(),
            $this->getCleanLockdownEnv()
        );
        $this->assertTrue($res['valid'], implode('; ', $res['errors']));
        $this->assertEmpty($res['errors']);
    }

    public function testVerifyOutboundLockdownRejectsEmptyNotificationsObject(): void
    {
        // Confirmed Blocker 1: {notifications:{}} must FAIL
        $plan = ['features_json' => '{"notifications":{}}'];
        $res = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $this->getCleanLockdownEnv());
        $this->assertFalse($res['valid']);
        $errorStr = implode('; ', $res['errors']);
        $this->assertStringContainsString("channel 'sms' is missing", $errorStr);
        $this->assertStringContainsString("channel 'whatsapp' is missing", $errorStr);
        $this->assertStringContainsString("channel 'email' is missing", $errorStr);
    }

    public function testVerifyOutboundLockdownRejectsMissingIndividualChannels(): void
    {
        // Missing email channel
        $planMissingEmail = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => false],
                    'whatsapp' => ['enabled' => false],
                ],
            ]),
        ];
        $res = DeveloperSandboxTrialController::verifyOutboundLockdown($planMissingEmail, $this->getCleanLockdownEnv());
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("channel 'email' is missing", implode('; ', $res['errors']));
    }

    public function testVerifyOutboundLockdownRejectsChannelNotAnArray(): void
    {
        // Channel is boolean false instead of array
        $plan = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => false,
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $res = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $this->getCleanLockdownEnv());
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("channel 'sms' is missing or not an array", implode('; ', $res['errors']));
    }

    public function testVerifyOutboundLockdownRejectsMissingEnabledKey(): void
    {
        $plan = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['provider' => 'twilio'], // missing 'enabled'
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $res = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $this->getCleanLockdownEnv());
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("channel 'sms' is missing required 'enabled' property", implode('; ', $res['errors']));
    }

    public function testVerifyOutboundLockdownRejectsNonStrictBooleanFalse(): void
    {
        $env = $this->getCleanLockdownEnv();

        // 1. null
        $planNull = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => null],
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $resNull = DeveloperSandboxTrialController::verifyOutboundLockdown($planNull, $env);
        $this->assertFalse($resNull['valid']);
        $this->assertStringContainsString("channel 'sms.enabled' must be strictly boolean false", implode('; ', $resNull['errors']));

        // 2. Integer 0
        $planZero = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => 0],
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $resZero = DeveloperSandboxTrialController::verifyOutboundLockdown($planZero, $env);
        $this->assertFalse($resZero['valid']);
        $this->assertStringContainsString("channel 'sms.enabled' must be strictly boolean false", implode('; ', $resZero['errors']));

        // 3. String 'false'
        $planStrFalse = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => 'false'],
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $resStrFalse = DeveloperSandboxTrialController::verifyOutboundLockdown($planStrFalse, $env);
        $this->assertFalse($resStrFalse['valid']);
        $this->assertStringContainsString("channel 'sms.enabled' must be strictly boolean false", implode('; ', $resStrFalse['errors']));

        // 4. String '0'
        $planStrZero = [
            'features_json' => json_encode([
                'notifications' => [
                    'sms' => ['enabled' => '0'],
                    'whatsapp' => ['enabled' => false],
                    'email' => ['enabled' => false],
                ],
            ]),
        ];
        $resStrZero = DeveloperSandboxTrialController::verifyOutboundLockdown($planStrZero, $env);
        $this->assertFalse($resStrZero['valid']);
        $this->assertStringContainsString("channel 'sms.enabled' must be strictly boolean false", implode('; ', $resStrZero['errors']));
    }

    public function testVerifyOutboundLockdownRejectsMissingPlanRowOrQueryFailure(): void
    {
        $env = $this->getCleanLockdownEnv();
        $resNull = DeveloperSandboxTrialController::verifyOutboundLockdown(null, $env);
        $this->assertFalse($resNull['valid']);
        $this->assertStringContainsString('missing, query failed, or lacks features_json', implode('; ', $resNull['errors']));

        $resEmptyArray = DeveloperSandboxTrialController::verifyOutboundLockdown([], $env);
        $this->assertFalse($resEmptyArray['valid']);
        $this->assertStringContainsString('missing, query failed, or lacks features_json', implode('; ', $resEmptyArray['errors']));
    }

    public function testVerifyOutboundLockdownRejectsMalformedJsonOrMissingNotifications(): void
    {
        $env = $this->getCleanLockdownEnv();

        // Malformed JSON
        $planMalformed = ['features_json' => '{not-json'];
        $resMalformed = DeveloperSandboxTrialController::verifyOutboundLockdown($planMalformed, $env);
        $this->assertFalse($resMalformed['valid']);
        $this->assertStringContainsString('malformed JSON', implode('; ', $resMalformed['errors']));

        // Missing notifications key
        $planMissingNotifs = ['features_json' => json_encode(['storage' => 'local'])];
        $resMissing = DeveloperSandboxTrialController::verifyOutboundLockdown($planMissingNotifs, $env);
        $this->assertFalse($resMissing['valid']);
        $this->assertStringContainsString('missing required notifications object', implode('; ', $resMissing['errors']));
    }

    public function testVerifyOutboundLockdownRejectsNonFailClosedEnvironmentEmailTransport(): void
    {
        $plan = $this->getCleanLockdownPlan();

        // 1. Wrong protocol
        $envBadProto = ['email.protocol' => 'mail', 'email.SMTPHost' => '', 'email.SMTPPort' => '0'];
        $res1 = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $envBadProto);
        $this->assertFalse($res1['valid']);
        $this->assertStringContainsString("protocol must be strictly 'smtp'", implode('; ', $res1['errors']));

        // 2. Non-empty SMTPHost
        $envBadHost = ['email.protocol' => 'smtp', 'email.SMTPHost' => 'smtp.example.com', 'email.SMTPPort' => '0'];
        $res2 = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $envBadHost);
        $this->assertFalse($res2['valid']);
        $this->assertStringContainsString("SMTPHost must be strictly blank", implode('; ', $res2['errors']));

        // 3. Non-zero SMTPPort
        $envBadPort = ['email.protocol' => 'smtp', 'email.SMTPHost' => '', 'email.SMTPPort' => '587'];
        $res3 = DeveloperSandboxTrialController::verifyOutboundLockdown($plan, $envBadPort);
        $this->assertFalse($res3['valid']);
        $this->assertStringContainsString("SMTPPort must be strictly '0' or 0", implode('; ', $res3['errors']));
    }

    // =========================================================================
    // 2. MIGRATION BATCHES & SOURCE SET VERIFIER TESTS (BLOCKER 4 GAPS)
    // =========================================================================

    public function testVerifyMigrationBatchesAcceptsExactManifestSet(): void
    {
        $manifest = DeveloperSandboxExecutor::REVIEWED_MIGRATION_MANIFEST;
        $rows = [];
        $id = 1;
        foreach (array_keys($manifest) as $filename) {
            $version = substr($filename, 0, (int) strpos($filename, '_'));
            $content = file_get_contents(dirname(__DIR__, 2) . '/app/Database/Migrations/' . $filename);
            preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends/i', (string) $content, $m);
            $className = 'App\\Database\\Migrations\\' . ($m[1] ?? 'Unknown');

            $rows[] = [
                'id' => $id,
                'version' => $version,
                'class' => $className,
                'batch' => $id, // Native incremental batch numbers (batch >= 1)
            ];
            $id++;
        }

        $res = DeveloperSandboxTrialController::verifyMigrationBatches($rows, 85, DeveloperSandboxExecutor::MODE_ALL_SOURCE);
        $this->assertTrue($res['valid'], implode('; ', $res['errors']));
        $this->assertSame(85, $res['count']);
        $this->assertCount(85, $res['batches']);
    }

    public function testVerifyMigrationBatchesRejectsDuplicates(): void
    {
        $rows = [
            ['id' => 1, 'version' => '2025-01-19-000001', 'class' => 'App\\Database\\Migrations\\CreateRBACTables', 'batch' => 1],
            ['id' => 2, 'version' => '2025-01-19-000001', 'class' => 'App\\Database\\Migrations\\CreateRBACTablesDuplicate', 'batch' => 2],
        ];

        $res = DeveloperSandboxTrialController::verifyMigrationBatches($rows, 2);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("Duplicate migration version '2025-01-19-000001'", implode('; ', $res['errors']));
    }

    public function testVerifyMigrationBatchesRejectsUnreviewedVersion(): void
    {
        $rows = [
            ['id' => 1, 'version' => '2099-01-01-000001', 'class' => 'App\\Database\\Migrations\\BackdoorMigration', 'batch' => 1],
        ];

        $res = DeveloperSandboxTrialController::verifyMigrationBatches($rows, 1);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("not in the reviewed migration manifest", implode('; ', $res['errors']));
    }

    public function testVerifyMigrationBatchesRejectsInvalidBatchOrNamespace(): void
    {
        $rows = [
            ['id' => 1, 'version' => '2025-01-19-000001', 'class' => 'External\\Migration', 'batch' => 0],
        ];

        $res = DeveloperSandboxTrialController::verifyMigrationBatches($rows, 1);
        $this->assertFalse($res['valid']);
        $errorStr = implode('; ', $res['errors']);
        $this->assertStringContainsString("unexpected class namespace", $errorStr);
        $this->assertStringContainsString("invalid batch '0'; must be an integer >= 1", $errorStr);
    }

    // =========================================================================
    // 3. APPLICATION TABLES VERIFIER TESTS
    // =========================================================================

    public function testVerifyAppTablesAcceptsCompleteBaselineTables(): void
    {
        $tables = [
            '_dev_sandbox_marker',
            'users', 'groups', 'users_groups', 'login_attempts',
            'patients', 'appointments', 'finances', 'examinations',
            'prescriptions', 'settings', 'treatments', 'odontograms',
            'inventory', 'inventory_usage', 'appointment_summary',
            'financial_summary', 'patient_examination_summary', 'permission_audit_log',
            'migrations',
            'activity_logs', 'api_tokens', 'clinic_notification_channels',
            'clinic_subscriptions', 'clinic_users', 'clinical_charge_lines',
            'clinical_procedure_status_history', 'clinical_procedures', 'clinics',
            'control_plane_audits', 'control_plane_invoice_payments', 'control_plane_invoices',
            'email_templates', 'examination_note_addenda', 'examination_note_signatures',
            'file_attachments', 'finance_liquidity_transfers', 'finance_payments',
            'finance_settlement_requests', 'inventory_batches', 'inventory_item_templates',
            'inventory_reorder_items', 'inventory_stock_movements', 'inventory_supplier_links',
            'job_audits', 'notification_attempts', 'notifications',
            'patient_odontogram_snapshots', 'patient_whatsapp', 'permissions',
            'plan_audits', 'plan_requests', 'plan_usage', 'plans',
            'platform_secrets', 'provider_payout_payments', 'provider_payouts',
            'role_permissions', 'roles', 'suppliers',
            'treatment_contract_items', 'treatment_contract_odontogram_snapshots',
            'treatment_contract_signatures', 'treatment_contracts',
            'user_permissions', 'user_roles', 'whatsapp_delivery_events',
            'whatsapp_optouts', 'whatsapp_tenant_policies',
        ];

        $res = DeveloperSandboxTrialController::verifyAppTables($tables, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE);
        $this->assertTrue($res['valid'], implode("\n", $res['errors']));
        $this->assertSame(69, $res['count']);
        $this->assertEmpty($res['missing']);
        $this->assertEmpty($res['extra']);
    }

    public function testVerifyAppTablesRejectsExtraTable(): void
    {
        $tables = [
            '_dev_sandbox_marker',
            'users', 'groups', 'users_groups', 'login_attempts',
            'patients', 'appointments', 'finances', 'examinations',
            'prescriptions', 'settings', 'treatments', 'odontograms',
            'inventory', 'inventory_usage', 'appointment_summary',
            'financial_summary', 'patient_examination_summary', 'permission_audit_log',
            'migrations',
            'activity_logs', 'api_tokens', 'clinic_notification_channels',
            'clinic_subscriptions', 'clinic_users', 'clinical_charge_lines',
            'clinical_procedure_status_history', 'clinical_procedures', 'clinics',
            'control_plane_audits', 'control_plane_invoice_payments', 'control_plane_invoices',
            'email_templates', 'examination_note_addenda', 'examination_note_signatures',
            'file_attachments', 'finance_liquidity_transfers', 'finance_payments',
            'finance_settlement_requests', 'inventory_batches', 'inventory_item_templates',
            'inventory_reorder_items', 'inventory_stock_movements', 'inventory_supplier_links',
            'job_audits', 'notification_attempts', 'notifications',
            'patient_odontogram_snapshots', 'patient_whatsapp', 'permissions',
            'plan_audits', 'plan_requests', 'plan_usage', 'plans',
            'platform_secrets', 'provider_payout_payments', 'provider_payouts',
            'role_permissions', 'roles', 'suppliers',
            'treatment_contract_items', 'treatment_contract_odontogram_snapshots',
            'treatment_contract_signatures', 'treatment_contracts',
            'user_permissions', 'user_roles', 'whatsapp_delivery_events',
            'whatsapp_optouts', 'whatsapp_tenant_policies',
            'unauthorized_backdoor_table', // extra!
        ];

        $res = DeveloperSandboxTrialController::verifyAppTables($tables, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE);
        $this->assertFalse($res['valid']);
        $this->assertContains('unauthorized_backdoor_table', $res['extra']);
    }

    public function testVerifyAppTablesRejectsDuplicates(): void
    {
        $tables = ['users', 'users', '_dev_sandbox_marker'];
        $res = DeveloperSandboxTrialController::verifyAppTables($tables);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("Duplicate table 'users'", implode('; ', $res['errors']));
    }

    public function testVerifyAppTablesRejectsMissingTable(): void
    {
        // Missing _dev_sandbox_marker
        $tables = [
            'users', 'groups', 'users_groups', 'login_attempts',
            'patients', 'appointments', 'finances', 'examinations',
            'prescriptions', 'settings', 'treatments', 'odontograms',
            'inventory', 'inventory_usage', 'appointment_summary',
            'financial_summary', 'patient_examination_summary', 'permission_audit_log',
        ];
        $res = DeveloperSandboxTrialController::verifyAppTables($tables);
        $this->assertFalse($res['valid']);
        $this->assertContains('_dev_sandbox_marker', $res['missing']);
        $this->assertStringContainsString("Required table '_dev_sandbox_marker' is missing", implode('; ', $res['errors']));
    }

    // =========================================================================
    // 4. RBAC DYNAMIC VERIFIER TESTS (DUPLICATES, EXTRAS, MAPPINGS GAPS)
    // =========================================================================

    public function testVerifyRbacDynamicAcceptsExactPermissionsRolesAndMappings(): void
    {
        $defPerms = Permissions::getDefaultPermissions();
        $dbPerms = [];
        $permId = 1;
        $permNameToId = [];
        foreach ($defPerms as $module => $actions) {
            foreach ($actions as $action => $desc) {
                $pName = $module . '.' . $action;
                $dbPerms[] = [
                    'id' => $permId,
                    'name' => $pName,
                    'module' => $module,
                    'action' => $action,
                ];
                $permNameToId[$pName] = $permId;
                $permId++;
            }
        }

        $defRoles = Permissions::getDefaultRoles();
        $dbRoles = [];
        $roleId = 1;
        $roleSlugToId = [];
        foreach ($defRoles as $slug => $spec) {
            $dbRoles[] = [
                'id' => $roleId,
                'name' => $spec['name'] ?? $slug,
                'slug' => $slug,
            ];
            $roleSlugToId[$slug] = $roleId;
            $roleId++;
        }

        // Generate canonical role_permissions mapping
        $dbRolePerms = [];
        foreach ($defRoles as $slug => $spec) {
            $rId = $roleSlugToId[$slug];
            $assigned = $spec['permissions'];

            if ($assigned === '*') {
                foreach ($permNameToId as $pId) {
                    $dbRolePerms[] = ['role_id' => $rId, 'permission_id' => $pId, 'granted' => 1];
                }
            } elseif (is_array($assigned)) {
                foreach ($assigned as $m => $actions) {
                    foreach ($actions as $a) {
                        $pId = $permNameToId[$m . '.' . $a];
                        $dbRolePerms[] = ['role_id' => $rId, 'permission_id' => $pId, 'granted' => 1];
                    }
                }
            }
        }

        $res = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles, $dbRolePerms);
        $this->assertTrue($res['valid'], implode('; ', $res['errors']));
        $this->assertSame(42, $res['permissions_count']);
        $this->assertSame(8, $res['roles_count']);
        $this->assertTrue($res['role_permissions_checked']);
    }

    public function testVerifyRbacDynamicRejectsDuplicatePermissionOrRole(): void
    {
        $dbPerms = [
            ['id' => 1, 'name' => 'dashboard.view'],
            ['id' => 2, 'name' => 'dashboard.view'], // Duplicate
        ];
        $dbRoles = [
            ['id' => 1, 'slug' => 'doctor'],
            ['id' => 2, 'slug' => 'doctor'], // Duplicate
        ];

        $res = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles);
        $this->assertFalse($res['valid']);
        $errorStr = implode('; ', $res['errors']);
        $this->assertStringContainsString("Duplicate permission 'dashboard.view'", $errorStr);
        $this->assertStringContainsString("Duplicate role slug 'doctor'", $errorStr);
    }

    public function testVerifyRbacDynamicRejectsExtraUnapprovedRoleOrPermission(): void
    {
        $defPerms = Permissions::getDefaultPermissions();
        $dbPerms = [];
        $id = 1;
        foreach ($defPerms as $module => $actions) {
            foreach ($actions as $action => $desc) {
                $dbPerms[] = ['id' => $id++, 'name' => $module . '.' . $action];
            }
        }
        $dbPerms[] = ['id' => $id, 'name' => 'backdoor.execute']; // extra!

        $defRoles = Permissions::getDefaultRoles();
        $dbRoles = [];
        $rId = 1;
        foreach ($defRoles as $slug => $spec) {
            $dbRoles[] = ['id' => $rId++, 'slug' => $slug];
        }
        $dbRoles[] = ['id' => $rId, 'slug' => 'unauthorized_hacker']; // extra!

        $res = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles);
        $this->assertFalse($res['valid']);
        $errorStr = implode('; ', $res['errors']);
        $this->assertStringContainsString("Unexpected extra permission 'backdoor.execute'", $errorStr);
        $this->assertStringContainsString("Unexpected extra role 'unauthorized_hacker'", $errorStr);
    }

    public function testVerifyRbacDynamicRejectsInvalidRolePermissionMapping(): void
    {
        // doctor role granted unapproved permission 'finance.delete'
        $dbPerms = [
            ['id' => 1, 'name' => 'dashboard.view'],
            ['id' => 2, 'name' => 'finance.delete'],
        ];
        $dbRoles = [
            ['id' => 1, 'slug' => 'doctor'],
        ];
        $dbRolePerms = [
            ['role_id' => 1, 'permission_id' => 2, 'granted' => 1], // doctor has finance.delete!
        ];

        $res = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles, $dbRolePerms);
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("granted unexpected permission 'finance.delete'", implode('; ', $res['errors']));
    }

    // =========================================================================
    // 5. CANONICAL SETTINGS VERIFIER TESTS (NO INVENTED currency_symbol)
    // =========================================================================

    public function testVerifyCanonicalSettingsAcceptsRealClinicSettings(): void
    {
        $settings = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 0,
        ];

        $res = DeveloperSandboxTrialController::verifyCanonicalSettings($settings, 'clinic');
        $this->assertTrue($res['valid'], implode('; ', $res['errors']));
    }

    public function testVerifyCanonicalSettingsRejectsNonZeroDynamicScheduling(): void
    {
        $settings = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 1, // Invariant violation!
        ];

        $res = DeveloperSandboxTrialController::verifyCanonicalSettings($settings, 'clinic');
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString('dynamic_doctor_scheduling_enabled must be 0', implode('; ', $res['errors']));
    }

    public function testVerifyCanonicalSettingsRejectsInventedCurrencySymbol(): void
    {
        $settings = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 0,
            'currency_symbol' => '₪', // Invented setting key!
        ];

        $res = DeveloperSandboxTrialController::verifyCanonicalSettings($settings, 'clinic');
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("Unexpected invented/extra setting key 'currency_symbol'", implode('; ', $res['errors']));

        // Global scope also rejects invented keys
        $globalWithInvented = [
            'default_timezone' => 'Asia/Jerusalem',
            'default_currency' => 'ILS',
            'default_currency_position' => 'after',
            'default_date_format' => 'd/m/Y',
            'default_time_format' => '12',
            'default_appointment_duration' => 30,
            'default_booking_chair_max' => 3,
            'app_name' => 'DentaCare Developer Sandbox',
            'invented_global_key' => 'junk_value',
        ];
        $resGlobalBad = DeveloperSandboxTrialController::verifyCanonicalSettings($globalWithInvented, 'global');
        $this->assertFalse($resGlobalBad['valid']);
        $this->assertStringContainsString("Unexpected invented/extra setting key 'invented_global_key'", implode('; ', $resGlobalBad['errors']));

        // Global scope accepts legitimate migration-seeded control plane settings
        $globalWithMigration = [
            'default_timezone' => 'Asia/Jerusalem',
            'default_currency' => 'ILS',
            'default_currency_position' => 'after',
            'default_date_format' => 'd/m/Y',
            'default_time_format' => '12',
            'default_appointment_duration' => 30,
            'default_booking_chair_max' => 3,
            'app_name' => 'DentaCare Developer Sandbox',
            'platform_name' => 'DentaCare Cloud',
            'retention_mode' => 'latest',
        ];
        $resGlobalGood = DeveloperSandboxTrialController::verifyCanonicalSettings($globalWithMigration, 'global');
        $this->assertTrue($resGlobalGood['valid'], implode('; ', $resGlobalGood['errors']));
    }

    public function testVerifyCanonicalSettingsRejectsMissingRequiredKey(): void
    {
        $settings = [
            'timezone' => 'Asia/Jerusalem',
            // Missing 'currency'
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 0,
        ];

        $res = DeveloperSandboxTrialController::verifyCanonicalSettings($settings, 'clinic');
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString("Required setting 'currency' (scope: clinic) is missing", implode('; ', $res['errors']));
    }

    // =========================================================================
    // 6. FOREIGN KEY ORPHAN & TABLE CHECK VERIFIER TESTS
    // =========================================================================

    public function testVerifyForeignKeyOrphansRejectsOrphansAndFailures(): void
    {
        // 1. Found orphans
        $resOrphans = DeveloperSandboxTrialController::verifyForeignKeyOrphans([
            'users_groups.user_id -> users.id' => 3,
        ]);
        $this->assertFalse($resOrphans['valid']);
        $this->assertStringContainsString('found 3 orphaned records', implode('; ', $resOrphans['errors']));

        // 2. Query failure (-1)
        $resFailed = DeveloperSandboxTrialController::verifyForeignKeyOrphans([
            'clinic_users.clinic_id -> clinics.id' => -1,
        ]);
        $this->assertFalse($resFailed['valid']);
        $this->assertStringContainsString('query for \'clinic_users.clinic_id -> clinics.id\' failed', implode('; ', $resFailed['errors']));

        // 3. Clean zero orphans
        $resClean = DeveloperSandboxTrialController::verifyForeignKeyOrphans([
            'users_groups.user_id -> users.id' => 0,
            'clinic_users.clinic_id -> clinics.id' => 0,
        ]);
        $this->assertTrue($resClean['valid']);
        $this->assertSame(0, $resClean['orphans_found']);
    }

    public function testVerifyTableCheckIntegrityFailsClosedOnFalseOrUnhealthy(): void
    {
        // 1. Query returned false
        $resFalse = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'users' => false,
        ]);
        $this->assertFalse($resFalse['valid']);
        $this->assertStringContainsString('CHECK TABLE query failed (returned false/null)', implode('; ', $resFalse['errors']));

        // 2. Corrupt / error status
        $resCorrupt = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'users' => ['Msg_text' => 'Corrupt index block'],
        ]);
        $this->assertFalse($resCorrupt['valid']);
        $this->assertStringContainsString('reported unhealthy status: Corrupt index block', implode('; ', $resCorrupt['errors']));

        // 3. Valid status
        $resOk = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'users' => ['Msg_text' => 'OK'],
            'settings' => ['Msg_text' => 'Table is already up to date'],
        ]);
        $this->assertTrue($resOk['valid']);

        // 4. Msg_type error
        $resErrorType = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'finances' => [
                'Msg_type' => 'Error',
                'Msg_text' => 'Table is marked as crashed',
            ],
        ]);
        $this->assertFalse($resErrorType['valid']);
        $this->assertStringContainsString('reported error type: Table is marked as crashed', implode('; ', $resErrorType['errors']));
    }

    public function testVerifyForeignKeyStructureAcceptsValidSchemaAndRejectsMissing(): void
    {
        $expected = [
            ['table' => 'clinic_users', 'column' => 'clinic_id', 'referenced_table' => 'clinics', 'referenced_column' => 'id'],
            ['table' => 'clinic_users', 'column' => 'user_id', 'referenced_table' => 'users', 'referenced_column' => 'id'],
            ['table' => 'role_permissions', 'column' => 'role_id', 'referenced_table' => 'roles', 'referenced_column' => 'id'],
        ];

        // 1. Valid matching foreign keys
        $validFks = [
            ['table' => 'clinic_users', 'column' => 'clinic_id', 'referenced_table' => 'clinics', 'referenced_column' => 'id'],
            ['table' => 'clinic_users', 'column' => 'user_id', 'referenced_table' => 'users', 'referenced_column' => 'id'],
            ['table' => 'role_permissions', 'column' => 'role_id', 'referenced_table' => 'roles', 'referenced_column' => 'id'],
        ];
        $resValid = DeveloperSandboxTrialController::verifyForeignKeyStructure($validFks, $expected);
        $this->assertTrue($resValid['valid'], implode('; ', $resValid['errors']));
        $this->assertSame(3, $resValid['constraints_checked']);

        // 2. Missing foreign key
        $missingFks = [
            ['table' => 'clinic_users', 'column' => 'clinic_id', 'referenced_table' => 'clinics', 'referenced_column' => 'id'],
            // user_id missing!
            ['table' => 'role_permissions', 'column' => 'role_id', 'referenced_table' => 'roles', 'referenced_column' => 'id'],
        ];
        $resMissing = DeveloperSandboxTrialController::verifyForeignKeyStructure($missingFks, $expected);
        $this->assertFalse($resMissing['valid']);
        $this->assertCount(1, $resMissing['missing']);
        $this->assertStringContainsString("Foreign key constraint on 'clinic_users.user_id' referencing 'users.id' is missing", implode('; ', $resMissing['errors']));

        // 3. Foreign key referencing wrong table
        $wrongFks = [
            ['table' => 'clinic_users', 'column' => 'clinic_id', 'referenced_table' => 'clinics', 'referenced_column' => 'id'],
            ['table' => 'clinic_users', 'column' => 'user_id', 'referenced_table' => 'clinics', 'referenced_column' => 'id'], // wrong!
            ['table' => 'role_permissions', 'column' => 'role_id', 'referenced_table' => 'roles', 'referenced_column' => 'id'],
        ];
        $resWrong = DeveloperSandboxTrialController::verifyForeignKeyStructure($wrongFks, $expected);
        $this->assertFalse($resWrong['valid']);
        $this->assertStringContainsString("references 'clinics.id', expected 'users.id'", implode('; ', $resWrong['errors']));
    }

    // =========================================================================
    // 7. PINNED SOURCE VERIFICATION TESTS
    // =========================================================================

    public function testVerifyReviewedSourcesAgainstPinnedMatchesRealRepositoryFiles(): void
    {
        $res = DeveloperSandboxExecutor::verifyReviewedSourcesAgainstPinned();
        $this->assertTrue($res['valid'], implode('; ', $res['errors']));
    }

    // =========================================================================
    // 8. EXECUTOR PHYSICAL COUNT HELPER (BLOCKER 5 GAPS)
    // =========================================================================

    public function testExecutorQueryPhysicalTableCountFailsClosedWithoutMockFabrication(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_table_fail_test_' . bin2hex(random_bytes(6));

        // Mock DB returns null on query
        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $sql, array $params = []) {
                return new class {
                    public function getRowArray() { return null; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        // Confirmed Blocker 5: queryPhysicalTableCount must return -1 fail-closed, NOT fabricated 69!
        $count = $executor->queryPhysicalTableCount($mockDb, $context);
        $this->assertSame(-1, $count);
    }

    public function testExecutorQueryPhysicalTableCountReturnsExplicitInjectedFakeResult(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_table_fake_test_' . bin2hex(random_bytes(6));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $sql, array $params = []) {
                return new class {
                    public function getRowArray() { return ['table_count' => 69]; }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);
        $count = $executor->queryPhysicalTableCount($mockDb, $context);
        $this->assertSame(69, $count);
    }

    // =========================================================================
    // 9. EXECUTOR STAGE 8 INTEGRATION TESTS CALLING PRODUCTION VALIDATOR
    // =========================================================================

    public function testExecutorStage8FailsClosedOnEmptyNotificationsObject(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_stage8_empty_test_' . bin2hex(random_bytes(6));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $sql, array $params = []) {
                return new class {
                    public function getRowArray() {
                        return ['features_json' => '{"notifications":{}}'];
                    }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        $ref = new \ReflectionProperty($context, 'currentPhase');
        $ref->setAccessible(true);
        $ref->setValue($context, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE7_DONE);

        putenv('email.protocol=smtp');
        putenv('email.SMTPHost=');
        putenv('email.SMTPPort=0');

        $result = $executor->executeStage8_VerifyMessagingTransportLockdown($context, $mockDb);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString("channel 'sms' is missing", $result['message']);
    }

    public function testExecutorStage8SucceedsOnFullyLockedDownChannels(): void
    {
        $executor = new DeveloperSandboxExecutor();
        $tempDir = sys_get_temp_dir() . '/denta_stage8_clean_test_' . bin2hex(random_bytes(6));

        $mockDb = new class implements \Scripts\DeveloperSandboxTestConnectionStub {
            public function query(string $sql, array $params = []) {
                return new class {
                    public function getRowArray() {
                        return [
                            'features_json' => json_encode([
                                'notifications' => [
                                    'sms' => ['enabled' => false],
                                    'whatsapp' => ['enabled' => false],
                                    'email' => ['enabled' => false],
                                ],
                            ]),
                        ];
                    }
                };
            }
        };

        $context = DeveloperSandboxExecutor::createTestMockContext($mockDb, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE, $tempDir);

        $ref = new \ReflectionProperty($context, 'currentPhase');
        $ref->setAccessible(true);
        $ref->setValue($context, \Scripts\DeveloperSandboxRunContext::PHASE_STAGE7_DONE);

        putenv('email.protocol=smtp');
        putenv('email.SMTPHost=');
        putenv('email.SMTPPort=0');

        $result = $executor->executeStage8_VerifyMessagingTransportLockdown($context, $mockDb);
        $this->assertTrue($result['success'], $result['message'] ?? '');
    }

    // =========================================================================
    // 7. REGRESSION TESTS FOR CONFIRMED VERIFIER NEGATIVES & MALFORMED INPUTS
    // =========================================================================

    public function testVerifyTableCheckIntegrityRejectsNotOkSubstringAndNonStatusMsgTypes(): void
    {
        // Negative 1: 'not ok' contains 'ok' but must strictly FAIL
        $resNotOk = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'patients' => [
                'Msg_type' => 'status',
                'Msg_text' => 'not ok',
            ],
        ]);
        $this->assertFalse($resNotOk['valid']);
        $this->assertStringContainsString('reported unhealthy status: not ok', implode('; ', $resNotOk['errors']));

        // Negative 2: Warning type instead of status
        $resWarning = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'patients' => [
                'Msg_type' => 'warning',
                'Msg_text' => '1 client is using or hasn\'t closed the table properly',
            ],
        ]);
        $this->assertFalse($resWarning['valid']);
        $this->assertStringContainsString("reported non-status Msg_type 'warning'", implode('; ', $resWarning['errors']));

        // Negative 3: Empty Msg_text
        $resEmpty = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'patients' => [
                'Msg_type' => 'status',
                'Msg_text' => '   ',
            ],
        ]);
        $this->assertFalse($resEmpty['valid']);
        $this->assertStringContainsString('missing Msg_text', implode('; ', $resEmpty['errors']));

        // Negative 4: String input with corrupt status
        $resStringBad = DeveloperSandboxTrialController::verifyTableCheckIntegrity([
            'patients' => 'corrupt',
        ]);
        $this->assertFalse($resStringBad['valid']);
        $this->assertStringContainsString('reported unhealthy status: corrupt', implode('; ', $resStringBad['errors']));
    }

    public function testVerifyCanonicalSettingsFailsClosedOnUnknownScopeAndInvalidValues(): void
    {
        // Negative 1: verifyCanonicalSettings([],'invalid') must fail closed!
        $resUnknownScope = DeveloperSandboxTrialController::verifyCanonicalSettings([], 'invalid');
        $this->assertFalse($resUnknownScope['valid']);
        $this->assertStringContainsString("Unknown settings verification scope 'invalid'", implode('; ', $resUnknownScope['errors']));

        // Negative 2: dynamic_doctor_scheduling_enabled with junk string (must not pass via (int) 'junk' == 0)
        $settingsJunkDyn = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 'junk_value',
        ];
        $resJunkDyn = DeveloperSandboxTrialController::verifyCanonicalSettings($settingsJunkDyn, 'clinic');
        $this->assertFalse($resJunkDyn['valid']);
        $this->assertStringContainsString('dynamic_doctor_scheduling_enabled must be 0', implode('; ', $resJunkDyn['errors']));

        // Negative 3: booking_chair_count with junk string or non-matching int
        $settingsJunkCount = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 'junk',
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 0,
        ];
        $resJunkCount = DeveloperSandboxTrialController::verifyCanonicalSettings($settingsJunkCount, 'clinic');
        $this->assertFalse($resJunkCount['valid']);
        $this->assertStringContainsString("Setting 'booking_chair_count' value mismatch: expected integer 1", implode('; ', $resJunkCount['errors']));

        // Negative 4: wrong timezone / currency
        $settingsBadTz = [
            'timezone' => 'UTC',
            'currency' => 'USD',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Doctor 1'],
            'dynamic_doctor_scheduling_enabled' => 0,
        ];
        $resBadTz = DeveloperSandboxTrialController::verifyCanonicalSettings($settingsBadTz, 'clinic');
        $this->assertFalse($resBadTz['valid']);
        $errorStr = implode('; ', $resBadTz['errors']);
        $this->assertStringContainsString("Setting 'timezone' value mismatch: expected 'Asia/Jerusalem'", $errorStr);
        $this->assertStringContainsString("Setting 'currency' value mismatch: expected 'ILS'", $errorStr);

        // Negative 5: malformed JSON for array setting
        $settingsBadJson = [
            'timezone' => 'Asia/Jerusalem',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'booking_chair_count' => 1,
            'booking_chair_names' => '{malformed_json}',
            'dynamic_doctor_scheduling_enabled' => 0,
        ];
        $resBadJson = DeveloperSandboxTrialController::verifyCanonicalSettings($settingsBadJson, 'clinic');
        $this->assertFalse($resBadJson['valid']);
        $this->assertStringContainsString("Setting 'booking_chair_names' JSON value mismatch", implode('; ', $resBadJson['errors']));

        // Clean: verify 'global' scope
        $globalSettings = [
            'default_timezone' => 'Asia/Jerusalem',
            'default_currency' => 'ILS',
            'default_currency_position' => 'after',
            'default_date_format' => 'd/m/Y',
            'default_time_format' => '12',
            'default_appointment_duration' => 30,
            'default_booking_chair_max' => 3,
            'app_name' => 'DentaCare Developer Sandbox',
        ];
        $resGlobal = DeveloperSandboxTrialController::verifyCanonicalSettings($globalSettings, 'global');
        $this->assertTrue($resGlobal['valid'], implode('; ', $resGlobal['errors']));
    }

    public function testVerifyAppTablesRefusesModeAllSourceWithNotImplementedAndFailsClosedOnUnknown(): void
    {
        // 1. Refuse MODE_ALL_SOURCE with NOT_IMPLEMENTED
        $resAllSource = DeveloperSandboxTrialController::verifyAppTables([], DeveloperSandboxExecutor::MODE_ALL_SOURCE);
        $this->assertFalse($resAllSource['valid']);
        $this->assertSame('NOT_IMPLEMENTED', $resAllSource['status']);
        $this->assertStringContainsString('MODE_ALL_SOURCE table verification is NOT_IMPLEMENTED', implode('; ', $resAllSource['errors']));

        // 2. Fail closed on unknown mode
        $resUnknown = DeveloperSandboxTrialController::verifyAppTables([], 'unsupported_mode');
        $this->assertFalse($resUnknown['valid']);
        $this->assertSame('FAILED', $resUnknown['status']);
        $this->assertStringContainsString("Unknown table verification mode 'unsupported_mode'", implode('; ', $resUnknown['errors']));
    }

    public function testVerifyMigrationBatchesRejectsArbitraryFakeClassesAndFloatValues(): void
    {
        // 1. Rejects fake class
        $rowsFakeClass = [
            ['id' => 1, 'version' => '2025-01-19-000001', 'class' => 'App\\Database\\Migrations\\FakeArbitraryMigration', 'batch' => 1],
        ];
        $resFakeClass = DeveloperSandboxTrialController::verifyMigrationBatches($rowsFakeClass, 1);
        $this->assertFalse($resFakeClass['valid']);
        $this->assertStringContainsString("class mismatch for version '2025-01-19-000001': expected 'App\\Database\\Migrations\\CreateRBACTables'", implode('; ', $resFakeClass['errors']));

        // 2. Rejects float batch 1.5
        $rowsFloatBatch = [
            ['id' => 1, 'version' => '2025-01-19-000001', 'class' => 'App\\Database\\Migrations\\CreateRBACTables', 'batch' => 1.5],
        ];
        $resFloatBatch = DeveloperSandboxTrialController::verifyMigrationBatches($rowsFloatBatch, 1);
        $this->assertFalse($resFloatBatch['valid']);
        $this->assertStringContainsString("invalid batch '1.5'; must be an integer >= 1", implode('; ', $resFloatBatch['errors']));

        // 3. Rejects float id 1.5
        $rowsFloatId = [
            ['id' => 1.5, 'version' => '2025-01-19-000001', 'class' => 'App\\Database\\Migrations\\CreateRBACTables', 'batch' => 1],
        ];
        $resFloatId = DeveloperSandboxTrialController::verifyMigrationBatches($rowsFloatId, 1);
        $this->assertFalse($resFloatId['valid']);
        $this->assertStringContainsString("invalid or non-positive migration id 1.5", implode('; ', $resFloatId['errors']));

        // 4. Baseline mode rejects dormant migration (e.g. 2026-09-26-000001)
        $rowsDormant = [
            ['id' => 1, 'version' => '2026-09-26-000001', 'class' => 'App\\Database\\Migrations\\AddDynamicDoctorScheduling', 'batch' => 1],
        ];
        $resDormant = DeveloperSandboxTrialController::verifyMigrationBatches($rowsDormant, 1, DeveloperSandboxExecutor::MODE_PRODUCTION_BASELINE);
        $this->assertFalse($resDormant['valid']);
        $this->assertStringContainsString("version '2026-09-26-000001' is not in the reviewed migration manifest for mode 'production-baseline'", implode('; ', $resDormant['errors']));

        // 5. Fail closed on unknown mode
        $resUnknownMode = DeveloperSandboxTrialController::verifyMigrationBatches([], 77, 'invalid_mode');
        $this->assertFalse($resUnknownMode['valid']);
        $this->assertStringContainsString("Unknown migration verification mode 'invalid_mode'", implode('; ', $resUnknownMode['errors']));
    }

    public function testVerifyRbacDynamicRejectsPermissionScopeClinicOrphanIdsAndWildcardExtras(): void
    {
        $defPerms = Permissions::getDefaultPermissions();
        $dbPerms = [];
        $permId = 1;
        foreach ($defPerms as $module => $actions) {
            foreach ($actions as $action => $desc) {
                $dbPerms[] = [
                    'id' => $permId,
                    'name' => $module . '.' . $action,
                    'module' => $module,
                    'action' => $action,
                ];
                $permId++;
            }
        }

        $defRoles = Permissions::getDefaultRoles();
        $dbRoles = [];
        $roleId = 1;
        foreach ($defRoles as $slug => $spec) {
            $dbRoles[] = [
                'id' => $roleId,
                'slug' => $slug,
                'name' => $spec['name'] ?? $slug,
                'clinic_id' => null, // Valid system role has null clinic_id
            ];
            $roleId++;
        }

        // 1. Partial status when role_permissions mappings are omitted
        $resPartial = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles, null);
        $this->assertTrue($resPartial['valid']);
        $this->assertTrue($resPartial['is_partial']);
        $this->assertSame('PARTIAL', $resPartial['status']);

        // 2. Rejects permission with wrong module or action
        $badPerms = $dbPerms;
        $badPerms[0]['module'] = 'wrong_module';
        $resBadMod = DeveloperSandboxTrialController::verifyRbacDynamic($badPerms, $dbRoles);
        $this->assertFalse($resBadMod['valid']);
        $this->assertStringContainsString("unexpected module 'wrong_module'", implode('; ', $resBadMod['errors']));

        // 3. Rejects role with tenant clinic scope (non-null clinic_id)
        $badRoles = $dbRoles;
        $badRoles[0]['clinic_id'] = 42;
        $resBadScope = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $badRoles);
        $this->assertFalse($resBadScope['valid']);
        $this->assertStringContainsString("has invalid clinic_id scope 42; system default roles must have null clinic_id", implode('; ', $resBadScope['errors']));

        // 4. Rejects mapping with orphaned / unknown role_id or permission_id
        $orphanMappings = [
            ['role_id' => 9999, 'permission_id' => 1, 'granted' => 1],
        ];
        $resOrphan = DeveloperSandboxTrialController::verifyRbacDynamic($dbPerms, $dbRoles, $orphanMappings);
        $this->assertFalse($resOrphan['valid']);
        $this->assertStringContainsString("references unknown or orphaned role_id 9999", implode('; ', $resOrphan['errors']));

        // 5. Rejects wildcard role granted extra non-system permission
        $fakePerms = $dbPerms;
        $fakePerms[] = ['id' => 999, 'name' => 'backdoor.exploit', 'module' => 'backdoor', 'action' => 'exploit'];
        $resWildcardExtra = DeveloperSandboxTrialController::verifyRbacDynamic($fakePerms, $dbRoles, [
            ['role_id' => 1, 'permission_id' => 999, 'granted' => 1],
        ]);
        $this->assertFalse($resWildcardExtra['valid']);
        $errorStr = implode('; ', $resWildcardExtra['errors']);
        $this->assertStringContainsString("Unexpected extra permission 'backdoor.exploit'", $errorStr);
    }

    public function testVerifyForeignKeyStructureReportsPartialStatus(): void
    {
        $res = DeveloperSandboxTrialController::verifyForeignKeyStructure(
            [['table' => 'users_groups', 'column' => 'user_id', 'referenced_table' => 'users', 'referenced_column' => 'id']],
            [['table' => 'users_groups', 'column' => 'user_id', 'referenced_table' => 'users', 'referenced_column' => 'id']]
        );
        $this->assertTrue($res['valid']);
        $this->assertTrue($res['is_partial']);
        $this->assertFalse($res['exhaustive']);
        $this->assertSame('PARTIAL', $res['status']);
    }

    public function testOfflineCliEnforcesExactlyOneArgument(): void
    {
        $scriptPath = escapeshellarg(dirname(__DIR__, 2) . '/scripts/DeveloperSandboxTrialController.php');

        // 0 arguments -> exit code 1
        exec("php {$scriptPath} 2>&1", $out0, $code0);
        $this->assertSame(1, $code0);
        $this->assertStringContainsString('CRITICAL USAGE ERROR', implode(' ', $out0));

        // 2 arguments -> exit code 1
        exec("php {$scriptPath} --version extra 2>&1", $out2, $code2);
        $this->assertSame(1, $code2);
        $this->assertStringContainsString('CRITICAL USAGE ERROR', implode(' ', $out2));

        // 1 argument -> exit code 0
        exec("php {$scriptPath} --version 2>&1", $out1, $code1);
        $this->assertSame(0, $code1);
        $this->assertStringContainsString('DeveloperSandboxTrialController', implode(' ', $out1));
    }
}
