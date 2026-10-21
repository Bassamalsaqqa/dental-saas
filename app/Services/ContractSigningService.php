<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use App\Models\TreatmentContractModel;
use App\Models\TreatmentContractSignatureModel;
use App\Models\FileAttachmentModel;
use App\Models\ActivityLogModel;

/** Two short database transactions surround immutable document publication.
 * Publication and database commit are separate durable steps; journals require reconciliation.
 */
class ContractSigningService
{
    public const ACTOR_STAFF = 'staff';
    public const ACTOR_KIOSK = 'kiosk';
    private BaseConnection $db;
    private ContractDocumentJournal $journal;
    private TreatmentContractModel $contracts;
    private TreatmentContractSignatureModel $signatures;
    private FileAttachmentModel $attachments;
    private ActivityLogModel $audit;
    public $renderHtmlFn = null;
    public $auditFn = null;
    /** Optional fault/coordination seam; never replaces actual SQL or storage. */
    public $phaseFn = null;

    public function __construct(?BaseConnection $db = null, ?ContractDocumentJournal $journal = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->journal = $journal ?? new ContractDocumentJournal();
        $this->contracts = new TreatmentContractModel($this->db);
        $this->signatures = new TreatmentContractSignatureModel($this->db);
        $this->attachments = new FileAttachmentModel($this->db);
        $this->audit = new ActivityLogModel($this->db);
    }

