<?php

declare(strict_types=1);
namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Libraries\IonAuth;
use App\Models\ControlPlaneAuditModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Lossless clinic-scoped audit bundles; the caller owns transaction and compact pointer storage. */
final class ScheduleImpactAuditStore
{
    private const EVENT = 'schedule_impact_bundle_part';
    private const CHUNK_BYTES = 40000;
    private const MAX_PARTS = 4096; // Refuse oversized bundles explicitly; never truncate evidence.
    private const JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    private ControlPlaneAuditModel $audit;

    public function __construct(private BaseConnection $db, ?ControlPlaneAuditModel $audit = null)
    {
        $this->audit = $audit ?? new ControlPlaneAuditModel($db);
    }

    /** Required inserts participate in the caller's single checked mutation transaction. */
    public function write(int $clinicId, int $actorId, array $fullBundle): array
    {
        $actor = $this->authorizedActor($clinicId);
        if ($this->db->transDepth !== 1 || $actor !== $actorId
            || !(new PermissionService($this->db))->hasPermission($actorId, 'settings', 'edit')) {
            throw new RuntimeException('Owned authorized audit transaction required.');
        }
        $reviewId = bin2hex(random_bytes(16));
        $now = gmdate('Y-m-d H:i:s');
        $fullBundle['review_id'] = $reviewId;
        $fullBundle['clinic_id'] = $clinicId;
        $fullBundle['actor_user_id'] = $actorId;
        $fullBundle['applied_at'] = $now;
        $json = json_encode($fullBundle, self::JSON_FLAGS); // Invalid UTF-8, recursion and unsupported values fail before inserts.
        if (!mb_check_encoding($json, 'UTF-8')) throw new RuntimeException('Invalid audit encoding.');
        $hash = hash('sha256', $json);
        $encoded = base64_encode($json);
        $parts = (int) ceil(strlen($encoded) / self::CHUNK_BYTES);
        if ($parts < 1 || $parts > self::MAX_PARTS) throw new RuntimeException('Audit bundle exceeds safe storage limit.');
        $firstId = $lastId = 0;
        for ($index = 0; $index < $parts; $index++) {
            $metadata = ['version'=>1, 'clinic_id'=>$clinicId, 'review_id'=>$reviewId, 'actor_user_id'=>$actorId,
                'part_index'=>$index, 'parts'=>$parts, 'bundle_hash'=>$hash,
                'data'=>substr($encoded, $index * self::CHUNK_BYTES, self::CHUNK_BYTES)];
            $payload = json_encode($metadata, self::JSON_FLAGS);
            if (strlen($payload) > 60000) throw new RuntimeException('Audit part exceeds TEXT byte budget.');
            $id = $this->audit->insert(['actor_user_id'=>$actorId, 'event_type'=>self::EVENT,
                'route'=>'/settings/apply-schedule-impact', 'method'=>'POST', 'metadata_json'=>$payload, 'created_at'=>$now], true);
            $id = $this->positiveInteger($id);
            if ($id === null || !$this->db->transStatus() || $this->db->transDepth !== 1 || $id <= $lastId) {
                throw new RuntimeException('Required schedule impact audit insert failed.');
            }
            if ($index === 0) $firstId = $id;
            $lastId = $id;
        }
        return ['version'=>1, 'review_id'=>$reviewId, 'bundle_hash'=>$hash, 'parts'=>$parts,
            'first_id'=>$firstId, 'last_id'=>$lastId, 'actor_user_id'=>$actorId, 'applied_at'=>$now];
    }

