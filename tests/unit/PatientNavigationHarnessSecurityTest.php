<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Scripts\TestDatabasePreflight;

require_once dirname(__DIR__, 2) . '/scripts/TestDatabasePreflight.php';

/**
 * Pure database-free security and fail-closed isolation tests for the
 * Patient HTTP navigation test harness and server router.
 */
final class PatientNavigationHarnessSecurityTest extends CIUnitTestCase
{
    private string $repoRoot;
    private string $fixtureScript;
    private string $serverScript;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repoRoot = dirname(__DIR__, 2);
        $this->fixtureScript = $this->repoRoot . '/tests/_support/PatientHttpNavigationFixture.php';
        $this->serverScript = $this->repoRoot . '/tests/_support/test_http_server.php';
    }

    public function testPreflightFailsClosedOnBusinessDatabaseTarget(): void
    {
        $businessTargetConfig = [
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'database' => 'democa_dental_saas', // FORBIDDEN business database
            'DBDriver' => 'MySQLi',
        ];
        $errors = TestDatabasePreflight::validateConfigBeforeConnection($businessTargetConfig, ['database' => 'democa_dental_saas']);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('does not match approved disposable target', implode('; ', $errors));
        $this->assertStringContainsString('CRITICAL ISOLATION FAILURE', implode('; ', $errors));
    }

    public function testPreflightFailsClosedOnDisallowedPortOrRemoteHost(): void
    {
        $remoteHostConfig = [
            'hostname' => '192.168.1.100', // Remote host
            'port' => 3306,
            'database' => 'democa_dental_saas_test',
            'DBDriver' => 'MySQLi',
        ];
        $errors = TestDatabasePreflight::validateConfigBeforeConnection($remoteHostConfig, []);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('not an approved local address', implode('; ', $errors));

        $badPortConfig = [
            'hostname' => '127.0.0.1',
            'port' => 3309, // Non-approved port
            'database' => 'democa_dental_saas_test',
            'DBDriver' => 'MySQLi',
        ];
        $portErrors = TestDatabasePreflight::validateConfigBeforeConnection($badPortConfig, []);
        $this->assertNotEmpty($portErrors);
        $this->assertStringContainsString('not an approved local MySQL port', implode('; ', $portErrors));
    }

    public function testServerScriptFailsClosedUnderCliSapi(): void
    {
        // When invoked from CLI instead of cli-server, test_http_server.php must exit 1 with 403
        $output = [];
        $exitCode = 0;
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($this->serverScript) . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, 'Server router must exit 1 when not invoked via cli-server SAPI');
        $this->assertStringContainsString('cli-server execution only', implode("\n", $output));
    }

    public function testFixtureRefusesUnknownAction(): void
    {
        $output = [];
        $exitCode = 0;
        $cmd = sprintf('%s %s unknown_action a1b2c3d4e5f60718 fake_receipt.json', escapeshellarg(PHP_BINARY), escapeshellarg($this->fixtureScript));
        exec($cmd . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, 'Fixture must exit 1 on unknown action');
        $raw = implode("\n", $output);
        $json = json_decode($raw, true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('Invalid or missing action', $json['error']);
    }

    public function testFixtureRefusesInvalidRunIdOrReceipt(): void
    {
        $output = [];
        $exitCode = 0;
        // Invalid non-hex run_id
        $cmd = sprintf('%s %s provision invalid-run-id fake_receipt.json', escapeshellarg(PHP_BINARY), escapeshellarg($this->fixtureScript));
        exec($cmd . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, 'Fixture must exit 1 on malformed run_id');
        $json = json_decode(implode("\n", $output), true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('Invalid or missing run_id', $json['error']);
    }

    public function testFixtureRefusesTeardownWithoutReceipt(): void
    {
        $output = [];
        $exitCode = 0;
        $nonExistentReceipt = $this->repoRoot . '/writable/tmp/non_existent_receipt_' . bin2hex(random_bytes(4)) . '.json';
        $cmd = sprintf('%s %s teardown a1b2c3d4e5f60718 %s', escapeshellarg(PHP_BINARY), escapeshellarg($this->fixtureScript), escapeshellarg($nonExistentReceipt));
        exec($cmd . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, 'Fixture must exit 1 when teardown receipt is missing');
        $json = json_decode(implode("\n", $output), true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('Receipt file not found for teardown', $json['error']);
    }

    public function testMissingConnectionNeverGuessesCredentials(): void
    {
        require_once $this->repoRoot . '/tests/_support/PatientNavigationConnection.php';
        $names = ['DENTA_VERIFIED_TEST_CONNECTION'];
        foreach (['hostname', 'port', 'database', 'username', 'password', 'DBDriver', 'DBPrefix'] as $key) $names[] = 'database.tests.' . $key;
        $saved = [];
        foreach ($names as $name) { $saved[$name] = getenv($name); putenv($name); }
        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('no credential defaults');
            \Tests\Support\PatientNavigationConnection::load();
        } finally {
            foreach ($saved as $name => $value) $value === false ? putenv($name) : putenv($name . '=' . $value);
        }
    }

    public function testMalformedConnectionFailsBeforeBootstrapOrDatabase(): void
    {
        $previous = getenv('DENTA_VERIFIED_TEST_CONNECTION');
        putenv('DENTA_VERIFIED_TEST_CONNECTION=invalid-json');
        $runId = bin2hex(random_bytes(8));
        $receipt = $this->repoRoot . '/writable/tmp/test_fixture_' . $runId . '.json';
        try {
            $output = []; $exitCode = 0;
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($this->fixtureScript) . ' provision ' . $runId . ' ' . escapeshellarg($receipt) . ' 2>&1', $output, $exitCode);
            $this->assertSame(1, $exitCode);
            $json = json_decode(implode("\n", $output), true);
            $this->assertFalse($json['success']);
            $this->assertSame('Invalid test connection JSON.', $json['error']);
            $this->assertFileDoesNotExist($receipt);
        } finally {
            $previous === false ? putenv('DENTA_VERIFIED_TEST_CONNECTION') : putenv('DENTA_VERIFIED_TEST_CONNECTION=' . $previous);
        }
    }
}
