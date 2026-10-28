<?php
declare(strict_types=1);
namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Connection-bound persistence only. The controller must authorize the verified scope, never a request-supplied tenant. */
final class StudioThemeStore
{
    public function __construct(private BaseConnection $db, private bool $enabled = false) {}

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function scope(?int $clinicId): string
    {
        if ($clinicId !== null && $clinicId <= 0) throw new \DomainException('Invalid theme clinic scope.');
        return $clinicId === null ? 'platform' : 'clinic:'.$clinicId;
    }

    public function available(): bool
    {
        return $this->enabled && $this->db->tableExists('studio_theme_current') && $this->db->tableExists('studio_theme_revisions');
    }

    /** Single read snapshot; never static-cached across tenants, users, or requests. */
    public function load(?int $clinicId): array
    {
        $scope = $this->scope($clinicId);
        $fallback = ['theme'=>StudioThemeCatalog::normalize([]),'platform_version'=>0,'version'=>0,'inherited'=>$clinicId !== null];
        if (!$this->available()) return $fallback;
        $rows = $this->db->query('SELECT scope_key,version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key IN (?,?)', ['platform',$scope]);
        if ($rows === false) throw new \RuntimeException('Cannot read theme state.');
        $byScope = array_column($rows->getResultArray(), null, 'scope_key');
        if (!isset($byScope['platform'])) throw new \RuntimeException('Theme platform anchor is missing.');
        $platform = $byScope['platform'];
        $current = $byScope[$scope] ?? ['version'=>0,'payload_json'=>null];
        $payload = $current['payload_json'] ?? $platform['payload_json'];
        return [
            'theme'=>$payload === null ? StudioThemeCatalog::normalize([]) : $this->decode($payload),
            'platform_version'=>(int)$platform['version'], 'version'=>(int)$current['version'],
            'inherited'=>$clinicId !== null && $current['payload_json'] === null,
        ];
    }

    private function decode(string $json): array
    {
        $input = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($input)) throw new \DomainException('Invalid stored theme.');
        return StudioThemeCatalog::normalize($input);
    }

    /** Bounded, scope-specific audit list. No historical row locking. */
    public function history(?int $clinicId): array
    {
        if (!$this->available()) return [];
        return $this->db->table('studio_theme_revisions')->select('version,actor_id,action,reason,created_at')->where('scope_key',$this->scope($clinicId))->orderBy('version','DESC')->limit(25)->get()->getResultArray();
    }

    /**
     * Lock ordering: platform anchor -> clinic parent -> clinic theme -> current authorizer rows.
     * Authorizer runs inside our owned transaction and MUST return true from current canonical RBAC.
     * Revert creates new immutable history, never deletes/rewrites an earlier revision.
     */
    public function publish(?int $clinicId, int $actorId, int $expectedVersion, int $expectedPlatformVersion, ?array $input, string $reason, callable $authorize, ?int $revertVersion = null): array
    {
        if (!$this->available()) throw new \DomainException('Theme editing is unavailable.');
        $scope = $this->scope($clinicId);
        $reason = trim($reason);
        if ($actorId <= 0 || $expectedVersion < 0 || $expectedPlatformVersion < 0 || $reason === '' || mb_strlen($reason)>500 || !preg_match('//u',$reason)) throw new \DomainException('Theme revision requires an actor, versions and a bounded reason.');
        if ($revertVersion !== null && $revertVersion <= 0) throw new \DomainException('Invalid theme recovery revision.');
        $payload = $input === null ? null : json_encode(StudioThemeCatalog::normalize($input), JSON_THROW_ON_ERROR);
        if ((int)$this->db->transDepth !== 0 || !$this->db->transStatus() || !$this->db->transBegin()) throw new \RuntimeException('Theme publish requires a healthy owned transaction.');
        try {
            $platformQuery = $this->db->query('SELECT version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key=? FOR UPDATE', ['platform']);
            if ($platformQuery === false || !($platform = $platformQuery->getRowArray())) throw new \RuntimeException('Theme anchor unavailable.');
            if ((int)$platform['version'] !== $expectedPlatformVersion) throw new \DomainException('Platform theme changed; reload before publishing.');
            $current = $platform;
            if ($clinicId !== null) {
                $parent = $this->db->query('SELECT id,status FROM '.$this->table('clinics').' WHERE id=? FOR UPDATE', [$clinicId]);
                if ($parent === false || !($clinic = $parent->getRowArray()) || $clinic['status'] !== 'active') throw new \DomainException('Theme clinic unavailable.');
                // The platform anchor serializes creation of an absent scope; no broad range locks.
                $query = $this->db->query('SELECT version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key=? FOR UPDATE', [$scope]);
                if ($query === false) throw new \RuntimeException('Cannot lock clinic theme.');
                $current = $query->getRowArray();
                if (!$current) {
                    if (!$this->db->table('studio_theme_current')->insert(['scope_key'=>$scope,'version'=>0,'payload_json'=>null,'updated_at'=>gmdate('Y-m-d H:i:s')])) throw new \RuntimeException('Cannot initialize clinic theme.');
                    $current = ['version'=>0,'payload_json'=>null];
                }
            }
            if ((int)$current['version'] !== $expectedVersion) throw new \DomainException('Clinic theme changed; reload before publishing.');
            if ($authorize($this->db, $clinicId, $actorId) !== true) throw new \DomainException('Theme publish is not authorized.');
            $action = $input === null ? 'inherit' : 'publish';
            if ($revertVersion !== null) {
                $old = $this->db->table('studio_theme_revisions')->select('payload_json')->where('scope_key',$scope)->where('version',$revertVersion)->get()->getRowArray();
                if (!$old) throw new \DomainException('Theme recovery revision not found in this scope.');
                $payload = $old['payload_json'] === null ? null : json_encode($this->decode($old['payload_json']), JSON_THROW_ON_ERROR);
                $action = 'revert';
            }
            // Exact duplicate is a no-op only after freshness and current authorization checks.
            if ($current['payload_json'] === $payload) {
                if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot commit unchanged theme check.');
                return $this->load($clinicId);
            }
            $version = $expectedVersion + 1;
            $now = gmdate('Y-m-d H:i:s');
            if (!$this->db->table('studio_theme_revisions')->insert(['scope_key'=>$scope,'version'=>$version,'actor_id'=>$actorId,'action'=>$action,'reason'=>$reason,'before_json'=>$current['payload_json'],'payload_json'=>$payload,'created_at'=>$now])) throw new \RuntimeException('Cannot append theme audit.');
            if (!$this->db->table('studio_theme_current')->where('scope_key',$scope)->where('version',$expectedVersion)->update(['version'=>$version,'payload_json'=>$payload,'updated_at'=>$now]) || $this->db->affectedRows() !== 1) throw new \RuntimeException('Cannot advance theme revision.');
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot commit theme revision.');
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
        return $this->load($clinicId);
    }
}