    /** Missing, pruned, malformed, duplicated or corrupt evidence is unavailable, never partially trusted. */
    public function read(int $clinicId, array $pointer): ?array
    {
        try {
            if ($this->authorizedActor($clinicId) === null || ($pointer['version'] ?? null) !== 1) return null;
            $reviewId = $pointer['review_id'] ?? null;
            $hash = $pointer['bundle_hash'] ?? null;
            $parts = $this->positiveInteger($pointer['parts'] ?? null);
            $first = $this->positiveInteger($pointer['first_id'] ?? null);
            $last = $this->positiveInteger($pointer['last_id'] ?? null);
            $actor = $this->positiveInteger($pointer['actor_user_id'] ?? null);
            if (!is_string($reviewId) || preg_match('/^[a-f0-9]{32}$/D', $reviewId) !== 1
                || !is_string($hash) || preg_match('/^[a-f0-9]{64}$/D', $hash) !== 1
                || $parts === null || $parts > self::MAX_PARTS || $first === null || $last === null || $actor === null
                || $last < $first || $last - $first + 1 < $parts) return null;
            // CASE protects JSON_EXTRACT even if the optimizer evaluates it before JSON_VALID.
            $safeJson = "CASE WHEN JSON_VALID(metadata_json) THEN metadata_json ELSE '{}' END";
            $sql = "SELECT id, actor_user_id, metadata_json FROM control_plane_audits
                WHERE event_type=? AND id>=? AND id<=? AND actor_user_id=? AND JSON_VALID(metadata_json)=1
                AND JSON_UNQUOTE(JSON_EXTRACT($safeJson, '$.clinic_id'))=?
                AND JSON_UNQUOTE(JSON_EXTRACT($safeJson, '$.review_id'))=? ORDER BY id ASC LIMIT " . ($parts + 1);
            $rows = $this->db->query($sql, [self::EVENT, $first, $last, $actor, (string)$clinicId, $reviewId])->getResultArray();
            if (count($rows) !== $parts || (int)$rows[0]['id'] !== $first || (int)$rows[$parts-1]['id'] !== $last) return null;
            $encoded = '';
            foreach ($rows as $index => $row) {
                if (!is_string($row['metadata_json']) || strlen($row['metadata_json']) > 60000) return null;
                $meta = json_decode($row['metadata_json'], true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($meta) || ($meta['version'] ?? null) !== 1 || ($meta['clinic_id'] ?? null) !== $clinicId
                    || ($meta['review_id'] ?? null) !== $reviewId || ($meta['actor_user_id'] ?? null) !== $actor
                    || (int)$row['actor_user_id'] !== $actor || ($meta['part_index'] ?? null) !== $index
                    || ($meta['parts'] ?? null) !== $parts || ($meta['bundle_hash'] ?? null) !== $hash
                    || !is_string($meta['data'] ?? null) || strlen($meta['data']) > self::CHUNK_BYTES
                    || ($index < $parts-1 && strlen($meta['data']) !== self::CHUNK_BYTES)) return null;
                $encoded .= $meta['data'];
            }
            $json = base64_decode($encoded, true);
            if ($json === false || base64_encode($json) !== $encoded || !hash_equals($hash, hash('sha256', $json))
                || !mb_check_encoding($json, 'UTF-8')) return null;
            $bundle = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($bundle) || ($bundle['clinic_id'] ?? null) !== $clinicId
                || ($bundle['actor_user_id'] ?? null) !== $actor || ($bundle['review_id'] ?? null) !== $reviewId
                || ($bundle['applied_at'] ?? null) !== ($pointer['applied_at'] ?? null)) return null;
            return $bundle;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function positiveInteger($value): ?int
    {
        if (!is_int($value) && !is_string($value)) return null;
        if (preg_match('/^[1-9][0-9]*$/D', (string)$value) !== 1) return null;
        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        return $parsed === false ? null : $parsed;
    }

    private function authorizedActor(int $clinicId): ?int
    {
        if ($clinicId <= 0 || (int)session('active_clinic_id') !== $clinicId || session('global_mode') === true
            || session('is_impersonating') === true || (int)session('impersonated_clinic_id') > 0
            || ApiRuntimeContext::getUserId() !== null || ApiRuntimeContext::getClinicId() !== null) return null;
        $auth = new IonAuth();
        $actor = (int)$auth->getUserId();
        if (!$auth->loggedIn() || $actor <= 0) return null;
        $permissions = new PermissionService($this->db);
        if ($permissions->getTenantRoleId($actor, $clinicId) === null) return null;
        return $permissions->hasPermission($actor, 'settings', 'view') || $permissions->hasPermission($actor, 'settings', 'edit') ? $actor : null;
    }
}
