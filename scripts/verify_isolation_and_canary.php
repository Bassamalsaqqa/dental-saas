<?php
declare(strict_types=1);

/**
 * Canary Preservation and Guarded Refusal Verification Script
 *
 * Demonstrates:
 * 1. Normal repeat provisioning preserves synthetic canary data completely (all columns).
 * 2. Explicit --reset affects only task-owned disposable data.
 * 3. Both normal provisioning and --reset refuse unowned non-empty databases.
 * 4. Fails with nonzero exit code on any verification failure.
 */

$repoRoot = dirname(__DIR__);
require_once __DIR__ . '/TestDatabasePreflight.php';
require_once $repoRoot . '/vendor/autoload.php';
require_once $repoRoot . '/system/Test/bootstrap.php';

use Scripts\TestDatabasePreflight;

class VerificationFailureException extends \RuntimeException {}

function failVerification(string $message): void {
    throw new VerificationFailureException($message);
}

// Check CLI flags
$cliArgs = array_slice($argv, 1);
$testInjectedFailure = in_array('--test-injected-failure', $cliArgs, true);
$testInjectedFailureAfterMarkerDrop = in_array('--test-injected-failure-after-marker-drop', $cliArgs, true);

// Early injected failure verification (mutation-free)
if ($testInjectedFailure) {
    fwrite(STDERR, "\n[VERIFICATION FAILURE] Deliberately injected test failure for exit status verification.\n");
    exit(1);
}

$markerWasRemovedByHarness = false;
$capturedFailure = null;
$db = null;
$provisionScript = $repoRoot . '/scripts/provision_test_db.php';

