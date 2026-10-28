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
        return $this->resolvedState($clinicId, $current, $platform);
    }

    private function resolvedState(?int $clinicId, array $current, array $platform): array
    {
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

    public function catalog(): array
    {
        if (!$this->available()) return ['version'=>0,'policy'=>StudioThemeCatalog::normalizePolicy([])];
        $row = $this->db->table('studio_theme_current')->select('version,payload_json')->where('scope_key','catalog')->get()->getRowArray();
        return $this->catalogState($row ?: null);
    }

    private function catalogState(?array $row): array
    {
        $payload = $row['payload_json'] ?? null;
        return ['version'=>(int)($row['version'] ?? 0),'policy'=>$payload === null ? StudioThemeCatalog::normalizePolicy([]) : StudioThemeCatalog::normalizePolicy(json_decode($payload,true,32,JSON_THROW_ON_ERROR))];
    }

    /** Platform-only caller: bounded clinic metadata, no patient or financial joins. */
    public function clinicOverrides(int $page = 1, string $search = ''): array
    {
        if (!$this->available()) return ['rows'=>[],'has_more'=>false,'page'=>1];
        if ($page < 1 || $page > 10000 || mb_strlen($search)>100) throw new \DomainException('Invalid clinic appearance page.');
        $query = $this->db->table('clinics c')->select('c.id,c.name,c.status,t.version,t.payload_json')
            ->join('studio_theme_current t', "t.scope_key=CONCAT('clinic:',c.id)",'left',false);
        if ($search !== '') $query->like('c.name',$search);
        $rows = $query->orderBy('c.id','ASC')->limit(51,($page-1)*50)->get()->getResultArray();
        $more = count($rows)>50;
        $rows = array_slice($rows,0,50);
        foreach ($rows as &$row) {
            $row['inherited'] = $row['payload_json'] === null;
            $row['preset'] = $row['inherited'] ? null : $this->decode($row['payload_json'])['preset'];
            unset($row['payload_json']);
        }
        unset($row);
        return ['rows'=>$rows,'has_more'=>$more,'page'=>$page];
    }

    public function catalogHistory(): array
    {
        if (!$this->available()) return [];
        return $this->db->table('studio_theme_revisions')->select('version,actor_id,action,reason,created_at')->where('scope_key','catalog')->orderBy('version','DESC')->limit(25)->get()->getResultArray();
    }

    /** Reuses current/revision schema; no new records per tenant or destructive catalog removal. */
    public function publishCatalog(int $actorId, int $expectedVersion, int $expectedPlatformVersion, array $input, string $reason, callable $authorize): array
    {
        if (!$this->available()) throw new \DomainException('Theme editing is unavailable.');
        $reason=trim($reason);
        if ($actorId<=0 || $expectedVersion<0 || $expectedPlatformVersion<0 || $reason==='' || mb_strlen($reason)>500 || !preg_match('//u',$reason)) throw new \DomainException('Catalog publication requires a bounded reason and current versions.');
        $policy=StudioThemeCatalog::normalizePolicy($input);
        $payload=json_encode($policy,JSON_THROW_ON_ERROR);
        if ((int)$this->db->transDepth!==0 || !$this->db->transStatus() || !$this->db->transBegin()) throw new \RuntimeException('Catalog publication requires a healthy owned transaction.');
        try {
            $platform=$this->db->query('SELECT version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key=? FOR UPDATE',['platform'])->getRowArray();
            if (!$platform || (int)$platform['version']!==$expectedPlatformVersion) throw new \DomainException('Platform default changed; reload.');
            $defaultPreset=$platform['payload_json']===null ? StudioThemeCatalog::DEFAULT_PRESET : $this->decode($platform['payload_json'])['preset'];
            if (!in_array($defaultPreset,$policy['enabled'],true)) throw new \DomainException('The active platform default must remain available.');
            $current=$this->db->query('SELECT version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key=? FOR UPDATE',['catalog'])->getRowArray();
            if (!$current) {
                if (!$this->db->table('studio_theme_current')->insert(['scope_key'=>'catalog','version'=>0,'payload_json'=>null,'updated_at'=>gmdate('Y-m-d H:i:s')])) throw new \RuntimeException('Cannot initialize theme catalog.');
                $current=['version'=>0,'payload_json'=>null];
            }
            if ((int)$current['version']!==$expectedVersion) throw new \DomainException('Catalog changed; reload.');
            if ($authorize($this->db,null,$actorId)!==true) throw new \DomainException('Catalog publication is not authorized.');
            if ($this->catalogState($current)['policy']===$policy) {
                if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot confirm unchanged catalog.');
                return ['version'=>$expectedVersion,'policy'=>$policy];
            }
            $version=$expectedVersion+1;$now=gmdate('Y-m-d H:i:s');
            if (!$this->db->table('studio_theme_revisions')->insert(['scope_key'=>'catalog','version'=>$version,'actor_id'=>$actorId,'action'=>'catalog','reason'=>$reason,'before_json'=>$current['payload_json'],'payload_json'=>$payload,'created_at'=>$now])) throw new \RuntimeException('Cannot append catalog audit.');
            if (!$this->db->table('studio_theme_current')->where('scope_key','catalog')->where('version',$expectedVersion)->update(['version'=>$version,'payload_json'=>$payload,'updated_at'=>$now]) || $this->db->affectedRows()!==1) throw new \RuntimeException('Cannot advance catalog version.');
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot commit catalog.');
            return ['version'=>$version,'policy'=>$policy];
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth>0) $this->db->transRollback();
            throw $e;
        }
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
            if ($payload !== null || $clinicId === null) {
                $policyRow=$this->db->query('SELECT version,payload_json FROM '.$this->table('studio_theme_current').' WHERE scope_key=?',['catalog'])->getRowArray();
                $enabled=$this->catalogState($policyRow ?: null)['policy']['enabled'];
                $selected=$payload===null ? StudioThemeCatalog::DEFAULT_PRESET : $this->decode($payload)['preset'];
                $existing=$current['payload_json']===null ? null : $this->decode($current['payload_json'])['preset'];
                if (!in_array($selected,$enabled,true) && ($clinicId===null || $existing!==$selected)) throw new \DomainException('This preset is unavailable for new selection.');
            }
            // Exact duplicate is a no-op only after freshness and current authorization checks.
            if ($current['payload_json'] === $payload) {
                $result = $this->resolvedState($clinicId, $current, $platform);
                if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot commit unchanged theme check.');
                return $result;
            }
            $version = $expectedVersion + 1;
            $now = gmdate('Y-m-d H:i:s');
            if (!$this->db->table('studio_theme_revisions')->insert(['scope_key'=>$scope,'version'=>$version,'actor_id'=>$actorId,'action'=>$action,'reason'=>$reason,'before_json'=>$current['payload_json'],'payload_json'=>$payload,'created_at'=>$now])) throw new \RuntimeException('Cannot append theme audit.');
            if (!$this->db->table('studio_theme_current')->where('scope_key',$scope)->where('version',$expectedVersion)->update(['version'=>$version,'payload_json'=>$payload,'updated_at'=>$now]) || $this->db->affectedRows() !== 1) throw new \RuntimeException('Cannot advance theme revision.');
            $committedState = ['version'=>$version,'payload_json'=>$payload];
            $result = $this->resolvedState($clinicId, $committedState, $clinicId === null ? $committedState : $platform);
            if (!$this->db->transStatus() || !$this->db->transCommit()) throw new \RuntimeException('Cannot commit theme revision.');
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
        return $result;
    }
}