    public function sign(int $clinicId, int $contractId, int $patientId, string $signerName,
        string $signatureData, string $actorType, ?int $staffUserId, string $ipAddress,
        string $userAgent, callable $renderHtml, ?int $examinationId = null): array
    {
        $this->validateInput($signerName, $signatureData);
        if (!in_array($actorType, [self::ACTOR_STAFF, self::ACTOR_KIOSK], true)
            || ($actorType === self::ACTOR_STAFF && (!$staffUserId || $staffUserId < 1))) {
            throw new \RuntimeException('SIGN_ERR_INVALID_ACTOR: Invalid signing actor.');
        }
        $capture = $this->transaction(function () use ($clinicId, $contractId, $patientId, $examinationId) {
            return $this->capture($clinicId, $contractId, $patientId, $examinationId);
        });
        if ($capture['contract']['status'] === 'signed') {
            return $this->existing($capture, $signerName, $signatureData);
        }
        $this->assertDraft($capture);
        $fingerprint = $this->fingerprint($capture);
        $consentFingerprint = $this->immutableConsentFingerprint($capture['contract'], $capture['items'], $capture['snapshot']);
        $now = date('Y-m-d H:i:s');
        $sig = ['clinic_id' => $clinicId, 'contract_id' => $contractId, 'signer_name' => $signerName,
            'signer_role' => 'patient', 'signature_data' => $signatureData, 'signed_at' => $now,
            'ip_address' => $ipAddress, 'user_agent' => substr($userAgent, 0, 255)];
        $snapshot = $capture['snapshot'];
        if ($snapshot) {
            $snapshot['svg_snapshot'] = (new OdontogramSvgSnapshotService())->decode($snapshot['svg_snapshot'] ?? null);
        }
        $data = ['contract' => $capture['contract'], 'patient' => $capture['patient'],
            'clinic' => $capture['clinic'], 'items' => $capture['items'], 'snapshot' => $snapshot, 'signature' => $sig];
        $this->phase('render');
        $html = $this->renderHtmlFn ? ($this->renderHtmlFn)($data) : $renderHtml($data);
        if (!is_string($html) || strlen($html) < 50) {
            throw new \RuntimeException('SIGN_ERR_RENDER: Invalid document.');
        }
        $artifact = $this->journal->publish($clinicId, $contractId, $html, ['source_fingerprint' => $fingerprint, 'consent_fingerprint' => $consentFingerprint]);
        if (!$this->journal->verify($artifact)) {
            throw new \RuntimeException('SIGN_ERR_VERIFY: Published evidence failed verification.');
        }
        $this->phase('published');
        try {
            $result = $this->transaction(function () use ($clinicId, $contractId, $patientId, $examinationId,
                $fingerprint, $consentFingerprint, $artifact, $sig, $actorType, $staffUserId, $now) {
                $fresh = $this->capture($clinicId, $contractId, $patientId, $examinationId);
                if ($fresh['contract']['status'] === 'signed') {
                    return ['winner' => $fresh];
                }
                $this->assertDraft($fresh);
                if (!hash_equals($fingerprint, $this->fingerprint($fresh))) {
                    throw new \RuntimeException('SIGN_ERR_CHANGED: Draft changed during rendering.');
                }
                $quota = (new PlanGuard($this->db))->assertQuota($clinicId, 'storage_gb', (int) $artifact['size'],
                    ['entity_type' => 'contract', 'entity_id' => $contractId, 'purpose' => 'contract_pdf']);
                if (is_array($quota) && empty($quota['allowed'])) {
                    throw new \RuntimeException('SIGN_ERR_QUOTA: Storage quota denied.');
                }
                $signatureId = $this->signatures->insert($sig);
                $this->checked($signatureId, 'signature');
                $attachmentId = $this->attachments->insert([
                    'clinic_id' => $clinicId, 'file_name' => basename($artifact['relative_path']),
                    'original_name' => $artifact['original_name'], 'file_path' => $artifact['relative_path'],
                    'mime_type' => 'text/html', 'file_size' => $artifact['size'], 'file_hash' => $artifact['hash'],
                    'entity_type' => 'contract', 'entity_id' => $contractId, 'purpose' => 'contract_pdf',
                    'created_by' => $actorType === self::ACTOR_STAFF ? $staffUserId : 0]);
                $this->checked($attachmentId, 'attachment');
                $meta = ['signature_id' => (int) $signatureId, 'signer_name' => $sig['signer_name'],
                    'actor_type' => $actorType, 'staff_user_id' => $actorType === self::ACTOR_STAFF ? $staffUserId : null,
                    'operation' => $artifact['operation'], 'source_fingerprint' => $fingerprint,
                    'consent_fingerprint' => $consentFingerprint,
                    'artifact_sha256' => $artifact['hash']];
                $hash = hash('sha256', $consentFingerprint . "\n" . $sig['signer_name'] . "\n" . $sig['signature_data']);
                $this->phase('link');
                $this->checked($this->contracts->update($contractId, ['status' => 'signed', 'signed_at' => $now,
                    'signature_hash' => $hash, 'signature_meta_json' => json_encode($meta, JSON_THROW_ON_ERROR),
                    'signed_attachment_id' => $attachmentId]), 'contract link');
                $this->writeAudit($clinicId, $actorType === self::ACTOR_STAFF ? (int) $staffUserId : 0,
                    'contract_sign', $patientId, $contractId, $meta);
                return ['signature_id' => (int) $signatureId, 'attachment_id' => (int) $attachmentId];
            });
        } catch (\Throwable $e) {
            // Commit failure can be ambiguous. Retain the artifact and journal for reconciliation.
            throw $e;
        }
        if (isset($result['winner'])) {
            $winner = $this->existing($result['winner'], $signerName, $signatureData);
            $this->journal->markUnreferenced($artifact, 'Concurrent complete winner');
            return $winner;
        }
        try {
            $this->journal->markCommitted($artifact, $result['signature_id'], $result['attachment_id']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('SIGN_ERR_RECONCILIATION: Database committed; journal requires reconciliation.', 0, $e);
        }
        return ['contract' => $this->row('SELECT * FROM treatment_contracts WHERE clinic_id=? AND id=?', [$clinicId, $contractId]),
            'signature' => $this->row('SELECT * FROM treatment_contract_signatures WHERE clinic_id=? AND id=?', [$clinicId, $result['signature_id']]),
            'attachment' => $this->row('SELECT * FROM file_attachments WHERE clinic_id=? AND id=?', [$clinicId, $result['attachment_id']])];
    }

    private function capture(int $clinicId, int $contractId, ?int $patientId, ?int $examId): array
    {
        // Parent identity hint is read before locking; the locked contract must match it exactly.
        $hint = $this->row('SELECT patient_id, examination_id FROM treatment_contracts WHERE clinic_id=? AND id=? AND deleted_at IS NULL', [$clinicId, $contractId]);
        if (!$hint || ($patientId !== null && (int) $hint['patient_id'] !== $patientId)) {
            throw new \RuntimeException('SIGN_ERR_NOT_FOUND: Contract owner not found.');
        }
        $clinic = $this->row('SELECT * FROM clinics WHERE id=? FOR UPDATE', [$clinicId]);
        $patient = $this->row('SELECT * FROM patients WHERE clinic_id=? AND id=? FOR UPDATE', [$clinicId, $hint['patient_id']]);
        $exam = null;
        if (!empty($hint['examination_id'])) {
            $exam = $this->row('SELECT * FROM examinations WHERE clinic_id=? AND patient_id=? AND id=? FOR UPDATE', [$clinicId, $hint['patient_id'], $hint['examination_id']]);
            if (!$exam) { throw new \RuntimeException('SIGN_ERR_EXAM_MISMATCH: Examination missing.'); }
        }
        $contract = $this->row('SELECT * FROM treatment_contracts WHERE clinic_id=? AND id=? AND deleted_at IS NULL FOR UPDATE', [$clinicId, $contractId]);
        if (!$clinic || !$patient || !$contract || (int) $contract['patient_id'] !== (int) $hint['patient_id']
            || (int) $contract['examination_id'] !== (int) $hint['examination_id']
            || ($examId !== null && (int) $contract['examination_id'] !== $examId)) {
            throw new \RuntimeException('SIGN_ERR_PATIENT_MISMATCH: Parent ownership changed.');
        }
        $creator = $this->row('SELECT first_name, last_name FROM users WHERE id=?', [$contract['created_by']]);
        $contract['creator_first_name'] = $creator['first_name'] ?? null;
        $contract['creator_last_name'] = $creator['last_name'] ?? null;
        $items = $this->rows('SELECT * FROM treatment_contract_items WHERE clinic_id=? AND contract_id=? AND deleted_at IS NULL ORDER BY id FOR UPDATE', [$clinicId, $contractId]);
        $snapshots = $this->rows('SELECT * FROM treatment_contract_odontogram_snapshots WHERE clinic_id=? AND contract_id=? ORDER BY id FOR UPDATE', [$clinicId, $contractId]);
        if (count($snapshots) !== 1) { throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Exactly one frozen snapshot required.'); }
        return ['clinic' => $clinic, 'patient' => $patient, 'examination' => $exam, 'contract' => $contract,
            'items' => $items, 'snapshot' => $snapshots[0] ?? null,
            'signatures' => $this->rows('SELECT * FROM treatment_contract_signatures WHERE clinic_id=? AND contract_id=? ORDER BY id FOR UPDATE', [$clinicId, $contractId]),
            'attachments' => $this->rows("SELECT * FROM file_attachments WHERE clinic_id=? AND entity_type='contract' AND entity_id=? AND purpose='contract_pdf' ORDER BY id FOR UPDATE", [$clinicId, $contractId])];
    }

    private function assertDraft(array $capture): void
    {
        $c = $capture['contract'];
        if ($c['status'] !== 'draft') { throw new \RuntimeException('SIGN_ERR_NOT_DRAFT: Draft required.'); }
        if ($capture['signatures'] || $capture['attachments'] || !empty($c['signed_attachment_id'])
            || !empty($c['signature_hash']) || !empty($c['signature_meta_json']) || !empty($c['signed_at'])) {
            throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Existing consent evidence requires review.');
        }
    }

    private function fingerprint(array $capture): string
    {
        return hash('sha256', json_encode($capture, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Stable consent content, independent of signing links, audit timestamps and directory labels.
     * The caller supplies active ordered items and the exact stored frozen snapshot.
     */
    public function immutableConsentFingerprint(array $contract, array $items, ?array $snapshot): string
    {
        foreach (['status', 'signed_at', 'signature_hash', 'signature_meta_json', 'signed_attachment_id',
            'created_at', 'updated_at', 'deleted_at', 'creator_first_name', 'creator_last_name'] as $field) {
            unset($contract[$field]);
        }
        $stripTimestamps = static function (array $row): array {
            unset($row['created_at'], $row['updated_at'], $row['deleted_at']);
            ksort($row, SORT_STRING);
            return $row;
        };
        ksort($contract, SORT_STRING);
        usort($items, static fn(array $a, array $b): int => (int) $a['id'] <=> (int) $b['id']);
        $items = array_map($stripTimestamps, $items);
        $snapshot = $snapshot !== null ? $stripTimestamps($snapshot) : null;
        return hash('sha256', json_encode(['contract' => $contract, 'items' => $items, 'snapshot' => $snapshot],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Run only after releasing database locks; verify legacy MD5 or new SHA-256 evidence. */
    private function existing(array $capture, string $name, string $signature): array
    {
        $c = $capture['contract'];
        $meta = json_decode($c['signature_meta_json'] ?? '', true);
        if ($c['status'] !== 'signed' || empty($c['signature_hash']) || empty($c['signed_at'])
            || !is_array($meta) || count($capture['signatures']) !== 1) {
            throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Incomplete signed consent.');
        }
        $s = $capture['signatures'][0];
        if ($s['signer_name'] !== $name || !hash_equals($s['signature_data'], $signature)) {
            throw new \RuntimeException('SIGN_ERR_CONFLICT: Different consent input.');
        }
        if ((int) ($meta['signature_id'] ?? 0) !== (int) $s['id']
            || ($meta['signer_name'] ?? null) !== $name || $s['signed_at'] !== $c['signed_at']) {
            throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Signature metadata mismatch.');
        }
        $validHash = false;
        if (!empty($meta['operation'])) {
            $currentConsent = $this->immutableConsentFingerprint($c, $capture['items'], $capture['snapshot']);
            if (!hash_equals((string) ($meta['consent_fingerprint'] ?? ''), $currentConsent)) {
                throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Immutable consent changed.');
            }
            $expected = hash('sha256', $currentConsent . "\n" . $name . "\n" . $signature);
            $validHash = hash_equals($c['signature_hash'], $expected);
        } else {
            // Historical hash covers notes/items/snapshot only, not all terms; never upgrades that guarantee.
            // Historical staff hashed decoded SVG; historical kiosk hashed the stored snapshot.
            $decoded = $capture['snapshot'];
            if ($decoded) { $decoded['svg_snapshot'] = (new OdontogramSvgSnapshotService())->decode($decoded['svg_snapshot'] ?? null); }
            foreach ([$capture['snapshot'], $decoded] as $snapshot) {
                foreach ([(int) $c['id'], (string) $c['id']] as $id) {
                    $expected = hash('sha256', json_encode(['contract' => $id, 'signature' => $signature,
                        'name' => $name, 'snapshot' => $snapshot, 'items' => $capture['items'], 'notes' => $c['notes'] ?? null]));
                    $validHash = $validHash || hash_equals($c['signature_hash'], $expected);
                }
            }
        }
        if (!$validHash) { throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Consent hash mismatch.'); }
        $a = null;
        foreach ($capture['attachments'] as $candidate) {
            if ((int) $candidate['id'] === (int) $c['signed_attachment_id'] && empty($candidate['deleted_at'])) { $a = $candidate; }
        }
        if (!$a || $a['mime_type'] !== 'text/html' || !$this->verifyAttachment($a)) {
            throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Missing or invalid signed artifact.');
        }
        if (isset($meta['artifact_sha256']) && !hash_equals($meta['artifact_sha256'], $a['file_hash'])) {
            throw new \RuntimeException('SIGN_ERR_PARTIAL_STATE: Artifact metadata mismatch.');
        }
        return ['contract' => $c, 'signature' => $s, 'attachment' => $a];
    }

    public function verifyAttachment(array $a): bool
    {
        $relative = str_replace('\\', '/', (string) $a['file_path']);
        if (!preg_match('#^clinic_' . (int) $a['clinic_id'] . '/[^/]+\.html$#D', $relative)
            || str_contains($relative, '..')) { return false; }
        $writeRoot = realpath(WRITEPATH);
        $rawBase = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
        $base = realpath($rawBase);
        if (!$writeRoot || !$base || !$this->samePath($base, $writeRoot . DIRECTORY_SEPARATOR . 'uploads')) { return false; }
        $ancestor = $rawBase;
        while (true) {
            if (is_link($ancestor)) { return false; }
            $parent = dirname($ancestor);
            if ($parent === $ancestor) { break; }
            $ancestor = $parent;
        }
        $path = WRITEPATH . 'uploads/' . $relative;
        $real = realpath($path);
        if (!$base || !$real || is_link($path) || is_link(dirname($path)) || !is_file($real) || !is_readable($real)
            || strncmp($real, $base . DIRECTORY_SEPARATOR, strlen($base . DIRECTORY_SEPARATOR)) !== 0
            || filesize($real) !== (int) $a['file_size']) { return false; }
        $hash = (string) $a['file_hash'];
        $algorithm = strlen($hash) === 64 ? 'sha256' : (strlen($hash) === 32 ? 'md5' : null);
        return $algorithm !== null && hash_equals($hash, hash_file($algorithm, $real));
    }

    private function samePath(string $a, string $b): bool
    {
        return PHP_OS_FAMILY === 'Windows' ? strcasecmp($a, $b) === 0 : $a === $b;
    }

    public function updateDraftWithLock(int $clinicId, int $contractId, callable $mutateFn): void
    {
        $this->mutateDraft($clinicId, $contractId, $mutateFn, 'contract_update');
    }

    public function deleteDraftWithLock(int $clinicId, int $contractId, callable $deleteFn): void
    {
        $this->mutateDraft($clinicId, $contractId, $deleteFn, 'contract_delete');
    }

    private function mutateDraft(int $clinicId, int $contractId, callable $callback, string $action): void
    {
        $this->transaction(function () use ($clinicId, $contractId, $callback, $action) {
            $capture = $this->capture($clinicId, $contractId, null, null);
            $this->assertDraft($capture);
            if ($callback($this->db, $capture['contract']) !== true) {
                throw new \RuntimeException('SIGN_ERR_WRITE: Draft callback must explicitly confirm all writes.');
            }
            $this->writeAudit($clinicId, (int) session()->get('user_id'), $action,
                (int) $capture['contract']['patient_id'], $contractId, []);
        });
    }

    private function transaction(callable $work)
    {
        if ((int) $this->db->transDepth !== 0 || !$this->db->transStatus() || !$this->db->transBegin()) {
            throw new \RuntimeException('SIGN_ERR_TRANSACTION: Healthy exclusively owned transaction required.');
        }
        try {
            $result = $work();
            $this->phase('commit');
            if ((int) $this->db->transDepth !== 1 || !$this->db->transStatus() || !$this->db->transCommit()) {
                throw new \RuntimeException('SIGN_ERR_TRANSACTION: Commit failed; reconciliation may be required.');
            }
            return $result;
        } catch (\Throwable $e) {
            if ((int) $this->db->transDepth === 1 && !$this->db->transRollback()) {
                throw new \RuntimeException('SIGN_ERR_TRANSACTION: Rollback failed; reconciliation required.', 0, $e);
            }
            throw $e;
        }
    }

    private function writeAudit(int $clinicId, int $userId, string $action, int $patientId, int $contractId, array $meta): void
    {
        $this->phase('audit');
        if ($this->auditFn !== null && ($this->auditFn)($userId, $action, 'patient', $patientId, 'Treatment contract #' . $contractId) !== true) {
            throw new \RuntimeException('SIGN_ERR_AUDIT: Audit hook rejected write.');
        }
        $this->checked($this->audit->insert(['clinic_id' => $clinicId, 'user_id' => $userId,
            'action' => $action, 'entity_type' => 'patient', 'entity_id' => $patientId,
            'description' => 'Treatment contract #' . $contractId . ' ' . $action,
            'metadata' => json_encode($meta, JSON_THROW_ON_ERROR)]), 'audit');
    }

    private function validateInput(string $name, string $signature): void
    {
        if (trim($name) === '' || strlen($name) > 255 || !preg_match('//u', $name)
            || preg_match('/[\x00-\x1f\x7f]/u', $name) || strlen($signature) > 1400000
            || !preg_match('#^data:image/png;base64,([A-Za-z0-9+/]+={0,2})$#D', $signature, $m)) {
            throw new \RuntimeException('SIGN_ERR_MISSING_INPUT: Invalid signer or PNG signature.');
        }
        $bytes = base64_decode($m[1], true);
        $size = $bytes !== false ? @getimagesizefromstring($bytes) : false;
        if ($bytes === false || base64_encode($bytes) !== $m[1] || strlen($bytes) < 64 || strlen($bytes) > 1048576
            || substr($bytes, 0, 8) !== "\x89PNG\r\n\x1a\n" || !$size || $size[2] !== IMAGETYPE_PNG
            || $size[0] < 1 || $size[1] < 1 || $size[0] > 4096 || $size[1] > 4096 || $size[0] * $size[1] > 4194304) {
            throw new \RuntimeException('SIGN_ERR_MISSING_INPUT: Invalid PNG signature.');
        }
    }

    private function rows(string $sql, array $args): array
    {
        $query = $this->db->query($sql, $args);
        if (!$query) { throw new \RuntimeException('SIGN_ERR_QUERY: Database read failed.'); }
        return $query->getResultArray();
    }
    private function row(string $sql, array $args): ?array { return $this->rows($sql, $args)[0] ?? null; }
    private function checked($ok, string $step): void
    {
        if (!$ok || !$this->db->transStatus()) { throw new \RuntimeException('SIGN_ERR_WRITE: Failed ' . $step . '.'); }
    }
    private function phase(string $phase): void { if ($this->phaseFn !== null) { ($this->phaseFn)($phase); } }
}