try {
    // 1. Enforce preflight environment and configuration on this verification handle
    TestDatabasePreflight::enforceEnvironment();

    $dbConfig = new \Config\Database();
    $testsConfig = $dbConfig->tests ?? [];
    $businessConfig = $dbConfig->default ?? [];

    $configErrors = TestDatabasePreflight::validateConfigBeforeConnection($testsConfig, $businessConfig);
    if (!empty($configErrors)) {
        failVerification("Preflight config check failed in verification harness: " . implode('; ', $configErrors));
    }

    try {
        $db = \Config\Database::connect('tests');
    } catch (\Throwable $e) {
        failVerification("Unable to connect to tests database in verification harness: " . $e->getMessage());
    }

    $liveErrors = TestDatabasePreflight::validateLiveConnection($db);
    if (!empty($liveErrors)) {
        failVerification("Preflight live connection check failed in verification harness: " . implode('; ', $liveErrors));
    }

    echo "========================================================\n";
    echo " STEP 1: PROVISION CLEAN TEST DATABASE WITH MARKER\n";
    echo "========================================================\n";
    passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript) . ' --reset', $exitCode);
    if ($exitCode !== 0) {
        failVerification("Initial reset failed with exit code {$exitCode}");
    }

    // Re-verify ownership marker on our handle before issuing writes
    $ownership = TestDatabasePreflight::verifyOwnershipForReset($db);
    if (!$ownership['allowed']) {
        failVerification("Ownership verification failed before canary insertion: {$ownership['reason']}");
    }

    echo "\n========================================================\n";
    echo " STEP 2: INSERT SYNTHETIC CANARY ROW\n";
    echo "========================================================\n";
    $canaryPlanId = 9999;
    $db->table('plans')->where('id', $canaryPlanId)->delete();
    $inserted = $db->table('plans')->insert([
        'id' => $canaryPlanId,
        'name' => 'Synthetic Canary Plan',
        'status' => 'active',
        'features_json' => json_encode(['canary' => true]),
        'limits_json' => json_encode(['canary' => 1]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    if (!$inserted) {
        failVerification("Failed to insert synthetic canary row.");
    }

    $canaryBefore = $db->table('plans')->where('id', $canaryPlanId)->get()->getRowArray();
    if (!$canaryBefore) {
        failVerification("Failed to retrieve inserted synthetic canary row.");
    }
    echo "Canary row inserted successfully (ID: {$canaryPlanId}, Name: '{$canaryBefore['name']}').\n";

    echo "\n========================================================\n";
    echo " STEP 3: RUN NORMAL PROVISION (REPEAT INVOCATION, NO --reset)\n";
    echo "========================================================\n";
    passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript), $exitCode);
    if ($exitCode !== 0) {
        failVerification("Repeat normal provision failed with exit code {$exitCode}");
    }

    $canaryAfterNormal = $db->table('plans')->where('id', $canaryPlanId)->get()->getRowArray();
    if (!$canaryAfterNormal) {
        failVerification("Canary row was destroyed by normal provisioning!");
    }

    // Compare complete row values to verify zero mutation
    foreach ($canaryBefore as $col => $val) {
        if (!array_key_exists($col, $canaryAfterNormal)) {
            failVerification("Canary column '{$col}' was removed by normal provisioning!");
        }
        if ((string) $canaryAfterNormal[$col] !== (string) $val) {
            failVerification("Canary column '{$col}' was mutated! Expected '{$val}', got '{$canaryAfterNormal[$col]}'.");
        }
    }
    echo "\n>>> VERIFICATION SUCCESS: Synthetic canary row was COMPLETELY PRESERVED across normal repeat provisioning! <<<\n";

    echo "\n========================================================\n";
    echo " STEP 4: RUN EXPLICIT --reset (CONFIRM RE-PROVISION)\n";
    echo "========================================================\n";
    passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript) . ' --reset', $exitCode);
    if ($exitCode !== 0) {
        failVerification("Explicit reset failed with exit code {$exitCode}");
    }

    $canaryAfterReset = $db->table('plans')->where('id', $canaryPlanId)->get()->getRowArray();
    if ($canaryAfterReset !== null) {
        failVerification("Canary row was not cleaned by explicit reset!");
    }
    echo "\n>>> VERIFICATION SUCCESS: Explicit --reset wiped task-owned data and restored clean baseline! <<<\n";

    echo "\n========================================================\n";
    echo " STEP 5: TEST REFUSAL ON UNOWNED NON-EMPTY DATABASE\n";
    echo "========================================================\n";
    $preDropOwnership = TestDatabasePreflight::verifyOwnershipForReset($db);
    if (!$preDropOwnership['allowed']) {
        failVerification("Cannot test refusal: target database is not verified as task-owned.");
    }

    echo "Removing task ownership marker table to simulate unowned/unknown database...\n";
    $markerWasRemovedByHarness = true;
    $dropRes = $db->query("DROP TABLE IF EXISTS `_test_db_marker`");
    if (!$dropRes) {
        failVerification("Failed to drop marker table for refusal test: " . json_encode($db->error()));
    }

    // Exercise deterministic failure injection after marker removal (if requested)
    if ($testInjectedFailureAfterMarkerDrop) {
        echo "Triggering injected failure immediately after marker removal...\n";
        failVerification("Deliberately injected test failure after marker drop to verify cleanup execution.");
    }

    echo "\nAttempting --reset on unowned non-empty database:\n";
    passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript) . ' --reset', $exitResetRefusal);
    echo "Exit code from --reset: {$exitResetRefusal}\n";
    if ($exitResetRefusal === 0) {
        failVerification("--reset did not refuse an unowned non-empty database!");
    }
    echo ">>> VERIFICATION SUCCESS: --reset REFUSED unowned non-empty database! <<<\n";

    echo "\nAttempting normal provisioning on unowned non-empty database:\n";
    passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript), $exitNormalRefusal);
    echo "Exit code from normal provisioning: {$exitNormalRefusal}\n";
    if ($exitNormalRefusal === 0) {
        failVerification("normal provisioning did not refuse an unowned non-empty database!");
    }
    echo ">>> VERIFICATION SUCCESS: normal provisioning REFUSED unowned non-empty database! <<<\n";

} catch (\Throwable $primaryError) {
    $capturedFailure = $primaryError;
} finally {
    // ALWAYS run bounded cleanup before outermost exit if this harness removed the marker
    if ($markerWasRemovedByHarness && $db !== null) {
        echo "\n========================================================\n";
        echo " STEP 6: RESTORE CLEAN DISPOSABLE TEST DATABASE (CLEANUP)\n";
        echo "========================================================\n";
        try {
            if ($db->getDatabase() === TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB) {
                echo "Recreating task ownership marker on verified disposable test database...\n";
                $createMarker = $db->query("CREATE TABLE IF NOT EXISTS `_test_db_marker` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `task_owner` VARCHAR(100) NOT NULL,
                    `is_disposable` TINYINT(1) NOT NULL DEFAULT 1,
                    `target_db` VARCHAR(100) NOT NULL,
                    `created_at` DATETIME NOT NULL
                )");
                if (!$createMarker) {
                    throw new \RuntimeException("Cleanup failed to create _test_db_marker: " . json_encode($db->error()));
                }

                $insertMarker = $db->table('_test_db_marker')->insert([
                    'task_owner' => TestDatabasePreflight::TASK_OWNER_SIGNATURE,
                    'is_disposable' => 1,
                    'target_db' => TestDatabasePreflight::APPROVED_DISPOSABLE_TEST_DB,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                if (!$insertMarker) {
                    throw new \RuntimeException("Cleanup failed to insert marker into _test_db_marker.");
                }

                echo "Restoring clean baseline schema via provision_test_db.php --reset...\n";
                passthru('"' . PHP_BINARY . '" ' . escapeshellarg($provisionScript) . ' --reset', $exitRestore);
                if ($exitRestore !== 0) {
                    throw new \RuntimeException("Cleanup provisioning reset failed with exit code {$exitRestore}");
                }

                $markerWasRemovedByHarness = false;
                echo ">>> CLEANUP SUCCESS: Task ownership marker and baseline schema restored! <<<\n";
            } else {
                echo "[CLEANUP WARNING] Current database is '{$db->getDatabase()}', not approved disposable test target. Skipping restore.\n";
            }
        } catch (\Throwable $cleanupError) {
            fwrite(STDERR, "\n[CRITICAL CLEANUP ERROR] Bounded cleanup failed: " . $cleanupError->getMessage() . "\n");
            if ($capturedFailure === null) {
                $capturedFailure = $cleanupError;
            }
        }
    }
}

// Outermost exit handling: report failure and exit nonzero if any step failed
if ($capturedFailure !== null) {
    fwrite(STDERR, "\n[VERIFICATION FAILURE] " . $capturedFailure->getMessage() . "\n");
    exit(1);
}

echo "\n>>> ALL CANARY AND GUARDED REFUSAL CHECKS PASSED PERFECTLY! <<<\n";
exit(0);
