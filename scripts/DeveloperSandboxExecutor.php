<?php
declare(strict_types=1);

namespace Scripts;

require_once __DIR__ . '/DeveloperSandboxPreflight.php';
require_once dirname(__DIR__) . '/tests/_support/Database/LegacyBaseSchema.php';

use Scripts\DeveloperSandboxPreflight;
use Tests\Support\Database\LegacyBaseSchema;

/**
 * Explicit Test Connection Stub Interface
 * Guarantees that public test mock factories accept ONLY explicit test stubs
 * and can NEVER turn an arbitrary real database connection into a fake-authorized connection.
 */
interface DeveloperSandboxTestConnectionStub
{
    public function query(string $sql, array $params = []);
}

/**
 * Migration Adapter Interface for Developer Sandbox
 * Decouples executor from migration implementation while enforcing that
 * migrations can ONLY be executed through an authoritative MigrationRunner backend.
 */
interface DeveloperSandboxMigrationAdapterInterface
{
    /**
     * Run a specific migration file through the authoritative runner.
     *
     * @param string $filePath Full path to migration file
     * @param string $namespace Namespace ('App')
     * @param string $group Database group ('default')
     * @return bool
     */
    public function runMigrationFile(string $filePath, string $namespace = 'App', string $group = 'default'): bool;
}

/**
 * Authoritative CodeIgniter MigrationRunner Adapter
 * Uses CodeIgniter 4's native MigrationRunner::force() to execute and track migrations.
 */
final class CodeIgniterMigrationRunnerAdapter implements DeveloperSandboxMigrationAdapterInterface
{
    private object $runner;

    public function __construct(?object $runner = null, $db = null)
    {
        if ($runner !== null) {
            $this->runner = $runner;
        } elseif (class_exists('\CodeIgniter\Database\MigrationRunner')) {
            $config = class_exists('\Config\Migrations') ? new \Config\Migrations() : null;
            $this->runner = new \CodeIgniter\Database\MigrationRunner($config ?? new class {
                public bool $enabled = true;
                public string $table = 'migrations';
                public string $timestampFormat = 'Y-m-d-His_';
            }, $db);
        } else {
            throw new \RuntimeException("CRITICAL REFUSAL: CodeIgniter MigrationRunner class is not available.");
        }
    }

    public function runMigrationFile(string $filePath, string $namespace = 'App', string $group = 'default'): bool
    {
        return (bool) $this->runner->force($filePath, $namespace, $group);
    }
}

/**
 * Private Windows / Host Storage Security Guard
 *
 * Implements strict NTFS DACL verification (operator, SYSTEM, Administrators only),
 * reparse/symlink rejection across directory ancestors, atomic exclusive file creation ('xb+'),
 * fstat identity validation, and complete flushed writes.
 */
final class DeveloperSandboxStorageGuard
{
    private static ?string $cachedCurrentUserSid = null;

    public static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    public static function getDefaultStorageDirectory(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'denta_sandbox_vault';
    }

    /**
     * Inspects unresolved path and ancestor components to reject symlinks and junctions BEFORE I/O.
     */
    public static function assertUnresolvedPathSafe(string $path): void
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (preg_match('~(^|[\\\\/])\.\.($|[\\\\/])~', $normalized)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Path traversal sequences are forbidden in private paths.");
        }

        // Check the original spelling before resolving away links.
        $current = '';
        foreach (explode(DIRECTORY_SEPARATOR, $normalized) as $part) {
            if ($part === '') { $current .= DIRECTORY_SEPARATOR; continue; }
            $current = ($current === '' || $current === DIRECTORY_SEPARATOR)
                ? $current . $part : $current . DIRECTORY_SEPARATOR . $part;
            if (is_link($current)) {
                throw new \RuntimeException('CRITICAL REFUSAL: Private path contains a symbolic link / reparse point / junction.');
            }
        }
        if (!self::isWindows()) { return; }

        // One process inspects the complete unresolved ancestry. No cached result
        // or omitted ancestor checks are used to reduce process-start overhead.
        $path64 = base64_encode($normalized);
        $script = '$ErrorActionPreference = "Stop"; '
            . '$p = [IO.Path]::GetFullPath([Text.Encoding]::UTF8.GetString([Convert]::FromBase64String("' . $path64 . '"))); '
            . 'while (-not [string]::IsNullOrEmpty($p)) { '
            . 'try { $item = Get-Item -Force -LiteralPath $p -ErrorAction Stop; '
            . 'if (($item.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) { exit 10 } } '
            . 'catch { if ($_.CategoryInfo.Category -ne [Management.Automation.ErrorCategory]::ObjectNotFound) { throw } }; '
            . '$parent = [IO.Path]::GetDirectoryName($p); if ($parent -eq $p) { break }; $p = $parent }; exit 0';
        $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $script));
        $out = [];
        $code = 0;
        exec('powershell.exe -NoProfile -NonInteractive -EncodedCommand ' . $encoded . ' 2>NUL', $out, $code);
        if ($code === 10) {
            throw new \RuntimeException('CRITICAL REFUSAL: Private path contains a reparse point / junction.');
        }
        if ($code !== 0) {
            throw new \RuntimeException('CRITICAL REFUSAL: Private path inspection failed; unknown ancestry is refused.');
        }
    }

    public static function getCurrentUserSid(): string
    {
        if (self::$cachedCurrentUserSid !== null) {
            return self::$cachedCurrentUserSid;
        }

        if (!self::isWindows()) {
            return (string) getmyuid();
        }

        $script = '$ErrorActionPreference = "Stop"; [Security.Principal.WindowsIdentity]::GetCurrent().User.Value';
        $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $script));
        $out = [];
        $code = 0;
        exec('powershell.exe -NoProfile -NonInteractive -EncodedCommand ' . $encoded . ' 2>NUL', $out, $code);
        $sid = trim(implode('', $out));
        if ($code !== 0 || !preg_match('/^S-1-[0-9-]+$/D', $sid)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Failed resolving current Windows user SID.");
        }

        self::$cachedCurrentUserSid = $sid;
        return $sid;
    }

    /**
     * Ensure private directory exists with strict access restrictions.
     * Inspects unresolved path first. If directory already exists, verifies privacy
     * and REFUSES to modify an insecure existing directory. Hardens only newly created child directories.
     */
    public static function ensurePrivateDirectory(string $dir): void
    {
        self::assertUnresolvedPathSafe($dir);

        $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
        $alreadyExisted = file_exists($dir) || is_dir($dir);

        if ($alreadyExisted) {
            $verifyExisting = self::verifyDirectoryPrivacy($dir, false);
            if (!$verifyExisting['valid']) {
                throw new \RuntimeException("CRITICAL REFUSAL: Destination '{$dir}' already exists and does not satisfy strict private ownership/ACL invariants. Refusing to modify unverified existing directory: " . ($verifyExisting['error'] ?? 'unknown error'));
            }
            return;
        }

        // Newly created directory in private root
        if (!mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Failed creating private directory: {$dir}");
        }

        $resolved = realpath($dir);
        if ($resolved === false || !is_dir($resolved)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Private directory path cannot be resolved: {$dir}");
        }

        if (self::isWindows()) {
            $sid = self::getCurrentUserSid();
            $path64 = base64_encode($resolved);
            $script = '$ErrorActionPreference = "Stop"; '
                . '$p = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String("' . $path64 . '")); '
                . '& icacls $p /inheritance:r /grant:r "*S-1-5-18:(OI)(CI)(F)" "*S-1-5-32-544:(OI)(CI)(F)" "*' . $sid . ':(OI)(CI)(F)" 2>&1';
            $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $script));
            $out = [];
            $code = 0;
            exec('powershell.exe -NoProfile -NonInteractive -EncodedCommand ' . $encoded . ' 2>NUL', $out, $code);

            $verify = self::verifyDirectoryPrivacy($resolved, true);
            if (!$verify['valid']) {
                throw new \RuntimeException("CRITICAL REFUSAL: Private directory NTFS ACL verification failed: " . ($verify['error'] ?? 'unknown error'));
            }
        } else {
            if (!chmod($resolved, 0700)) {
                throw new \RuntimeException("CRITICAL REFUSAL: chmod 0700 failed for private directory: {$resolved}");
            }
            if (is_link($resolved)) {
                throw new \RuntimeException("CRITICAL REFUSAL: Private directory cannot be a symbolic link.");
            }
            $stat = stat($resolved);
            if (!$stat || ($stat['uid'] !== getmyuid()) || (($stat['mode'] & 0777) !== 0700)) {
                throw new \RuntimeException("CRITICAL REFUSAL: Directory ownership or mode check failed on {$resolved}.");
            }
        }
    }

    /**
     * Verifies owner SID and effective NTFS ACLs for directory privacy.
     * Reuses the repository's reviewed F5 NTFS privacy algorithm.
     *
     * @return array{valid: bool, error?: string, owner?: string}
     */
    public static function verifyDirectoryPrivacy(string $path, bool $isExclusiveChild = false): array
    {
        if (is_link($path)) {
            return ['valid' => false, 'error' => 'Private directory cannot be a symbolic link.'];
        }

        $resolved = realpath($path);
        if ($resolved === false || !is_dir($resolved)) {
            return ['valid' => false, 'error' => 'Private directory cannot be resolved.'];
        }

        if (is_link($resolved)) {
            return ['valid' => false, 'error' => 'Private directory cannot be a symbolic link.'];
        }

        if (!self::isWindows()) {
            $stat = stat($resolved);
            if (!$stat || ($stat['uid'] !== getmyuid())) {
                return ['valid' => false, 'error' => 'Private directory owner does not match current process.'];
            }
            if (($stat['mode'] & 0777) !== 0700) {
                return ['valid' => false, 'error' => 'Private directory mode is not 0700.'];
            }
            return ['valid' => true];
        }

        $path64 = base64_encode($resolved);
        $script = '$ErrorActionPreference = "Stop"; '
            . '$p = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String("' . $path64 . '")); '
            . '$item = Get-Item -Force -LiteralPath $p; $reparse = $false; $ancestor = $item; '
            . 'while ($null -ne $ancestor) { if (($ancestor.Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) { $reparse = $true }; $ancestor = $ancestor.Parent }; '
            . '$acl = Get-Acl -LiteralPath $p; '
            . '$rules = @($acl.GetAccessRules($true, $true, [Security.Principal.SecurityIdentifier]) | ForEach-Object { '
            . '@{sid=$_.IdentityReference.Value; type=$_.AccessControlType.ToString(); rights=[int64]$_.FileSystemRights} }); '
            . '@{owner=$acl.GetOwner([Security.Principal.SecurityIdentifier]).Value; '
            . 'current=[Security.Principal.WindowsIdentity]::GetCurrent().User.Value; '
            . 'protected=$acl.AreAccessRulesProtected; reparse=$reparse; rules=$rules} | ConvertTo-Json -Compress -Depth 4';
        $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $script));
        $lines = [];
        $code = 0;
        exec('powershell.exe -NoProfile -NonInteractive -EncodedCommand ' . $encoded . ' 2>NUL', $lines, $code);
        $acl = json_decode(implode("\n", $lines), true);

        if ($code !== 0 || !is_array($acl) || !is_string($acl['current'] ?? null)
            || !preg_match('/^S-1-[0-9-]+$/D', $acl['current'])
            || ($acl['reparse'] ?? true) !== false || !is_array($acl['rules'] ?? null) || !$acl['rules']) {
            return ['valid' => false, 'error' => 'Could not verify private directory security identifiers or reparse ancestry.'];
        }

        $trusted = [$acl['current'], 'S-1-5-18', 'S-1-5-32-544'];
        if (!in_array($acl['owner'] ?? null, $trusted, true)) {
            return ['valid' => false, 'error' => 'Private directory owner is not the operator, SYSTEM or Administrators.'];
        }

        foreach ($acl['rules'] as $rule) {
            if (($rule['type'] ?? '') === 'Allow' && ($rule['rights'] ?? 0) !== 0
                && !in_array($rule['sid'] ?? null, $trusted, true)) {
                return ['valid' => false, 'error' => 'Private directory grants access to an untrusted principal.'];
            }
        }

        if ($isExclusiveChild && ($acl['protected'] ?? false) !== true) {
            return ['valid' => false, 'error' => 'Exclusive child directory inherits permissions.'];
        }

        return ['valid' => true, 'owner' => $acl['owner']];
    }

    /**
     * Atomically creates an exclusive file refusing collisions, symlinks, or existing files.
     *
     * @return resource File handle
     */
    public static function createExclusiveFile(string $filePath)
    {
        self::assertUnresolvedPathSafe($filePath);
        $dir = dirname($filePath);
        self::ensurePrivateDirectory($dir);

        if (file_exists($filePath) || is_link($filePath)) {
            throw new \RuntimeException("CRITICAL REFUSAL: File already exists or is a link: {$filePath}. Collision refused.");
        }

        $fp = fopen($filePath, 'xb+');
        if (!$fp) {
            throw new \RuntimeException("CRITICAL REFUSAL: Failed exclusive creation for file: {$filePath}");
        }

        $stat = fstat($fp);
        if (!$stat || ($stat['mode'] & 0170000) !== 0100000) {
            fclose($fp);
            unlink($filePath);
            throw new \RuntimeException("CRITICAL REFUSAL: Target is not a regular file: {$filePath}");
        }

        if (!self::isWindows()) {
            if (!chmod($filePath, 0600)) {
                fclose($fp);
                unlink($filePath);
                throw new \RuntimeException("CRITICAL REFUSAL: chmod 0600 failed on {$filePath}.");
            }
        }

        return $fp;
    }

    /**
     * Checked write and complete flush.
     */
    public static function writeAndFlush($handle, string $data): void
    {
        if (!is_resource($handle)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Invalid file handle for write operation.");
        }

        $written = fwrite($handle, $data);
        if ($written !== strlen($data)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Incomplete write operation (wrote {$written} of " . strlen($data) . " bytes).");
        }

        if (fflush($handle) === false) {
            throw new \RuntimeException("CRITICAL REFUSAL: fflush failed for write operation.");
        }
        if (function_exists('fsync')) {
            if (!fsync($handle)) {
                throw new \RuntimeException("CRITICAL REFUSAL: fsync failed for write operation.");
            }
        }
    }
}

/**
 * Durable Private Progress Journal for Developer Sandbox Execution
 *
 * Appends write-ahead and execution events to a restricted, exclusive journal file
 * with write-ahead flushing to disk. Refuses symbolic links, enforces 0600 mode,
 * and throws on closed or broken handle without silent in-memory buffering.
 */
final class DeveloperSandboxJournal
{
    private string $filePath;
    /** @var resource|null */
    private $handle = null;
    private array $inMemoryEntries = [];

    private function __construct(string $filePath, $handle)
    {
        $this->filePath = $filePath;
        $this->handle = $handle;
    }

    public static function create(?string $storageDir, string $runId): self
    {
        $dir = $storageDir ?? DeveloperSandboxStorageGuard::getDefaultStorageDirectory();
        $path = $dir . '/' . $runId . '.journal.jsonl';
        if (file_exists($path)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Journal file already exists for run ID '{$runId}'. Collision refused.");
        }

        $handle = DeveloperSandboxStorageGuard::createExclusiveFile($path);
        return new self($path, $handle);
    }

    public function record(string $stage, string $event, array $details = []): void
    {
        if ($this->handle === null || !is_resource($this->handle)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Journal file handle is closed or broken. Durable write cannot proceed.");
        }

        $entry = [
            'timestamp' => microtime(true),
            'time_iso' => date('Y-m-d H:i:s'),
            'stage' => $stage,
            'event' => $event,
            'details' => $details,
        ];

        $this->inMemoryEntries[] = $entry;

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES) . "\n";
        DeveloperSandboxStorageGuard::writeAndFlush($this->handle, $line);
    }

    public function getEntries(): array
    {
        return $this->inMemoryEntries;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function close(): void
    {
        if ($this->handle) {
            fflush($this->handle);
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}

/**
 * Exclusive Process Lock for Developer Sandbox Execution
 * Serializes bootstrap processes to prevent concurrent executions.
 * Maintains a stable lock inode across releases without unlinking.
 */
final class DeveloperSandboxProcessLock
{
    private string $lockFile;
    /** @var resource|null */
    private $handle = null;

    public function __construct(string $lockFile)
    {
        $this->lockFile = $lockFile;
        $dir = dirname($lockFile);
        DeveloperSandboxStorageGuard::ensurePrivateDirectory($dir);

        // Open stable lock inode (do NOT unlink on release to prevent splitting locks across inodes)
        $fp = fopen($lockFile, 'c+');
        if (!$fp) {
            throw new \RuntimeException("CRITICAL REFUSAL: Failed opening lock file: {$lockFile}");
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            throw new \RuntimeException("CRITICAL REFUSAL: Developer sandbox bootstrap process lock is held by another active run. Concurrent execution refused.");
        }

        $this->handle = $fp;
        ftruncate($fp, 0);
        fwrite($fp, (string) getmypid() . "\n" . date('Y-m-d H:i:s') . "\n");
        fflush($fp);
    }

    public static function acquire(?string $storageDir): self
    {
        $dir = $storageDir ?? DeveloperSandboxStorageGuard::getDefaultStorageDirectory();
        return new self($dir . '/bootstrap.lock');
    }

    public function release(): void
    {
        if ($this->handle) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
            $this->handle = null;
            // Stable inode preserved: do NOT unlink($this->lockFile)
        }
    }

    public function __destruct()
    {
        $this->release();
    }
}

/**
 * Private Developer Credentials Generator and Descriptor
 *
 * Generates usable private credentials for developer personas and persists them
 * strictly to an exclusive, owner-only (0600/protected NTFS DACL) descriptor file
 * so the incoming developer can authenticate without hardcoding or leaking passwords into Git or logs.
 */
final class DeveloperSandboxCredentials
{
    private string $filePath;
    private array $credentialsData = [];

    private function __construct(string $filePath, array $credentialsData)
    {
        $this->filePath = $filePath;
        $this->credentialsData = $credentialsData;
    }

    public static function generateAndPersist(?string $storageDir, string $runId): self
    {
        $dir = $storageDir ?? DeveloperSandboxStorageGuard::getDefaultStorageDirectory();
        $filePath = $dir . '/' . $runId . '.credentials.json';
        if (file_exists($filePath)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Credentials file already exists for run '{$runId}'.");
        }

        $handle = DeveloperSandboxStorageGuard::createExclusiveFile($filePath);

        $personas = [
            'superadmin' => ['username' => 'superadmin', 'email' => 'admin@sandbox.test', 'role' => 'super_admin', 'clinic_id' => null],
            'manager' => ['username' => 'manager', 'email' => 'manager@alpha.test', 'role' => 'practice_manager', 'clinic_id' => 1],
            'doctor' => ['username' => 'doctor', 'email' => 'doctor@alpha.test', 'role' => 'doctor', 'clinic_id' => 1],
            'reception' => ['username' => 'reception', 'email' => 'reception@alpha.test', 'role' => 'receptionist', 'clinic_id' => 1],
            'foreign_staff' => ['username' => 'foreign_staff', 'email' => 'staff@beta.test', 'role' => 'receptionist', 'clinic_id' => 2],
        ];

        $generated = [];
        foreach ($personas as $key => $meta) {
            $plain = bin2hex(random_bytes(16)) . '!A2026';
            $hash = password_hash($plain, PASSWORD_BCRYPT);
            $generated[$key] = [
                'username' => $meta['username'],
                'email' => $meta['email'],
                'role' => $meta['role'],
                'clinic_id' => $meta['clinic_id'],
                'password_plain' => $plain,
                'password_hash' => $hash,
            ];
        }

        $payload = [
            'run_id' => $runId,
            'created_at' => date('Y-m-d H:i:s'),
            'personas' => $generated,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        DeveloperSandboxStorageGuard::writeAndFlush($handle, $json);
        fclose($handle);

        return new self($filePath, $generated);
    }

    public function getPersona(string $key): array
    {
        if (!isset($this->credentialsData[$key])) {
            throw new \InvalidArgumentException("Unknown persona '{$key}'.");
        }
        return $this->credentialsData[$key];
    }

    public function getPasswordHash(string $key): string
    {
        return $this->getPersona($key)['password_hash'];
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}

/**
 * Validated Execution Context for Developer Sandbox Operations
 *
 * Encapsulates the unique execution token, target database, execution mode,
 * dynamic private credentials, verification state, stage lifecycle phase, and progress journal.
 * Cannot be forged by callers: constructor is strictly private. Context retains the actual
 * connection object reference and strictly enforces one-use stage transitions.
 */
final class DeveloperSandboxRunContext
{
    public const PHASE_INITIALIZED = 0;
    public const PHASE_STAGE1_DONE = 1;
    public const PHASE_STAGE2_DONE = 2;
    public const PHASE_STAGE3_DONE = 3;
    public const PHASE_STAGE4_DONE = 4;
    public const PHASE_STAGE5_DONE = 5;
    public const PHASE_STAGE6_DONE = 6;
    public const PHASE_STAGE7_DONE = 7;
    public const PHASE_STAGE8_DONE = 8;

    private string $runId;
    private string $mode;
    private string $targetDb;
    private object $connection; // Retains actual connection object reference
    private DeveloperSandboxJournal $journal;
    private DeveloperSandboxCredentials $credentials;
    private ?DeveloperSandboxProcessLock $processLock;
    private bool $isVerified = false;
    private bool $isTestMockRun = false;
    private bool $isLiveApply = false;
    private int $currentPhase = self::PHASE_INITIALIZED;

    /**
     * Private constructor enforces that context can ONLY be created through authoritative issuing factory.
     */
    private function __construct(
        string $runId,
        string $mode,
        string $targetDb,
        object $connection,
        DeveloperSandboxJournal $journal,
        DeveloperSandboxCredentials $credentials,
        ?DeveloperSandboxProcessLock $processLock,
        bool $isVerified,
        bool $isTestMockRun,
        bool $isLiveApply
    ) {
        $this->runId = $runId;
        $this->mode = $mode;
        $this->targetDb = $targetDb;
        $this->connection = $connection;
        $this->journal = $journal;
        $this->credentials = $credentials;
        $this->processLock = $processLock;
        $this->isVerified = $isVerified;
        $this->isTestMockRun = $isTestMockRun;
        $this->isLiveApply = $isLiveApply;
        $this->currentPhase = self::PHASE_INITIALIZED;
    }

    /**
     * Authoritative Internal Factory (strictly private)
     */
    private static function mintContext(
        string $runId,
        string $mode,
        string $targetDb,
        object $connection,
        DeveloperSandboxJournal $journal,
        DeveloperSandboxCredentials $credentials,
        ?DeveloperSandboxProcessLock $processLock,
        bool $isVerified,
        bool $isTestMockRun,
        bool $isLiveApply
    ): self {
        return new self(
            $runId,
            $mode,
            $targetDb,
            $connection,
            $journal,
            $credentials,
            $processLock,
            $isVerified,
            $isTestMockRun,
            $isLiveApply
        );
    }

    public function getRunId(): string { return $this->runId; }
    public function getMode(): string { return $this->mode; }
    public function getTargetDb(): string { return $this->targetDb; }
    public function isVerified(): bool { return $this->isVerified; }
    public function isTestMockRun(): bool { return $this->isTestMockRun; }
    public function isLiveApply(): bool { return $this->isLiveApply; }
    public function getJournal(): DeveloperSandboxJournal { return $this->journal; }
    public function getCredentials(): DeveloperSandboxCredentials { return $this->credentials; }
    public function getConnection(): object { return $this->connection; }
    public function getCurrentPhase(): int { return $this->currentPhase; }

    public function isBoundToConnection(object $db): bool
    {
        return $this->connection === $db;
    }

    /**
     * Internal Stage Lifecycle Transition Boundary
     * Strictly verifies that:
     * 1. Supplied connection reference exactly matches context binding ($db === $this->connection).
     * 2. Caller is the exact executing DeveloperSandboxExecutor::$callingStageMethod via backtrace.
     * 3. Transition follows the exact sequential stage order: 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7 -> 8.
     *
     * External code CANNOT advance phase, skip stages, or manufacture state.
     */
    public function internalAdvancePhase(string $callingStageMethod, object $db): void
    {
        if ($this->connection !== $db) {
            throw new \RuntimeException("CRITICAL REFUSAL: Run context is not bound to the supplied database connection.");
        }

        $stageOrder = [
            'executeStage1_VerifyEmptinessAndCreateMarker' => [self::PHASE_INITIALIZED, self::PHASE_STAGE1_DONE],
            'executeStage2_ApplyLegacyFoundation' => [self::PHASE_STAGE1_DONE, self::PHASE_STAGE2_DONE],
            'executeStage3_ExecuteMigrations' => [self::PHASE_STAGE2_DONE, self::PHASE_STAGE3_DONE],
            'executeStage4_InitializeUser1AndSyncRbac' => [self::PHASE_STAGE3_DONE, self::PHASE_STAGE4_DONE],
            'executeStage5_SeedSyntheticTenantsAndPlans' => [self::PHASE_STAGE4_DONE, self::PHASE_STAGE5_DONE],
            'executeStage6_SeedCanonicalSettings' => [self::PHASE_STAGE5_DONE, self::PHASE_STAGE6_DONE],
            'executeStage7_SeedDeveloperPersonasAndMemberships' => [self::PHASE_STAGE6_DONE, self::PHASE_STAGE7_DONE],
            'executeStage8_VerifyMessagingTransportLockdown' => [self::PHASE_STAGE7_DONE, self::PHASE_STAGE8_DONE],
        ];

        if (!isset($stageOrder[$callingStageMethod])) {
            throw new \RuntimeException("CRITICAL REFUSAL: Unknown stage method '{$callingStageMethod}'.");
        }

        [$requiredCurrent, $nextPhase] = $stageOrder[$callingStageMethod];

        // Verify caller via backtrace
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerClass = $trace[1]['class'] ?? '';
        $callerMethod = $trace[1]['function'] ?? '';
        if ($callerClass !== DeveloperSandboxExecutor::class || $callerMethod !== $callingStageMethod) {
            throw new \RuntimeException("CRITICAL REFUSAL: Stage transition can only be performed by the executing stage method in DeveloperSandboxExecutor.");
        }

        if ($this->currentPhase !== $requiredCurrent) {
            throw new \RuntimeException("CRITICAL REFUSAL: Invalid stage transition. Current phase is {$this->currentPhase}, expected {$requiredCurrent}. Repeat or out-of-order stage execution is strictly forbidden.");
        }

        $this->currentPhase = $nextPhase;
    }

    public function recordJournal(string $stage, string $event, array $details = []): void
    {
        $this->journal->record($stage, $event, $details);
    }
}

/**
 * Concrete Execution Engine for Developer Sandbox Bootstrap (REFUSED UNTIL AUTHORIZED)
 *
 * Implements the full, multi-stage synthetic database provisioning sequence for the
 * developer sandbox database (democa_dental_saas_dev on 127.0.0.1:3307):
 *
 *  - Stage 1: Preflight connection validation & target emptiness verification.
 *  - Stage 2: Legacy base schema foundation (18 pre-migration tables).
 *  - Stage 3: Canonical migrations execution through real MigrationRunner with exact history delta verification.
 *  - Stage 4: User 1 initialization & canonical RBAC sync (canonical slug resolution; exact schema).
 *  - Stage 5: Synthetic tenants (2 clinics) and baseline plan seeding (clinics created BEFORE clinic settings).
 *  - Stage 6: Canonical settings seeding (idempotent upsert; global defaults + clinic settings).
 *  - Stage 7: Synthetic developer personas with resolved role memberships in clinic_users.
 *  - Stage 8: Outbound messaging transport lockdown.
 *
 * POLICY INVARIANTS:
 *  - Live execution is unconditionally refused until authorized by Codex.
 *  - Stage methods require a verified DeveloperSandboxRunContext bound by object identity to the connection.
 *  - Enforces one-use stage order; repeat stage calls throw immediately.
 *  - Checks every query, DDL, insert, and migration result for false/null.
 *  - Inspects MigrationRunner history delta (+1 per migration) and rejects lock contention or no-progress.
 *  - On partial failure, halts execution and persists evidence to durable journal.
 *  - Application tables: 68 (baseline) or 81 (all-source). Total tables including marker: 69 or 82.
 *  - Table counts alone do NOT certify column/index/FK parity with production.
 */
class DeveloperSandboxExecutor
{
    public const MODE_PRODUCTION_BASELINE = 'production-baseline';
    public const MODE_ALL_SOURCE = 'all-source';

    public const SUPPORTED_MODES = [
        self::MODE_PRODUCTION_BASELINE,
        self::MODE_ALL_SOURCE,
    ];

    public const EXPECTED_APP_TABLES_BASELINE = 68;
    public const EXPECTED_APP_TABLES_ALL_SOURCE = 81;
    public const EXPECTED_TOTAL_TABLES_BASELINE = 69; // 68 app tables + 1 _dev_sandbox_marker
    public const EXPECTED_TOTAL_TABLES_ALL_SOURCE = 82; // 81 app tables + 1 _dev_sandbox_marker

    public const EXCLUDED_MIGRATIONS = [
        '2026-09-26-000001_AddDynamicDoctorScheduling.php',
        '2026-10-21-190000_CreateInvoiceCorrectionLedger.php',
        '2026-10-22-150000_ScopePatientEmailUniqueness.php',
        '2026-10-22-160000_CreateAttachmentVariants.php',
        '2026-10-22-170000_CreateFinanceCheckoutRequests.php',
        '2026-10-22-180000_CreateAppointmentContactNotes.php',
        '2026-10-22-190000_AddClinicalChargeAppointmentIndex.php',
        '2026-10-28-120000_CreateStudioThemeHistory.php',
    ];

    public const REVIEWED_FEATURE_COMMIT = '7427b4a43984666596455103fee00ab40e6633d7';

    public const REVIEWED_FOUNDATION_FIXTURE_HASH = '0dc7d983cb96dcb2c01958e4523c2c7355c08125438bd1c05f01e1820c5d074d';
    public const REVIEWED_WORKER_SOURCE_HASH = '6dca6f6fb86b8b8af8162a7b9c4bfc29bc0124e62a318c6d47e9a210a52bd8d7';
    public const REVIEWED_PREFLIGHT_SOURCE_HASH = '1f34b5c9307711e29fba759479facde6fb6f7dbbfd7c3cf1f25356d8fe5bffb3';

    /**
     * Immutable reviewed SHA256 manifest of all 85 migration files pinned at reviewed feature commit 7427b4a4.
     * Prevents execution against an unreviewed or modified working tree.
     */
    public const REVIEWED_MIGRATION_MANIFEST = [
        '2025-01-19-000001_CreateRBACTables.php' => '3f6139678297540dbf73632820cba5606ede57dae8d25c31b84af41b20038f53',
        '2025-01-19-000002_CreateActivityLogsTable.php' => '2c0a2952f6cd0d5f1f09e5f0317c87c612c7a0f672df6fec4752ba8f492bd92e',
        '2025-01-19-000003_AddIsDoctorToRoles.php' => '8bd44c41cc3417dff99ac6053127735f5be95890a3e82da1c53d813c83797872',
        '2026-01-04-000004_UpdateFinancesCurrencyEnum.php' => 'bc624be38867a2649c67a62ddd43a7b8c995fe31e87c473f67e5c3ffee23a592',
        '2026-01-04-000005_AddTotalAmountToFinances.php' => 'b415d1cb05e58bcb97de61a1af35fa77f3ad7113c663d7c396270238f55cd680',
        '2026-01-04-000006_AddHireDateToUsersTable.php' => '3b2af327698c0a810066a4d765fb67505cedf731b3bb111b552065b4894a290a',
        '2026-01-13-204835_CreateClinicsAndMemberships.php' => '2efeb7ccf6772c440f77b4e41ab64d87a26275052f3615a2bc569eecda8b54b7',
        '2026-01-14-101059_AddClinicIdToTenantTables.php' => '6047a2a6aab9886834be88e3c2eb07105f405b746053f3dd77fa182770885e9b',
        '2026-01-14-101229_BackfillClinicId.php' => 'afb9f0926b83af0f008dc6eb354870ec7f11d327cf86482cb69e8a541dbe000e',
        '2026-01-14-102341_EnforceClinicIdConstraints.php' => '4c7727f78e60b75b751056316cf4746b373e4b5f3dddac649a1b972ffb2e72f0',
        '2026-01-14-174315_CreateFileAttachmentsTable.php' => '7144d1a58604994b66878e0c92abe12aeab29568fbf7eda852793a85922663ab',
        '2026-01-15-000000_AdjustSettingsUniqueIndex.php' => '4b68709a3b2d00a7399e718121de9d766cf30979fcdf07b3e6336eb114c39de5',
        '2026-01-15-085119_FixMedicalRolesAndSyncPermissions.php' => '2403f518809949556781e7fa99b6894112d1e699ea1ae285eb75b034fa6bd264',
        '2026-01-15-091550_PurgeTenantRoles.php' => '554269ed3383278bc2744f21da32981de7a1745b653c577dbe177f0b8dca943e',
        '2026-01-15-092851_AddPurposeAndHashToFileAttachments.php' => 'b258f7eb52516c3d1205e171f11c372792c60c76b04a30dc8cb0c6fe20bfd45f',
        '2026-01-15-110941_CreateJobAuditsTable.php' => '4be3cf8b644649c94f36eb052159a4f8eb116727c604b3b73e9af7e2239add92',
        '2026-01-15-203050_CreateClinicNotificationChannelsTable.php' => 'cc8449b166d2e8fbbbb7b7729c8d87f5b4ca74761c423581dad87ea98850566f',
        '2026-01-15-203054_CreateNotificationsTable.php' => '8528696144eecd083a65ee379a7594cfe2640d8b683ddcb10d429b04c516f746',
        '2026-01-15-212052_AddParentNotificationIdToNotifications.php' => '3627a2dcbf9c36005939d8005db2d30f9ac85ac0665a20fd658ced9598eca05b',
        '2026-01-15-213930_CreatePlansAndSubscriptionsTables.php' => '2f646e45fea00ba7e0ae055dca9f7b87815d86aca00dbd4622be7f02ced4a89c',
        '2026-01-16-220000_CreateControlPlaneAudits.php' => '2956f845dc3310821c901cc8706bd341175a3c1f6964cc9618a45c78926be4b0',
        '2026-01-16-222000_AddPreferencesToClinicUsers.php' => '00562873e5277bb78e21170794330420cb0aeb0e64296b952cf887392f7dfc76',
        '2026-01-18-000001_CreateApiTokensTable.php' => '3b7b18293d397b149407ec02411c00e7af20d3f8005fd332f4f9865857d63bee',
        '2026-01-21-000001_AddNotesToPrescriptions.php' => '274963bf4097a04ed465e05043862e7e6c1663822f245533e05717d49591413b',
        '2026-01-22-000001_AddClinicIdToSummaryTables.php' => 'a49a6439725af92527cfa82e54cd939bababa2b1c86b5b9695adae92a6224c9f',
        '2026-01-22-200000_CreatePlanRequestsTable.php' => '9b10e51faeb9e464192821eb2f09dec9293f694c54c2ff6f5c2cb5c37f851317',
        '2026-01-23-070700_AddSubscriptionLifecycleReasons.php' => '4e69b3c85283911ad68650afb0587f7f33d823f14777ba4b6a532a450b63fb9e',
        '2026-01-23-091500_CreateEmailTemplates.php' => 'd1e4897313875f52cc194fbaf53afd61f81f4b940287268d123da69e169e2ac6',
        '2026-01-23-220000_AddClinicUuidAndDomain.php' => '1974571bf598b39af1d7b3f585c8416e0d06546797e42484f3a640f73c9bd97d',
        '2026-01-26-114500_CreateControlPlaneInvoicesTables.php' => '2d1884a07371664a1bac794f46ea33cdf3f03ff04fc59dcfc232bc206925334d',
        '2026-01-28-130000_AddClinicIdToRoles.php' => '3cbca640c6d5879ec5a1af1a1696bfedc5944666263430cea94abe415b8fb656',
        '2026-02-05-120000_AddAttachmentMetadataToFileAttachments.php' => '578cf8e1bc2e0762ea291e7301575a4ff1905831b1080e1e643ae8a6ed3fa4a1',
        '2026-02-05-130000_CreateTreatmentContractsTables.php' => '78c1e7b79545b7225bc20478b1ecac700678f479f8500123e951e57a39cfd96d',
        '2026-02-05-131000_AddContractFields.php' => '31f730bb845d81de4380ca004ba36e41ef86ed64a53154dccf651d4a4fe5a8a8',
        '2026-02-07-223000_AddFinancePaymentsAndBalances.php' => 'd0287f7fe3c98ef3965a99623b2f9f98d0444adcc1cb57ae2583171e7041817d',
        '2026-02-08-000001_UpdateFinancePaymentsTypes.php' => '6baead60b13f0d2f15c3e1371f12dbd6085e2fe92800d5540c2715c960e8d717',
        '2026-02-08-000002_EnsureIonAuthDefaultGroup.php' => '9893ba4d244872a246821dfed7250b63ebdf444ec20e86424f7a6433bcdfd5c3',
        '2026-02-09-100500_AllowNullClinicIdForGlobalSettings.php' => '49122068b338b454ab7108d775f923a4225b5b8bedfb2741b9c4aef7e9858e5f',
        '2026-02-09-110000_AddControlPlaneSettingsDefaults.php' => 'b7b97f5461ac7653b1471d92c736db179106a10c6fd37a2b626931dec9e6c66d',
        '2026-02-09-200000_AddTaxPercentageToFinances.php' => 'a7dc408105d23fe7371fed58c1fc733facc5ec27df5c99359a207d451f5f4e64',
        '2026-02-09-210000_RemoveNextAppointmentFromExaminations.php' => 'b87a893733da34457675187f68f9dd2924c9bba0e9358d65f55870eee2ec45dd',
        '2026-02-09-220000_RemoveTreatmentPlanFromExaminations.php' => 'c0216d2dcbbe9c98de18f7bf9006dfe5067251b773c0c7a92aec27b745e344f6',
        '2026-02-09-230000_AddExaminationIdToTreatmentContracts.php' => 'ec4ce90e887671742eebbccb0fa24d62190360256a4cc01e393f33a6a0621c06',
        '2026-02-09-235000_AddFinanceExchangeFields.php' => 'edc61a8c9fd677b4035d7900c486febf2b8a45e2ffe87f149c4881bb00068e7f',
        '2026-02-10-000001_AddCreatedByToCoreTables.php' => 'd24cf60aaf19ac2953888d8218f7e0e276dd976278c3a3a9754e8d5c32158f1e',
        '2026-02-16-000001_UpdateFinancesCurrencyEnumAddJod.php' => '9e95963adbb0301ec45a0c8a6c7b1ee59c817940b74347097162ef06ac649404',
        '2026-02-16-000002_AddAdjustmentTypeToFinancePayments.php' => '37ca87c450b74e5031af1cd9dbe6b75b3d56c0df89609b78d919996d8b18e777',
        '2026-02-18-120000_AddChairNameToAppointments.php' => '10a2f56ae21ce62712bcf694c308c65c4203329ed24d85579e84005122f643ce',
        '2026-02-19-120000_AddProviderPayouts.php' => '8f029ad5c11ec41d73c6144b4584ef88a108f7c1fc121192112904472841473a',
        '2026-02-22-180000_UpdateControlPlaneLocaleCurrencyDefaults.php' => '3a2b424ee33c5118d4a4ee81cd72cf4f608e305bc263160058dd736889b6e5bb',
        '2026-02-22-190000_AddPatientMedicalAlertFields.php' => '1277680903959c39e5cda8717603110d6de11a20dfa9ae16fcbe5198ae90184d',
        '2026-02-22-200000_AddExaminationHealthUpdateFields.php' => 'bff17d0a5fea75f8b208aaa7946c940ec82127ff32632ae7c4e61339584de632',
        '2026-02-24-120000_HardenNotificationsPhase0.php' => 'c0e7ffde60c79827668276c2ced99a468639e06ec96ed8ec58d9bfb79af874c3',
        '2026-02-24-140000_CreateWhatsAppPhase1Tables.php' => '780d2349e647ff7a2d1f174fab8f06347a774b3b7de3fc3c6542c19c4ee332f3',
        '2026-02-24-140100_FixWhatsAppOptoutsPatientForeignKey.php' => '85dac6602e8cfe2a37c450b3a4fbfe6b0e6633f7a3b7821ef91b04d3ad482a00',
        '2026-02-26-120000_AddExpenseSupportToFinances.php' => 'b1c60dafa9692555bcd13596fe92bfab3f0af3d6c56d5e246e1e4a101e5b05f4',
        '2026-02-26-140000_AddFinanceSourceReferences.php' => '6d7da07d1977600a0879dfa35c33ead56df74f1b1b9001ff82e05ed20457653c',
        '2026-02-26-160000_AddInventorySuppliersAndMovements.php' => '0680a4b65b159bce33badca1f4e97c1c175c21af19d053887277f10a6496fbb0',
        '2026-02-26-170000_AddInventoryItemTemplates.php' => 'a538a4564775061b460855cc3a6a7c52ce570beecba9d797c139d2dfeb5613c3',
        '2026-02-26-180000_AddInventorySupplierLinksAndReorderList.php' => '5819371b1980635d60251eadebdf3b8896cd32f34d9ed101abce51f10aea06c6',
        '2026-02-26-190000_AddInventoryBatchesAndReorderContactTracking.php' => '8050adaa39e531381d9f43d70a9f4a5875f7bd3af8b90ac15cfede90e4643557',
        '2026-02-28-140000_RemoveDeprecatedPatientContactColumns.php' => 'a92fd4cf7b5fc88dc48a8ad4c31be851ce2003a6a260e59967b334bf74f69b12',
        '2026-03-19-120000_AddExchangeFieldsToFinancePayments.php' => '0f98da1292e0baa183593df4f4e234e43bdc136d53ccdf9b088fe8aa73e330a7',
        '2026-03-22-120000_AddWhatsAppScopedIdentityFields.php' => '8116a4c5cdd5d85550ce33c83b782942efd282b0768621e8c6a0ca16b2786862',
        '2026-03-28-120000_AddTransactionDateToFinances.php' => 'a832c396dc05a00fec2d75b98f8c142441f036bff3c78596cf1bea307917b469',
        '2026-03-29-210000_AddFinanceTreasuryCustody.php' => '03d60f157ba0a92d65e9f54aff33b1d762854e2e2a7c3f9b1c5d14e9c348b06c',
        '2026-04-12-200000_CreatePatientOdontogramSnapshotsTable.php' => '12fd807e3ec8a7313d2b15ff5326db56fc24c8bc21b2252261edf1a81d984610',
        '2026-04-20-120000_AddSupplierIdToFinances.php' => 'eaa63fe30d48281f0f44c934afc4387e931a527286bab2451f37ba0d23e6c857',
        '2026-05-20-120000_CreateClinicalChargeLinesTable.php' => 'a0df1d126118d87bc674a2fc32c7bcad9804ec0e9ccdb41cb6bc2280e8a3a8a9',
        '2026-07-30-120000_ExpandAppointmentVisitStages.php' => '58cb1a9ec918ff27e2c4e4b8f9fd1679e5178f1172f9378bd37145ae40f99dee',
        '2026-07-30-140000_CreateClinicalProcedureLifecycleTables.php' => '6a861580ab7d84e01cb1edfd969621f4684d4cf9af4af86d323f8b56efb852a9',
        '2026-08-01-120000_AddAppointmentContextToExaminations.php' => 'f26e055401b15097a1938ad3ce96118882ee0eca1cf95ca1d59a038954021bf6',
        '2026-08-02-120000_CreateExaminationClinicalNoteGovernanceTables.php' => '68a540d9308934005f3e318616f887d400bec5e177d5fecf2df87e028c5b3bc7',
        '2026-08-02-150000_AddScheduledAppointmentToClinicalProcedures.php' => '0d8358d5a16fc1dcd55da508d66c27b23bdadfd01c97d8466f8edb55ff95e18e',
        '2026-09-14-200000_EnsureControlPlaneInvoicesTables.php' => '0bda82821a6b8f138fbe831ff3bf07b8ccc9200d2530a5f3a437dbe874f10ac5',
        '2026-09-26-000001_AddDynamicDoctorScheduling.php' => '7834a27aedc22f3fce59cdc55027a897b40e83f6adcbce63b04b5b2a1c5ec876',
        '2026-10-21-190000_CreateInvoiceCorrectionLedger.php' => '03432e6aa3abafd82f141f10499bcfd1d56ec515f441037d214f712558323e31',
        '2026-10-22-150000_ScopePatientEmailUniqueness.php' => 'b6ac807aa7efef26e6c252a97abd0f4fb7a6851b5167153d7ca0b51d5fc49393',
        '2026-10-22-160000_CreateAttachmentVariants.php' => '02b6a201298bd42c2ae36e155783a926961e03b6ac1bf3e149e1482d907d6909',
        '2026-10-22-170000_CreateFinanceCheckoutRequests.php' => '0650c73f47cb919233b4acb91f8abf7a0e7e3cabd83427466f60f910db7a8279',
        '2026-10-22-180000_CreateAppointmentContactNotes.php' => '6d4ed427855ccd3de8003384680b9bfd65686ea62b93d2a1b3f52bd58c2ac48f',
        '2026-10-22-190000_AddClinicalChargeAppointmentIndex.php' => '7dc3adbb6bde2eaaf9729fab3bb63ee7de6fd8cb337461a29234cac5308131bf',
        '2026-10-28-120000_CreateStudioThemeHistory.php' => '1746ba5e07d33803a2ec7109db378f93a4e86c1cbb4bafa0b7e71fd4f1a8efc9',
        '2026-10-29-120000_WidenTreatmentTypeStorage.php' => '594f3154c943972b00f54b3439889400dcdca84c6e82f991c0e7e55e10d1597d',
        '2026-10-29-180000_CreateFinanceSettlementRequests.php' => '9428afc527cf7f878e7a4db842804d3169e883915209c99498ef179bbfd91c1e',
    ];

    /**
     * Verify that all migration files in directory match the immutable reviewed manifest.
     *
     * @param string $migrationDir
     * @return array{valid: bool, reviewed_commit: string, total_reviewed_files: int, errors: string[]}
     */
    public static function verifyMigrationManifestAgainstReviewed(string $migrationDir = ''): array
    {
        $dir = $migrationDir !== '' ? $migrationDir : dirname(__DIR__) . '/app/Database/Migrations';
        $errors = [];
        $files = glob($dir . '/*.php') ?: [];
        sort($files);

        $presentFiles = [];
        foreach ($files as $filePath) {
            $base = basename($filePath);
            $presentFiles[$base] = true;

            if (!isset(self::REVIEWED_MIGRATION_MANIFEST[$base])) {
                $errors[] = "Unreviewed or unexpected migration file detected: '{$base}'.";
                continue;
            }

            $expectedHash = self::REVIEWED_MIGRATION_MANIFEST[$base];
            $actualHash = hash_file('sha256', $filePath);
            if (!hash_equals($expectedHash, (string) $actualHash)) {
                $errors[] = "Migration file '{$base}' hash mismatch. Expected immutable reviewed pin: {$expectedHash}, actual on disk: {$actualHash}.";
            }
        }

        foreach (self::REVIEWED_MIGRATION_MANIFEST as $expectedBase => $expectedHash) {
            if (!isset($presentFiles[$expectedBase])) {
                $errors[] = "Reviewed migration file missing from disk: '{$expectedBase}'.";
            }
        }

        return [
            'valid' => empty($errors),
            'reviewed_commit' => self::REVIEWED_FEATURE_COMMIT,
            'total_reviewed_files' => count(self::REVIEWED_MIGRATION_MANIFEST),
            'errors' => $errors,
        ];
    }

    /**
     * Verify that foundation schema fixture, worker source, and preflight source
     * match immutable reviewed hashes pinned at reviewed feature commit 7427b4a4.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public static function verifyReviewedSourcesAgainstPinned(): array
    {
        $repoRoot = dirname(__DIR__);
        $errors = [];

        $pinnedSources = [
            'LegacyBaseSchema' => [
                'path' => $repoRoot . '/tests/_support/Database/LegacyBaseSchema.php',
                'expected' => self::REVIEWED_FOUNDATION_FIXTURE_HASH,
            ],
            'DeveloperSandboxWorker' => [
                'path' => $repoRoot . '/scripts/DeveloperSandboxWorker.php',
                'expected' => self::REVIEWED_WORKER_SOURCE_HASH,
            ],
            'DeveloperSandboxPreflight' => [
                'path' => $repoRoot . '/scripts/DeveloperSandboxPreflight.php',
                'expected' => self::REVIEWED_PREFLIGHT_SOURCE_HASH,
            ],
        ];

        foreach ($pinnedSources as $name => $spec) {
            $path = $spec['path'];
            $expected = $spec['expected'];

            if (!file_exists($path)) {
                $errors[] = "Pinned source {$name} missing at '{$path}'.";
                continue;
            }

            $actual = hash_file('sha256', $path);
            if (!hash_equals($expected, (string) $actual)) {
                $errors[] = "Pinned source {$name} hash mismatch. Expected {$expected}, actual {$actual}.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Pure, fail-closed outbound messaging lockdown validator.
     * Shared by DeveloperSandboxExecutor and DeveloperSandboxTrialController.
     *
     * Invariants:
     * 1. Email transport environment:
     *    - email.protocol must be strictly 'smtp'
     *    - email.SMTPHost must be strictly ''
     *    - email.SMTPPort must be strictly '0' or 0
     * 2. Plan fixture row (id=1):
     *    - Must be a non-null array with a 'features_json' key
     *    - features_json must be a valid JSON string decoding to an array with 'notifications' array
     *    - notifications MUST explicitly contain EACH channel: 'sms', 'whatsapp', 'email'
     *    - Each channel MUST be an array containing the key 'enabled'
     *    - 'enabled' in each channel MUST be strictly boolean false (=== false)
     *    - Any missing channel, null, 0, string 'false', '0', missing enabled, or truthy value FAILS closed.
     *
     * @param array<string, mixed>|null $planRow Query row from plans table, or null if query failed / missing
     * @param array<string, mixed> $envSettings Environment array containing email transport keys
     * @return array{valid: bool, errors: string[]}
     */
    public static function verifyOutboundLockdown(?array $planRow, array $envSettings): array
    {
        $errors = [];

        // 1. Email transport lockdown
        $proto = $envSettings['email.protocol'] ?? null;
        $host = $envSettings['email.SMTPHost'] ?? null;
        $port = $envSettings['email.SMTPPort'] ?? null;

        if ($proto !== 'smtp') {
            $errors[] = "Outbound email protocol must be strictly 'smtp' (found " . var_export($proto, true) . ").";
        }
        if ($host !== '') {
            $errors[] = "Outbound email SMTPHost must be strictly blank (found " . var_export($host, true) . ").";
        }
        if ($port !== '0' && $port !== 0) {
            $errors[] = "Outbound email SMTPPort must be strictly '0' or 0 (found " . var_export($port, true) . ").";
        }

        // 2. Plan fixture lockdown (fail closed)
        if ($planRow === null || !is_array($planRow) || !array_key_exists('features_json', $planRow)) {
            $errors[] = "Plan fixture row id=1 is missing, query failed, or lacks features_json.";
        } else {
            $rawFeatures = $planRow['features_json'];
            if (!is_string($rawFeatures)) {
                $errors[] = "Plan features_json is not a valid string.";
            } else {
                $features = json_decode($rawFeatures, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($features)) {
                    $errors[] = "Plan features_json is malformed JSON.";
                } elseif (!isset($features['notifications']) || !is_array($features['notifications'])) {
                    $errors[] = "Plan features_json is missing required notifications object.";
                } else {
                    $notifs = $features['notifications'];
                    $channels = ['sms', 'whatsapp', 'email'];
                    foreach ($channels as $channel) {
                        if (!array_key_exists($channel, $notifs) || !is_array($notifs[$channel])) {
                            $errors[] = "Plan notification channel '{$channel}' is missing or not an array.";
                        } elseif (!array_key_exists('enabled', $notifs[$channel])) {
                            $errors[] = "Plan notification channel '{$channel}' is missing required 'enabled' property.";
                        } elseif ($notifs[$channel]['enabled'] !== false) {
                            $val = $notifs[$channel]['enabled'];
                            $errors[] = "Plan notification channel '{$channel}.enabled' must be strictly boolean false (found " . gettype($val) . " " . var_export($val, true) . ").";
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Performs real preflight checks, config audit, live identity, grants, emptiness,
     * exclusive process lock, credentials generation, and binds context to the exact DB connection.
     *
     * @param object $db Live database connection object
     * @param array $sandboxConfig Sandbox connection configuration
     * @param array $parentSnapshot Authoritative parent snapshot with protected_databases
     * @param string $mode Execution mode
     * @param bool $isTestMockRun True for offline test mocks only
     * @param bool $isLiveApply True if caller requests live execution (unconditionally refused)
     * @param string|null $storageDir Custom storage directory for tests
     * @return DeveloperSandboxRunContext
     */
    public static function initializeVerifiedContext(
        object $db,
        array $sandboxConfig,
        array $parentSnapshot,
        string $mode = self::MODE_PRODUCTION_BASELINE,
        bool $isTestMockRun = false,
        bool $isLiveApply = false,
        ?string $storageDir = null
    ): DeveloperSandboxRunContext {
        if (!in_array($mode, self::SUPPORTED_MODES, true)) {
            throw new \InvalidArgumentException("CRITICAL REFUSAL: Unsupported mode '{$mode}'.");
        }
        $targetDb = DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB;

        // 1. Authoritative parent snapshot validation
        if (empty($parentSnapshot['protected_databases']) || !is_array($parentSnapshot['protected_databases']) || empty($parentSnapshot['protected_databases']['database']) || trim((string) $parentSnapshot['protected_databases']['database']) === '') {
            throw new \RuntimeException("CRITICAL REFUSAL: Authoritative parent snapshot is missing or malformed; protected databases not provided.");
        }

        // 2. Preflight configuration validation BEFORE connection
        $configErrors = DeveloperSandboxPreflight::validateConfigBeforeConnection($sandboxConfig, $parentSnapshot['protected_databases'], $targetDb);
        if (!empty($configErrors)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Preflight config validation failed: " . implode("; ", $configErrors));
        }

        // 2b. Exact immutable migration manifest verification BEFORE mutation (pre-boot)
        if (!$isTestMockRun) {
            $manifestCheck = self::verifyMigrationManifestAgainstReviewed();
            if (!$manifestCheck['valid']) {
                throw new \RuntimeException("CRITICAL REFUSAL: Pre-mutation migration manifest verification failed: " . implode("; ", $manifestCheck['errors']));
            }
        }

        // 3. Acquire exclusive process lock
        $processLock = $isTestMockRun ? null : DeveloperSandboxProcessLock::acquire($storageDir);

        // 4. Initialize durable journal
        $runId = 'denta_run_' . bin2hex(random_bytes(8));
        $journal = DeveloperSandboxJournal::create($storageDir, $runId);
        $journal->record('context_init', 'created', ['run_id' => $runId, 'mode' => $mode]);

        // 5. Live connection identity check
        $idErrors = DeveloperSandboxPreflight::validateLiveConnection($db, $targetDb);
        if (!empty($idErrors)) {
            $journal->record('preflight', 'live_identity_failed', ['errors' => $idErrors]);
            throw new \RuntimeException("CRITICAL REFUSAL: Live connection identity check failed.");
        }

        // 6. Live user grants check
        $grantErrors = DeveloperSandboxPreflight::validateUserGrants($db, $targetDb);
        if (!empty($grantErrors)) {
            $journal->record('preflight', 'grants_failed', ['errors' => $grantErrors]);
            throw new \RuntimeException("CRITICAL REFUSAL: User grants validation failed.");
        }

        // 7. Target emptiness check
        $eligibility = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($db, $targetDb);
        if (!$eligibility['allowed']) {
            $journal->record('preflight', 'emptiness_failed', ['reason' => $eligibility['reason']]);
            throw new \RuntimeException("CRITICAL REFUSAL: Target emptiness verification failed: " . $eligibility['reason']);
        }

        // 8. Generate private credentials
        $credentials = DeveloperSandboxCredentials::generateAndPersist($storageDir, $runId);

        // 9. Record verification in journal
        $journal->record('preflight', 'verified', ['run_id' => $runId]);

        return self::mintContext(
            $runId,
            $mode,
            $targetDb,
            $db,
            $journal,
            $credentials,
            $processLock,
            true, // isVerified
            $isTestMockRun,
            $isLiveApply
        );
    }

    /**
     * Internal closure minting helper accessing private DeveloperSandboxRunContext::mintContext.
     */
    private static function mintContext(
        string $runId,
        string $mode,
        string $targetDb,
        object $connection,
        DeveloperSandboxJournal $journal,
        DeveloperSandboxCredentials $credentials,
        ?DeveloperSandboxProcessLock $processLock,
        bool $isVerified,
        bool $isTestMockRun,
        bool $isLiveApply
    ): DeveloperSandboxRunContext {
        $mint = \Closure::bind(
            static function () use (
                $runId, $mode, $targetDb, $connection, $journal, $credentials, $processLock, $isVerified, $isTestMockRun, $isLiveApply
            ) {
                return DeveloperSandboxRunContext::mintContext(
                    $runId, $mode, $targetDb, $connection, $journal, $credentials, $processLock, $isVerified, $isTestMockRun, $isLiveApply
                );
            },
            null,
            DeveloperSandboxRunContext::class
        );
        return $mint();
    }

    /**
     * Create a verified context strictly for offline negative and unit test simulations.
     * Enforces that ONLY explicit DeveloperSandboxTestConnectionStub instances can be passed,
     * preventing arbitrary real database connection objects from being converted to fake-authorized connections.
     */
    public static function createTestMockContext(
        DeveloperSandboxTestConnectionStub $db,
        string $mode = self::MODE_PRODUCTION_BASELINE,
        ?string $storageDir = null
    ): DeveloperSandboxRunContext {
        if ($db instanceof \CodeIgniter\Database\ConnectionInterface
            || $db instanceof \mysqli
            || $db instanceof \PDO
        ) {
            throw new \InvalidArgumentException("CRITICAL REFUSAL: Real database connection objects cannot be used in test mock context.");
        }

        $runId = 'denta_test_' . bin2hex(random_bytes(8));
        $journal = DeveloperSandboxJournal::create($storageDir, $runId);
        $credentials = DeveloperSandboxCredentials::generateAndPersist($storageDir, $runId);

        return self::mintContext(
            $runId,
            $mode,
            DeveloperSandboxPreflight::APPROVED_DEFAULT_SANDBOX_DB,
            $db,
            $journal,
            $credentials,
            null,
            true, // isVerified
            true, // isTestMockRun = true
            false // isLiveApply = false
        );
    }

    /**
     * Inspectable Orchestration Entry Point
     * Unconditionally refuses live apply without explicit authorization.
     *
     * @param DeveloperSandboxRunContext $context Validated run context
     * @param object $db Live database connection or stub
     * @param DeveloperSandboxMigrationAdapterInterface|null $migrationAdapter Optional migration adapter
     * @return array Structured execution report
     */
    public function runBootstrap(
        DeveloperSandboxRunContext $context,
        object $db,
        ?DeveloperSandboxMigrationAdapterInterface $migrationAdapter = null
    ): array {
        if ($context->isLiveApply() || !$context->isTestMockRun()) {
            if (getenv('DENTA_AUTHORIZED_DEV_SANDBOX_APPLY') !== '1') {
                throw new \RuntimeException("CRITICAL REFUSAL: Live database bootstrap execution is unconditionally refused. Codex approval is required.");
            }
        }

        $results = [
            'run_id' => $context->getRunId(),
            'mode' => $context->getMode(),
            'stages' => [],
            'success' => false,
            'tables_created_count' => 0,
        ];

        // Stage 1: Emptiness & Marker
        $res1 = $this->executeStage1_VerifyEmptinessAndCreateMarker($context, $db);
        $results['stages']['stage_1'] = $res1;
        if (!$res1['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_1', ['reason' => $res1['message']]);
            return $results;
        }

        // Stage 2: Legacy Foundation
        $res2 = $this->executeStage2_ApplyLegacyFoundation($context, $db);
        $results['stages']['stage_2'] = $res2;
        if (!$res2['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_2', ['reason' => $res2['message']]);
            return $results;
        }

        // Stage 3: Canonical Migrations
        $res3 = $this->executeStage3_ExecuteMigrations($context, $db, $migrationAdapter);
        $results['stages']['stage_3'] = $res3;
        if (!$res3['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_3', ['reason' => $res3['message']]);
            return $results;
        }

        // Stage 4: User 1 & RBAC
        $res4 = $this->executeStage4_InitializeUser1AndSyncRbac($context, $db);
        $results['stages']['stage_4'] = $res4;
        if (!$res4['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_4', ['reason' => $res4['message']]);
            return $results;
        }

        // Stage 5: Synthetic Tenants & Plans (Clinics created BEFORE clinic settings)
        $res5 = $this->executeStage5_SeedSyntheticTenantsAndPlans($context, $db);
        $results['stages']['stage_5'] = $res5;
        if (!$res5['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_5', ['reason' => $res5['message']]);
            return $results;
        }

        // Stage 6: Canonical Settings (Now Clinic 1 exists)
        $res6 = $this->executeStage6_SeedCanonicalSettings($context, $db);
        $results['stages']['stage_6'] = $res6;
        if (!$res6['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_6', ['reason' => $res6['message']]);
            return $results;
        }

        // Stage 7: Developer Personas & Memberships
        $res7 = $this->executeStage7_SeedDeveloperPersonasAndMemberships($context, $db);
        $results['stages']['stage_7'] = $res7;
        if (!$res7['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_7', ['reason' => $res7['message']]);
            return $results;
        }

        // Stage 8: Verify Transport Lockdown
        $res8 = $this->executeStage8_VerifyMessagingTransportLockdown($context, $db);
        $results['stages']['stage_8'] = $res8;
        if (!$res8['success']) {
            $context->recordJournal('orchestration', 'halted_at_stage_8', ['reason' => $res8['message']]);
            return $results;
        }

        // Physical table count verification: query real database rather than assuming constant
        $physicalCount = $this->queryPhysicalTableCount($db, $context);
        $expectedCount = ($context->getMode() === self::MODE_PRODUCTION_BASELINE)
            ? self::EXPECTED_TOTAL_TABLES_BASELINE
            : self::EXPECTED_TOTAL_TABLES_ALL_SOURCE;

        if ($physicalCount !== $expectedCount) {
            $context->recordJournal('orchestration', 'physical_table_count_mismatch', [
                'expected' => $expectedCount,
                'actual' => $physicalCount,
            ]);
            $results['success'] = false;
            $results['tables_created_count'] = $physicalCount;
            $results['message'] = "CRITICAL PHYSICAL VERIFICATION FAILURE: Expected {$expectedCount} physical tables in database, but queried {$physicalCount}.";
            return $results;
        }

        $results['success'] = true;
        $results['tables_created_count'] = $physicalCount;
        $results['journal'] = $context->getJournal()->getEntries();

        return $results;
    }

    /**
     * Query physical table count from target database via real query (fail closed).
     *
     * @param object $db Database connection or test stub
     * @param DeveloperSandboxRunContext $context Validated run context
     * @return int Count of physical base tables, or -1 on query failure
     */
    public function queryPhysicalTableCount(object $db, DeveloperSandboxRunContext $context): int
    {
        try {
            $databaseName = $context->getTargetDb();
            if ($databaseName === '') {
                return -1;
            }

            $query = $db->query(
                "SELECT COUNT(*) AS table_count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
                [$databaseName]
            );
            if ($query && method_exists($query, 'getRowArray')) {
                $row = $query->getRowArray();
                if (is_array($row) && isset($row['table_count']) && is_numeric($row['table_count'])) {
                    return (int) $row['table_count'];
                }
            }

            return -1;
        } catch (\Throwable $e) {
            $context->recordJournal('orchestration', 'query_physical_table_count_failed', [
                'error' => get_class($e),
            ]);
            return -1;
        }
    }

    /**
     * Enforce lifecycle/phase order, connection binding, and verification boundary.
     */
    private function assertValidStageCall($context, object $db, int $expectedPhase): void
    {
        if (!$context instanceof DeveloperSandboxRunContext) {
            throw new \RuntimeException("CRITICAL REFUSAL: Direct stage execution without verified DeveloperSandboxRunContext is refused.");
        }

        if (!$context->isVerified()) {
            throw new \RuntimeException("CRITICAL REFUSAL: Stage execution outside verified preflight boundary is strictly forbidden.");
        }

        if (!$context->isBoundToConnection($db)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Run context is not bound to the supplied database connection object.");
        }

        if ($context->getCurrentPhase() !== $expectedPhase) {
            throw new \RuntimeException("CRITICAL REFUSAL: Invalid stage transition. Current phase is {$context->getCurrentPhase()}, expected {$expectedPhase}. Repeat or out-of-order stage execution is strictly forbidden.");
        }
    }

    /**
     * Stage 1: Verify target emptiness and insert ownership marker.
     */
    public function executeStage1_VerifyEmptinessAndCreateMarker($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_INITIALIZED);

        $eligibility = DeveloperSandboxPreflight::verifyTargetEligibilityForBootstrap($db, $context->getTargetDb());
        if (!$eligibility['allowed']) {
            $context->recordJournal('stage_1', 'emptiness_check_failed', ['reason' => $eligibility['reason']]);
            return [
                'success' => false,
                'stage' => 'stage_1_target_emptiness',
                'message' => $eligibility['reason'],
            ];
        }

        $markerDdl = "CREATE TABLE IF NOT EXISTS `" . DeveloperSandboxPreflight::OWNERSHIP_MARKER_TABLE . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `target_db` varchar(100) NOT NULL,
            `task_owner` varchar(100) NOT NULL,
            `is_disposable` tinyint(1) NOT NULL DEFAULT 1,
            `mode` varchar(50) NOT NULL,
            `run_id` varchar(64) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $context->recordJournal('stage_1', 'creating_marker_table');
            $ddlResult = $db->query($markerDdl);
            if ($ddlResult === false || $ddlResult === null) {
                $context->recordJournal('stage_1', 'marker_ddl_failed');
                return [
                    'success' => false,
                    'stage' => 'stage_1_target_emptiness_and_marker',
                    'message' => "DDL statement for marker table returned false/null.",
                ];
            }

            $insertSql = "INSERT INTO `" . DeveloperSandboxPreflight::OWNERSHIP_MARKER_TABLE . "` (`target_db`, `task_owner`, `is_disposable`, `mode`, `run_id`) VALUES (?, ?, 1, ?, ?)";
            $insertResult = $db->query($insertSql, [$context->getTargetDb(), DeveloperSandboxPreflight::TASK_OWNER_SIGNATURE, $context->getMode(), $context->getRunId()]);
            if ($insertResult === false || $insertResult === null) {
                $context->recordJournal('stage_1', 'marker_insert_failed');
                return [
                    'success' => false,
                    'stage' => 'stage_1_target_emptiness_and_marker',
                    'message' => "Insert statement for marker record returned false/null.",
                ];
            }

            $context->recordJournal('stage_1', 'marker_created', ['target_db' => $context->getTargetDb(), 'run_id' => $context->getRunId()]);
            $context->internalAdvancePhase(__FUNCTION__, $db);

            return [
                'success' => true,
                'stage' => 'stage_1_target_emptiness_and_marker',
                'message' => "Ownership marker created successfully in '{$context->getTargetDb()}'.",
            ];
        } catch (\Throwable $e) {
            $context->recordJournal('stage_1', 'exception', ['error' => get_class($e)]);
            return [
                'success' => false,
                'stage' => 'stage_1_target_emptiness_and_marker',
                'message' => "Failed to create ownership marker: " . get_class($e),
            ];
        }
    }

    /**
     * Stage 2: Apply 18 legacy foundation tables from LegacyBaseSchema.
     */
    public function executeStage2_ApplyLegacyFoundation($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE1_DONE);

        if (!$this->isMarkerTablePresent($db)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: ownership marker table does not exist in target database.");
        }

        $legacySchemaFile = dirname(__DIR__) . '/tests/_support/Database/LegacyBaseSchema.php';
        if (!file_exists($legacySchemaFile) || !hash_equals(self::REVIEWED_FOUNDATION_FIXTURE_HASH, (string) hash_file('sha256', $legacySchemaFile))) {
            throw new \RuntimeException("CRITICAL REFUSAL: LegacyBaseSchema source file does not match immutable reviewed hash (reviewed commit " . self::REVIEWED_FEATURE_COMMIT . ").");
        }

        $statements = LegacyBaseSchema::DDL_STATEMENTS;
        $appliedCount = 0;

        foreach ($statements as $index => $ddl) {
            try {
                $context->recordJournal('stage_2', 'executing_ddl', ['index' => $index]);
                $result = $db->query($ddl);
                if ($result === false || $result === null) {
                    $context->recordJournal('stage_2', 'ddl_failed_false_return', ['index' => $index]);
                    return [
                        'success' => false,
                        'stage' => 'stage_2_legacy_foundation',
                        'tables_applied' => $appliedCount,
                        'message' => "Foundation table DDL returned false/null at statement index {$index}.",
                    ];
                }
                $appliedCount++;
            } catch (\Throwable $e) {
                $context->recordJournal('stage_2', 'ddl_exception', ['index' => $index, 'error' => get_class($e)]);
                return [
                    'success' => false,
                    'stage' => 'stage_2_legacy_foundation',
                    'tables_applied' => $appliedCount,
                    'message' => "Failed applying foundation table statement index {$index}: " . get_class($e),
                ];
            }
        }

        $context->recordJournal('stage_2', 'completed', ['tables_applied' => $appliedCount]);
        $context->internalAdvancePhase(__FUNCTION__, $db);

        return [
            'success' => true,
            'stage' => 'stage_2_legacy_foundation',
            'tables_applied' => $appliedCount,
            'message' => "Successfully applied all {$appliedCount} legacy foundation tables.",
        ];
    }

    /**
     * Stage 3: Canonical migrations execution using authoritative MigrationRunner.
     * Verifies exact history delta (+1 per migration) and rejects lock contention, duplicate/replay, or no-progress.
     */
    public function executeStage3_ExecuteMigrations(
        $context,
        object $db,
        ?DeveloperSandboxMigrationAdapterInterface $migrationAdapter = null
    ): array {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE2_DONE);

        if (!$this->isMarkerTablePresent($db) || !$this->isFoundationPresent($db)) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: marker or legacy foundation tables do not exist in target database.");
        }

        // Pre-mutation migration manifest verification against immutable reviewed pin
        $manifestCheck = self::verifyMigrationManifestAgainstReviewed();
        if (!$manifestCheck['valid']) {
            throw new \RuntimeException("CRITICAL REFUSAL: Pre-mutation migration manifest verification failed: " . implode("; ", $manifestCheck['errors']));
        }

        if ($migrationAdapter === null) {
            if ($db instanceof \CodeIgniter\Database\ConnectionInterface) {
                $migrationAdapter = new CodeIgniterMigrationRunnerAdapter(null, $db);
            } else {
                $context->recordJournal('stage_3', 'runner_unavailable');
                return [
                    'success' => false,
                    'stage' => 'stage_3_migrations',
                    'migrations_applied' => 0,
                    'message' => "CRITICAL REFUSAL: Authoritative MigrationRunner backend is unavailable. Halting before migration execution.",
                ];
            }
        }

        $mode = $context->getMode();
        $plan = $this->resolveMigrationPlan($mode);
        $appliedCount = 0;

        foreach ($plan as $fileName => $meta) {
            $context->recordJournal('stage_3', 'executing_migration', ['file' => $fileName, 'class' => $meta['class']]);

            // 1. Source file existence and hash verification
            if (!file_exists($meta['path'])) {
                return [
                    'success' => false,
                    'stage' => 'stage_3_migrations',
                    'migrations_applied' => $appliedCount,
                    'failed_migration' => $fileName,
                    'message' => "Migration source file not found: {$meta['path']}",
                ];
            }

            $currentHash = hash_file('sha256', $meta['path']);
            if (!hash_equals($meta['pinned_hash'], $currentHash)) {
                $context->recordJournal('stage_3', 'source_hash_mismatch', ['file' => $fileName]);
                return [
                    'success' => false,
                    'stage' => 'stage_3_migrations',
                    'migrations_applied' => $appliedCount,
                    'failed_migration' => $fileName,
                    'message' => "CRITICAL REFUSAL: Source hash mismatch for migration '{$fileName}'. Expected: {$meta['pinned_hash']}, Actual: {$currentHash}",
                ];
            }

            // 2. Duplicate/replay detection: verify migration version is NOT already in history
            if ($this->isMigrationRecordedInHistory($db, $meta['version'])) {
                $context->recordJournal('stage_3', 'duplicate_migration_detected', ['version' => $meta['version']]);
                return [
                    'success' => false,
                    'stage' => 'stage_3_migrations',
                    'migrations_applied' => $appliedCount,
                    'failed_migration' => $fileName,
                    'message' => "CRITICAL REFUSAL: Migration '{$fileName}' (version {$meta['version']}) is already recorded in history (duplicate/replay detected).",
                ];
            }

            // 3. Query history count before execution to verify exact history delta
            $historyCountBefore = $this->getMigrationHistoryRowCount($db, $appliedCount === 0);

            try {
                $res = $migrationAdapter->runMigrationFile($meta['path'], 'App', 'default');
                if (!$res) {
                    $context->recordJournal('stage_3', 'migration_failed', ['file' => $fileName]);
                    return [
                        'success' => false,
                        'stage' => 'stage_3_migrations',
                        'migrations_applied' => $appliedCount,
                        'failed_migration' => $fileName,
                        'message' => "Migration '{$fileName}' failed through MigrationRunner.",
                    ];
                }

                // 4. Exact history delta verification: MigrationRunner returning true MUST have added exactly 1 history row.
                $historyCountAfter = $this->getMigrationHistoryRowCount($db, false);
                if ($historyCountAfter <= $historyCountBefore) {
                    $context->recordJournal('stage_3', 'no_progress_true_detected', ['file' => $fileName]);
                    return [
                        'success' => false,
                        'stage' => 'stage_3_migrations',
                        'migrations_applied' => $appliedCount,
                        'failed_migration' => $fileName,
                        'message' => "CRITICAL REFUSAL: Migration '{$fileName}' returned true without adding a history row (lock contention or regression; no progress made).",
                    ];
                }

                if ($historyCountAfter !== $historyCountBefore + 1) {
                    $context->recordJournal('stage_3', 'irregular_history_delta', [
                        'file' => $fileName,
                        'before' => $historyCountBefore,
                        'after' => $historyCountAfter,
                    ]);
                    return [
                        'success' => false,
                        'stage' => 'stage_3_migrations',
                        'migrations_applied' => $appliedCount,
                        'failed_migration' => $fileName,
                        'message' => "CRITICAL REFUSAL: Migration '{$fileName}' produced an irregular history row delta (expected exact +1, got " . ($historyCountAfter - $historyCountBefore) . ").",
                    ];
                }

                // 5. Verify latest recorded row identity
                $latestRow = $this->getLatestMigrationHistoryRow($db);
                if ($latestRow === null) {
                    $context->recordJournal('stage_3', 'missing_latest_history_row', ['file' => $fileName]);
                    return [
                        'success' => false,
                        'stage' => 'stage_3_migrations',
                        'migrations_applied' => $appliedCount,
                        'failed_migration' => $fileName,
                        'message' => "CRITICAL REFUSAL: Migration '{$fileName}' recorded history delta but failed retrieving latest row.",
                    ];
                }

                $actualVersion = (string) ($latestRow['version'] ?? '');
                $actualClass = (string) ($latestRow['class'] ?? '');
                $actualNamespace = (string) ($latestRow['namespace'] ?? '');
                $actualGroup = (string) ($latestRow['group'] ?? '');

                if ($actualVersion !== $meta['version'] || $actualClass !== $meta['class'] || $actualNamespace !== 'App' || $actualGroup !== 'default') {
                    $context->recordJournal('stage_3', 'history_identity_mismatch', [
                        'file' => $fileName,
                        'expected' => ['version' => $meta['version'], 'class' => $meta['class'], 'namespace' => 'App', 'group' => 'default'],
                        'actual' => ['version' => $actualVersion, 'class' => $actualClass, 'namespace' => $actualNamespace, 'group' => $actualGroup],
                    ]);
                    return [
                        'success' => false,
                        'stage' => 'stage_3_migrations',
                        'migrations_applied' => $appliedCount,
                        'failed_migration' => $fileName,
                        'message' => "CRITICAL REFUSAL: Latest migration history row identity does not match executed migration '{$fileName}'. Expected [version: {$meta['version']}, class: {$meta['class']}, namespace: App, group: default], got [version: {$actualVersion}, class: {$actualClass}, namespace: {$actualNamespace}, group: {$actualGroup}].",
                    ];
                }

                $appliedCount++;
            } catch (\Throwable $e) {
                $context->recordJournal('stage_3', 'migration_exception', ['file' => $fileName, 'error' => get_class($e)]);
                return [
                    'success' => false,
                    'stage' => 'stage_3_migrations',
                    'migrations_applied' => $appliedCount,
                    'failed_migration' => $fileName,
                    'message' => "Exception in migration '{$fileName}': " . get_class($e),
                ];
            }
        }

        $context->recordJournal('stage_3', 'completed', ['migrations_applied' => $appliedCount]);
        $context->internalAdvancePhase(__FUNCTION__, $db);

        return [
            'success' => true,
            'stage' => 'stage_3_migrations',
            'migrations_applied' => $appliedCount,
            'message' => "Successfully applied and recorded {$appliedCount} migrations through MigrationRunner.",
        ];
    }

    /**
     * Check if ownership marker table exists in target database.
     */
    public function isMarkerTablePresent(object $db): bool
    {
        try {
            $marker = DeveloperSandboxPreflight::OWNERSHIP_MARKER_TABLE;
            $q = $db->query("SELECT 1 FROM `{$marker}` LIMIT 1");
            return $q !== false && $q !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if legacy base foundation tables exist in target database.
     */
    public function isFoundationPresent(object $db): bool
    {
        try {
            $q = $db->query("SELECT 1 FROM `users` LIMIT 1");
            return $q !== false && $q !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if a migration version is already recorded in migrations table.
     */
    public function isMigrationRecordedInHistory(object $db, string $version): bool
    {
        try {
            $q = $db->query("SELECT `id` FROM `migrations` WHERE `version` = ? AND `group` = 'default' LIMIT 1", [$version]);
            if (!is_object($q) || !method_exists($q, 'getRowArray')) {
                return false;
            }
            $row = $q->getRowArray();
            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get current count of recorded migrations in migrations table.
     */
    public function getMigrationHistoryRowCount(object $db, bool $allowMissingTable = false): int
    {
        $q = null;
        try {
            $q = $db->query("SELECT COUNT(*) as c FROM `migrations` WHERE `group` = 'default'");
        } catch (\Throwable $e) {
            if ($allowMissingTable && $this->isMissingHistoryTableError($db)) {
                return 0;
            }
            throw new \RuntimeException("CRITICAL REFUSAL: Failed querying migration history count.");
        }

        // With DBDebug=false, MySQLi returns false rather than throwing for an absent
        // table. Only the driver's exact missing-table code is valid on first run;
        // permission, connection and malformed-result failures must still stop execution.
        if ($q === false && $allowMissingTable && $this->isMissingHistoryTableError($db)) {
            return 0;
        }

        if (!$q || !is_object($q) || !method_exists($q, 'getRowArray')) {
            throw new \RuntimeException("CRITICAL REFUSAL: Query for migration history row count returned false or invalid result.");
        }

        $row = $q->getRowArray();
        if (!is_array($row) || !isset($row['c']) || !is_numeric($row['c'])) {
            throw new \RuntimeException("CRITICAL REFUSAL: Malformed row count returned from migrations history query.");
        }

        return (int) $row['c'];
    }

    private function isMissingHistoryTableError(object $db): bool
    {
        if (!method_exists($db, 'error')) {
            return false;
        }
        try {
            $error = $db->error();
            return is_array($error) && in_array($error['code'] ?? null, [1146, '1146'], true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Retrieve the latest recorded migration history row.
     *
     * @return array|null
     */
    public function getLatestMigrationHistoryRow(object $db): ?array
    {
        $q = $db->query("SELECT `id`, `version`, `class`, `group`, `namespace`, `time`, `batch` FROM `migrations` WHERE `group` = 'default' ORDER BY `id` DESC LIMIT 1");
        if (!$q || !is_object($q) || !method_exists($q, 'getRowArray')) {
            throw new \RuntimeException("CRITICAL REFUSAL: Failed querying latest migration history row from migrations table.");
        }
        $row = $q->getRowArray();
        return is_array($row) ? $row : null;
    }

    /**
     * Stage 4: Initialize User 1 (superadmin) and synchronize RBAC.
     * Uses canonical schema (name, module, action, description, category, is_system; NO display_name).
     * user_roles schema strictly matches CreateRBACTables: (user_id, role_id, assigned_by, is_active).
     * Populates role_permissions and resolves role IDs strictly by canonical slug.
     */
    public function executeStage4_InitializeUser1AndSyncRbac($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE3_DONE);

        if ($this->getMigrationHistoryRowCount($db, false) === 0) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: migration history is empty before Stage 4 execution.");
        }

        try {
            // Step 1: Insert User 1 first with private hash from credentials
            $now = time();
            $adminHash = $context->getCredentials()->getPasswordHash('superadmin');

            $checkUser1 = $db->query("SELECT id FROM `users` WHERE `id` = 1 LIMIT 1");
            $user1Exists = is_object($checkUser1) && method_exists($checkUser1, 'getRowArray') && !empty($checkUser1->getRowArray());

            if (!$user1Exists) {
                $user1Sql = "INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `active`, `created_at`, `first_name`, `last_name`)
                             VALUES (1, '127.0.0.1', 'superadmin', ?, 'admin@sandbox.test', 1, ?, 'Sandbox', 'Administrator')";
                $res1 = $db->query($user1Sql, [$adminHash, $now]);
                if ($res1 === false || $res1 === null) {
                    $context->recordJournal('stage_4', 'user1_insert_failed');
                    return [
                        'success' => false,
                        'stage' => 'stage_4_user_initialization_and_rbac',
                        'message' => "Failed inserting User 1 (query returned false/null).",
                    ];
                }
            }

            // Step 2: Synchronize permissions matching EXACT schema (name, module, action, description, category, is_system)
            require_once dirname(__DIR__) . '/app/Config/Permissions.php';
            $permissionsDef = \App\Config\Permissions::getDefaultPermissions();
            $permCount = 0;
            foreach ($permissionsDef as $module => $actions) {
                foreach ($actions as $action => $desc) {
                    $permName = $module . '.' . $action;

                    // Idempotent check
                    $checkP = $db->query("SELECT id FROM `permissions` WHERE `module` = ? AND `action` = ? LIMIT 1", [$module, $action]);
                    $pExists = is_object($checkP) && method_exists($checkP, 'getRowArray') && !empty($checkP->getRowArray());

                    if (!$pExists) {
                        $permSql = "INSERT INTO `permissions` (`name`, `module`, `action`, `description`, `category`, `is_system`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, 'general', 1, NOW(), NOW())";
                        $resPerm = $db->query($permSql, [$permName, $module, $action, $desc]);
                        if ($resPerm === false || $resPerm === null) {
                            $context->recordJournal('stage_4', 'permission_insert_failed', ['perm' => $permName]);
                            return [
                                'success' => false,
                                'stage' => 'stage_4_user_initialization_and_rbac',
                                'message' => "Failed inserting permission {$permName}.",
                            ];
                        }
                    }
                    $permCount++;
                }
            }

            // Step 3: Synchronize roles matching canonical schema
            $rolesDef = \App\Config\Permissions::getDefaultRoles();
            $medicalRoles = ['senior_doctor', 'doctor', 'dental_assistant'];
            $roleCount = 0;

            foreach ($rolesDef as $slug => $roleData) {
                $isMedical = in_array($slug, $medicalRoles, true) ? 1 : 0;

                // Idempotent check
                $checkR = $db->query("SELECT id FROM `roles` WHERE `slug` = ? AND `clinic_id` IS NULL LIMIT 1", [$slug]);
                $rExists = is_object($checkR) && method_exists($checkR, 'getRowArray') && !empty($checkR->getRowArray());

                if (!$rExists) {
                    $roleSql = "INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `is_medical`, `is_active`, `clinic_id`, `created_by`, `created_at`, `updated_at`) VALUES (?, ?, ?, 1, ?, 1, NULL, 1, NOW(), NOW())";
                    $resRole = $db->query($roleSql, [$roleData['name'], $slug, $roleData['description'], $isMedical]);
                    if ($resRole === false || $resRole === null) {
                        $context->recordJournal('stage_4', 'role_insert_failed', ['slug' => $slug]);
                        return [
                            'success' => false,
                            'stage' => 'stage_4_user_initialization_and_rbac',
                            'message' => "Failed inserting role {$slug}.",
                        ];
                    }
                }
                $roleCount++;
            }

            // Step 4: Populate role_permissions table based on canonical definitions
            foreach ($rolesDef as $slug => $roleData) {
                $roleId = $this->resolveRoleIdBySlug($db, $slug);
                if ($roleId === null) {
                    $context->recordJournal('stage_4', 'role_id_resolution_failed', ['slug' => $slug]);
                    return [
                        'success' => false,
                        'stage' => 'stage_4_user_initialization_and_rbac',
                        'message' => "Failed resolving role ID for slug {$slug}.",
                    ];
                }

                $permsToAssign = $roleData['permissions'];
                if ($permsToAssign === '*') {
                    $assignAllSql = "INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`, `created_at`)
                                     SELECT ?, p.`id`, 1, NOW()
                                     FROM `permissions` p
                                     WHERE NOT EXISTS (
                                         SELECT 1 FROM `role_permissions` rp
                                         WHERE rp.`role_id` = ? AND rp.`permission_id` = p.`id`
                                     )";
                    $resAssign = $db->query($assignAllSql, [$roleId, $roleId]);
                    if ($resAssign === false || $resAssign === null) {
                        return [
                            'success' => false,
                            'stage' => 'stage_4_user_initialization_and_rbac',
                            'message' => "Failed assigning wildcard permissions to role {$slug}.",
                        ];
                    }
                } elseif (is_array($permsToAssign)) {
                    foreach ($permsToAssign as $mod => $acts) {
                        foreach ($acts as $act) {
                            $assignSql = "INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`, `created_at`)
                                          SELECT ?, p.`id`, 1, NOW()
                                          FROM `permissions` p
                                          WHERE p.`module` = ? AND p.`action` = ?
                                          AND NOT EXISTS (
                                              SELECT 1 FROM `role_permissions` rp
                                              WHERE rp.`role_id` = ? AND rp.`permission_id` = p.`id`
                                          )";
                            $resAssign = $db->query($assignSql, [$roleId, $mod, $act, $roleId]);
                            if ($resAssign === false || $resAssign === null) {
                                return [
                                    'success' => false,
                                    'stage' => 'stage_4_user_initialization_and_rbac',
                                    'message' => "Failed assigning permission {$mod}.{$act} to role {$slug}.",
                                ];
                            }
                        }
                    }
                }
            }

            // Step 5: Assign User 1 to super_admin in user_roles by canonical slug resolution
            // Exact CreateRBACTables schema: (user_id, role_id, assigned_by, is_active)
            $superAdminRoleId = $this->resolveRoleIdBySlug($db, 'super_admin');
            if ($superAdminRoleId === null) {
                $context->recordJournal('stage_4', 'superadmin_role_resolution_failed');
                return [
                    'success' => false,
                    'stage' => 'stage_4_user_initialization_and_rbac',
                    'message' => "CRITICAL REFUSAL: Failed resolving super_admin role ID by canonical slug.",
                ];
            }

            $checkUserRole = $db->query("SELECT id FROM `user_roles` WHERE `user_id` = 1 AND `role_id` = ? LIMIT 1", [$superAdminRoleId]);
            $userRoleExists = $checkUserRole && method_exists($checkUserRole, 'getRowArray') && !empty($checkUserRole->getRowArray());

            if (!$userRoleExists) {
                $userRoleSql = "INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_by`, `is_active`) VALUES (1, ?, 1, 1)";
                $resUserRole = $db->query($userRoleSql, [$superAdminRoleId]);
                if ($resUserRole === false || $resUserRole === null) {
                    $context->recordJournal('stage_4', 'user_role_assignment_failed');
                    return [
                        'success' => false,
                        'stage' => 'stage_4_user_initialization_and_rbac',
                        'message' => "Failed assigning User 1 to super_admin in user_roles.",
                    ];
                }
            }

            $context->recordJournal('stage_4', 'completed', ['permissions' => $permCount, 'roles' => $roleCount]);
            $context->internalAdvancePhase(__FUNCTION__, $db);

            return [
                'success' => true,
                'stage' => 'stage_4_user_initialization_and_rbac',
                'message' => "User 1 initialized and RBAC synchronized ({$permCount} permissions, {$roleCount} roles).",
            ];
        } catch (\Throwable $e) {
            $context->recordJournal('stage_4', 'exception', ['error' => get_class($e)]);
            return [
                'success' => false,
                'stage' => 'stage_4_user_initialization_and_rbac',
                'message' => "Failed in Stage 4: " . get_class($e),
            ];
        }
    }

    /**
     * Stage 5: Seed synthetic tenants and baseline plan.
     * Clinics MUST be created BEFORE clinic settings are inserted.
     */
    public function executeStage5_SeedSyntheticTenantsAndPlans($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE4_DONE);

        $checkUser1 = $db->query("SELECT id FROM `users` WHERE `id` = 1 LIMIT 1");
        if (!$checkUser1 || !method_exists($checkUser1, 'getRowArray') || empty($checkUser1->getRowArray())) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: User 1 does not exist before Stage 5 tenant seeding.");
        }

        $data = $this->getSyntheticTenantsAndPersonasDefinition();
        $now = date('Y-m-d H:i:s');

        try {
            // Plan
            $plan = $data['plan'];
            $checkPlan = $db->query("SELECT id FROM `plans` WHERE `id` = ? LIMIT 1", [$plan['id']]);
            $planExists = $checkPlan && method_exists($checkPlan, 'getRowArray') && !empty($checkPlan->getRowArray());

            if (!$planExists) {
                $planSql = "INSERT INTO `plans` (`id`, `name`, `status`, `features_json`, `limits_json`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $planRes = $db->query($planSql, [
                    $plan['id'],
                    $plan['name'],
                    $plan['status'],
                    json_encode($plan['features_json']),
                    json_encode([]),
                    $now,
                    $now,
                ]);
                if ($planRes === false || $planRes === null) {
                    $context->recordJournal('stage_5', 'plan_insert_failed');
                    return ['success' => false, 'stage' => 'stage_5_tenants_and_plans', 'message' => "Failed inserting plan."];
                }
            }

            // Clinics & Subscriptions
            foreach ($data['tenants'] as $clinic) {
                $checkClinic = $db->query("SELECT id FROM `clinics` WHERE `id` = ? LIMIT 1", [$clinic['id']]);
                $clinicExists = $checkClinic && method_exists($checkClinic, 'getRowArray') && !empty($checkClinic->getRowArray());

                if (!$clinicExists) {
                    $clinicSql = "INSERT INTO `clinics` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?)";
                    $clinicRes = $db->query($clinicSql, [$clinic['id'], $clinic['name'], $clinic['slug'], $clinic['status'], $now, $now]);
                    if ($clinicRes === false || $clinicRes === null) {
                        $context->recordJournal('stage_5', 'clinic_insert_failed', ['clinic' => $clinic['name']]);
                        return ['success' => false, 'stage' => 'stage_5_tenants_and_plans', 'message' => "Failed inserting clinic {$clinic['name']}."];
                    }
                }

                $checkSub = $db->query("SELECT id FROM `clinic_subscriptions` WHERE `clinic_id` = ? AND `plan_id` = ? LIMIT 1", [$clinic['id'], $plan['id']]);
                $subExists = $checkSub && method_exists($checkSub, 'getRowArray') && !empty($checkSub->getRowArray());

                if (!$subExists) {
                    $subSql = "INSERT INTO `clinic_subscriptions` (`clinic_id`, `plan_id`, `status`, `start_at`, `created_at`, `updated_at`) VALUES (?, ?, 'active', ?, ?, ?)";
                    $subRes = $db->query($subSql, [$clinic['id'], $plan['id'], $now, $now, $now]);
                    if ($subRes === false || $subRes === null) {
                        $context->recordJournal('stage_5', 'subscription_insert_failed', ['clinic_id' => $clinic['id']]);
                        return ['success' => false, 'stage' => 'stage_5_tenants_and_plans', 'message' => "Failed inserting subscription for clinic {$clinic['id']}."];
                    }
                }
            }

            $context->recordJournal('stage_5', 'completed', ['clinics' => count($data['tenants'])]);
            $context->internalAdvancePhase(__FUNCTION__, $db);

            return [
                'success' => true,
                'stage' => 'stage_5_tenants_and_plans',
                'message' => "Successfully seeded " . count($data['tenants']) . " clinics and baseline plan.",
            ];
        } catch (\Throwable $e) {
            $context->recordJournal('stage_5', 'exception', ['error' => get_class($e)]);
            return [
                'success' => false,
                'stage' => 'stage_5_tenants_and_plans',
                'message' => "Failed in Stage 5: " . get_class($e),
            ];
        }
    }

    /**
     * Stage 6: Seed canonical global platform defaults and clinic-scoped operational settings.
     * Uses idempotent upsert to prevent collisions with migration-seeded defaults.
     */
    public function executeStage6_SeedCanonicalSettings($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE5_DONE);

        $checkClinic1 = $db->query("SELECT id FROM `clinics` WHERE `id` = 1 LIMIT 1");
        if (!$checkClinic1 || !method_exists($checkClinic1, 'getRowArray') || empty($checkClinic1->getRowArray())) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: Clinic 1 does not exist before Stage 6 clinic settings seeding.");
        }

        $settings = $this->getCanonicalSettingsDefinition();
        $count = 0;
        $now = date('Y-m-d H:i:s');

        try {
            // Global settings (clinic_id = null)
            foreach ($settings['global'] as $key => $val) {
                $type = is_int($val) || is_float($val) ? 'number' : (is_bool($val) ? 'boolean' : (is_array($val) ? 'json' : 'string'));
                $storedVal = is_array($val) ? json_encode($val) : (string) $val;

                $checkG = $db->query("SELECT id FROM `settings` WHERE `clinic_id` IS NULL AND `setting_key` = ? LIMIT 1", [$key]);
                $gExists = $checkG && method_exists($checkG, 'getRowArray') && !empty($checkG->getRowArray());

                if ($gExists) {
                    $sql = "UPDATE `settings` SET `setting_value` = ?, `setting_type` = ?, `updated_at` = ? WHERE `clinic_id` IS NULL AND `setting_key` = ?";
                    $res = $db->query($sql, [$storedVal, $type, $now, $key]);
                } else {
                    $sql = "INSERT INTO `settings` (`clinic_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES (NULL, ?, ?, ?, 'Canonical global default', ?, ?)";
                    $res = $db->query($sql, [$key, $storedVal, $type, $now, $now]);
                }

                if ($res === false || $res === null) {
                    $context->recordJournal('stage_6', 'global_setting_failed', ['key' => $key]);
                    return [
                        'success' => false,
                        'stage' => 'stage_6_canonical_settings',
                        'message' => "Failed inserting/updating global setting {$key}.",
                    ];
                }
                $count++;
            }

            // Clinic 1 settings (clinic_id = 1)
            foreach ($settings['clinic'] as $key => $val) {
                $type = is_int($val) || is_float($val) ? 'number' : (is_bool($val) ? 'boolean' : (is_array($val) ? 'json' : 'string'));
                $storedVal = is_array($val) ? json_encode($val) : (string) $val;

                $checkC = $db->query("SELECT id FROM `settings` WHERE `clinic_id` = 1 AND `setting_key` = ? LIMIT 1", [$key]);
                $cExists = $checkC && method_exists($checkC, 'getRowArray') && !empty($checkC->getRowArray());

                if ($cExists) {
                    $sql = "UPDATE `settings` SET `setting_value` = ?, `setting_type` = ?, `updated_at` = ? WHERE `clinic_id` = 1 AND `setting_key` = ?";
                    $res = $db->query($sql, [$storedVal, $type, $now, $key]);
                } else {
                    $sql = "INSERT INTO `settings` (`clinic_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES (1, ?, ?, ?, 'Canonical clinic 1 setting', ?, ?)";
                    $res = $db->query($sql, [$key, $storedVal, $type, $now, $now]);
                }

                if ($res === false || $res === null) {
                    $context->recordJournal('stage_6', 'clinic_setting_failed', ['key' => $key]);
                    return [
                        'success' => false,
                        'stage' => 'stage_6_canonical_settings',
                        'message' => "Failed inserting/updating clinic setting {$key}.",
                    ];
                }
                $count++;
            }

            $context->recordJournal('stage_6', 'completed', ['settings_seeded' => $count]);
            $context->internalAdvancePhase(__FUNCTION__, $db);

            return [
                'success' => true,
                'stage' => 'stage_6_canonical_settings',
                'message' => "Successfully seeded {$count} canonical settings.",
            ];
        } catch (\Throwable $e) {
            $context->recordJournal('stage_6', 'exception', ['error' => get_class($e)]);
            return [
                'success' => false,
                'stage' => 'stage_6_canonical_settings',
                'message' => "Failed in Stage 6: " . get_class($e),
            ];
        }
    }

    /**
     * Stage 7: Seed developer personas and memberships in clinic_users.
     * Resolves role IDs strictly by canonical slug; guessed IDs or fallbacks are forbidden.
     */
    public function executeStage7_SeedDeveloperPersonasAndMemberships($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE6_DONE);

        $checkClinic1 = $db->query("SELECT id FROM `clinics` WHERE `id` = 1 LIMIT 1");
        if (!$checkClinic1 || !method_exists($checkClinic1, 'getRowArray') || empty($checkClinic1->getRowArray())) {
            throw new \RuntimeException("CRITICAL REFUSAL: Physical precondition failure: Clinic 1 does not exist before Stage 7 membership seeding.");
        }

        $data = $this->getSyntheticTenantsAndPersonasDefinition();
        $now = time();
        $nowDate = date('Y-m-d H:i:s');

        try {
            foreach ($data['personas'] as $persona) {
                if ($persona['id'] === 1) {
                    continue;
                }

                $key = $persona['username'];
                $hash = $context->getCredentials()->getPasswordHash($key);

                $checkUser = $db->query("SELECT id FROM `users` WHERE `id` = ? LIMIT 1", [$persona['id']]);
                $userExists = is_object($checkUser) && method_exists($checkUser, 'getRowArray') && !empty($checkUser->getRowArray());

                if (!$userExists) {
                    $userSql = "INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `active`, `created_at`, `first_name`, `last_name`)
                                VALUES (?, '127.0.0.1', ?, ?, ?, 1, ?, 'Sandbox', ?)";
                    $userRes = $db->query($userSql, [
                        $persona['id'],
                        $persona['username'],
                        $hash,
                        $persona['email'],
                        $now,
                        ucfirst($persona['username']),
                    ]);
                    if ($userRes === false || $userRes === null) {
                        $context->recordJournal('stage_7', 'user_insert_failed', ['username' => $persona['username']]);
                        return ['success' => false, 'stage' => 'stage_7_personas', 'message' => "Failed inserting user {$persona['username']}."];
                    }
                }

                if (!empty($persona['clinic_id'])) {
                    $roleId = $this->resolveRoleIdBySlug($db, $persona['role']);
                    if ($roleId === null) {
                        $context->recordJournal('stage_7', 'role_resolution_failed', ['role' => $persona['role']]);
                        return [
                            'success' => false,
                            'stage' => 'stage_7_personas',
                            'message' => "CRITICAL REFUSAL: Failed resolving canonical role ID for role slug '{$persona['role']}'. Guessed IDs or fallbacks are strictly prohibited.",
                        ];
                    }

                    $checkCu = $db->query("SELECT id FROM `clinic_users` WHERE `clinic_id` = ? AND `user_id` = ? LIMIT 1", [$persona['clinic_id'], $persona['id']]);
                    $cuExists = is_object($checkCu) && method_exists($checkCu, 'getRowArray') && !empty($checkCu->getRowArray());

                    if (!$cuExists) {
                        $cuSql = "INSERT INTO `clinic_users` (`clinic_id`, `user_id`, `role_id`, `status`, `created_at`, `updated_at`) VALUES (?, ?, ?, 'active', ?, ?)";
                        $cuRes = $db->query($cuSql, [$persona['clinic_id'], $persona['id'], $roleId, $nowDate, $nowDate]);
                        if ($cuRes === false || $cuRes === null) {
                            $context->recordJournal('stage_7', 'membership_insert_failed', ['user' => $persona['username']]);
                            return ['success' => false, 'stage' => 'stage_7_personas', 'message' => "Failed inserting membership for {$persona['username']}."];
                        }
                    }
                }
            }

            $context->recordJournal('stage_7', 'completed', ['personas' => count($data['personas'])]);
            $context->internalAdvancePhase(__FUNCTION__, $db);

            return [
                'success' => true,
                'stage' => 'stage_7_personas',
                'message' => "Successfully seeded developer personas and clinic memberships with canonical role IDs.",
            ];
        } catch (\Throwable $e) {
            $context->recordJournal('stage_7', 'exception', ['error' => get_class($e)]);
            return [
                'success' => false,
                'stage' => 'stage_7_personas',
                'message' => "Failed in Stage 7: " . get_class($e),
            ];
        }
    }

    /**
     * Stage 8: Verify fail-closed messaging transport lockdown.
     */
    public function executeStage8_VerifyMessagingTransportLockdown($context, object $db): array
    {
        $this->assertValidStageCall($context, $db, DeveloperSandboxRunContext::PHASE_STAGE7_DONE);

        $envSettings = [
            'email.protocol' => (string) getenv('email.protocol'),
            'email.SMTPHost' => (string) getenv('email.SMTPHost'),
            'email.SMTPPort' => (string) getenv('email.SMTPPort'),
        ];

        // Query plan fixture row id=1
        $planQuery = $db->query("SELECT features_json FROM plans WHERE id = 1");
        $plan = ($planQuery && method_exists($planQuery, 'getRowArray')) ? $planQuery->getRowArray() : null;

        $lockdownCheck = self::verifyOutboundLockdown($plan, $envSettings);
        if (!$lockdownCheck['valid']) {
            $context->recordJournal('stage_8', 'lockdown_verification_failed', [
                'errors' => $lockdownCheck['errors'],
            ]);
            return [
                'success' => false,
                'stage' => 'stage_8_transport_lockdown',
                'message' => "CRITICAL ISOLATION FAILURE: " . implode('; ', $lockdownCheck['errors']),
            ];
        }

        $context->recordJournal('stage_8', 'completed', ['transport' => 'fail_closed']);
        $context->internalAdvancePhase(__FUNCTION__, $db);

        return [
            'success' => true,
            'stage' => 'stage_8_transport_lockdown',
            'message' => "Messaging transport and notification outbound paths confirmed strictly fail-closed.",
        ];
    }

    /**
     * Resolve a role ID strictly by its canonical slug. Never guesses or falls back.
     */
    public function resolveRoleIdBySlug(object $db, string $slug): ?int
    {
        $query = $db->query("SELECT id FROM `roles` WHERE `slug` = ? AND `clinic_id` IS NULL LIMIT 1", [$slug]);
        if (!$query || !method_exists($query, 'getRowArray')) {
            return null;
        }

        $row = $query->getRowArray();
        if (is_array($row) && isset($row['id']) && is_numeric($row['id'])) {
            return (int) $row['id'];
        }

        return null;
    }

    /**
     * Stage 3: Get list of migrations to run for the specified mode.
     *
     * @return array<string, array{file: string, class: string, path: string, is_dormant_optional: bool, version: string, pinned_hash: string}>
     */
    public function resolveMigrationPlan(string $mode = self::MODE_PRODUCTION_BASELINE): array
    {
        if (!in_array($mode, self::SUPPORTED_MODES, true)) {
            throw new \InvalidArgumentException("CRITICAL REFUSAL: Unsupported mode '{$mode}'.");
        }

        $migrationDir = dirname(__DIR__) . '/app/Database/Migrations';
        $allFiles = glob($migrationDir . '/*.php') ?: [];
        sort($allFiles);

        $plan = [];
        foreach ($allFiles as $filePath) {
            $baseName = basename($filePath);
            $isExcluded = in_array($baseName, self::EXCLUDED_MIGRATIONS, true);

            if ($mode === self::MODE_PRODUCTION_BASELINE && $isExcluded) {
                continue;
            }

            $content = file_get_contents($filePath) ?: '';
            preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends/i', $content, $mClass);
            preg_match('/^\s*namespace\s+([^;]+);/m', $content, $mNamespace);
            if (empty($mClass[1]) || empty($mNamespace[1])) {
                throw new \RuntimeException('Cannot resolve canonical migration class metadata.');
            }
            $className = trim($mNamespace[1]) . '\\' . $mClass[1];

            $version = substr($baseName, 0, (int) strpos($baseName, '_'));

            $fileHash = hash('sha256', $content);
            $pinnedHash = self::REVIEWED_MIGRATION_MANIFEST[$baseName] ?? null;
            if ($pinnedHash === null || !hash_equals($pinnedHash, $fileHash)) {
                throw new \RuntimeException("CRITICAL REFUSAL: Migration source file '{$baseName}' does not match immutable reviewed manifest (reviewed commit " . self::REVIEWED_FEATURE_COMMIT . ").");
            }

            $plan[$baseName] = [
                'file' => $baseName,
                'class' => $className,
                'path' => $filePath,
                'is_dormant_optional' => $isExcluded,
                'version' => $version,
                'pinned_hash' => $pinnedHash,
            ];
        }

        return $plan;
    }

    /**
     * Stage 6: Canonical settings definition (source-grounded).
     *
     * @return array{global: array<string, mixed>, clinic: array<string, mixed>}
     */
    public function getCanonicalSettingsDefinition(): array
    {
        return [
            'global' => [
                'default_timezone' => 'Asia/Jerusalem',
                'default_currency' => 'ILS',
                'default_currency_position' => 'after',
                'default_date_format' => 'd/m/Y',
                'default_time_format' => '12',
                'default_appointment_duration' => 30,
                'default_booking_chair_max' => 3,
                'app_name' => 'DentaCare Developer Sandbox',
            ],
            'clinic' => [
                'timezone' => 'Asia/Jerusalem',
                'currency' => 'ILS',
                'currency_position' => 'after',
                'booking_chair_count' => 1,
                'booking_chair_names' => ['Doctor 1'],
                'dynamic_doctor_scheduling_enabled' => 0, // Strict invariant: dynamic scheduling OFF
            ],
        ];
    }

    /**
     * Stage 5 & 7: Synthetic tenants, plans, and developer personas definition.
     *
     * @return array{tenants: array, plan: array, personas: array}
     */
    public function getSyntheticTenantsAndPersonasDefinition(): array
    {
        return [
            'tenants' => [
                ['id' => 1, 'name' => 'Alpha Dental Practice', 'slug' => 'alpha-dental', 'status' => 'active'],
                ['id' => 2, 'name' => 'Beta Dental Clinic', 'slug' => 'beta-dental', 'status' => 'active'],
            ],
            'plan' => [
                'id' => 1,
                'name' => 'Developer Baseline Plan',
                'status' => 'active',
                'features_json' => [
                    'contracts' => ['enabled' => true, 'signature' => ['enabled' => true]],
                    'attachments' => ['enabled' => true],
                    'kiosk' => ['enabled' => true],
                    'exports' => ['enabled' => true],
                    'notifications' => [
                        'sms' => ['enabled' => false],
                        'whatsapp' => ['enabled' => false],
                        'email' => ['enabled' => false],
                    ],
                ],
            ],
            'personas' => [
                ['id' => 1, 'username' => 'superadmin', 'email' => 'admin@sandbox.test', 'role' => 'super_admin', 'clinic_id' => null],
                ['id' => 2, 'username' => 'manager', 'email' => 'manager@alpha.test', 'role' => 'practice_manager', 'clinic_id' => 1],
                ['id' => 3, 'username' => 'doctor', 'email' => 'doctor@alpha.test', 'role' => 'doctor', 'clinic_id' => 1],
                ['id' => 4, 'username' => 'reception', 'email' => 'reception@alpha.test', 'role' => 'receptionist', 'clinic_id' => 1],
                ['id' => 5, 'username' => 'foreign_staff', 'email' => 'staff@beta.test', 'role' => 'receptionist', 'clinic_id' => 2],
            ],
        ];
    }
}
