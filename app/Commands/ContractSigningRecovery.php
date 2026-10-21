<?php

namespace App\Commands;

use App\Services\ContractDocumentJournal;
use App\Services\ContractSigningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Recovery never changes consent rows or removes artifacts. */
class ContractSigningRecovery extends BaseCommand
{
    protected $group = 'Contracts';
    protected $name = 'contracts:signing-recovery';
    protected $description = 'Report private signing journals; optionally reconcile verified committed journals only.';
    protected $usage = 'contracts:signing-recovery [--reconcile]';
    protected $options = ['--reconcile' => 'Mark positively verified complete database operations committed in their journal only.'];

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $journal = new ContractDocumentJournal();
        $signing = new ContractSigningService($db, $journal);
        $reconcile = CLI::getOption('reconcile') !== null;
        foreach ($journal->entries() as $artifact) {
            $operation = (string) ($artifact['operation'] ?? 'invalid');
            try {
                $clinicId = (int) ($artifact['clinic_id'] ?? 0);
                $contractId = (int) ($artifact['contract_id'] ?? 0);
                if (!preg_match('/^[a-f0-9]{32}$/D', $operation) || $clinicId < 1 || $contractId < 1) {
                    throw new \RuntimeException('ambiguous ownership');
                }
                $c = $db->query('SELECT * FROM treatment_contracts WHERE clinic_id=? AND id=?', [$clinicId, $contractId])->getRowArray();
                $clinic = $db->query('SELECT id FROM clinics WHERE id=?', [$clinicId])->getRowArray();
                $patient = $c ? $db->query('SELECT id FROM patients WHERE clinic_id=? AND id=?', [$clinicId, $c['patient_id']])->getRowArray() : null;
                $exam = !$c || empty($c['examination_id']) ? true : (bool) $db->query('SELECT id FROM examinations WHERE clinic_id=? AND patient_id=? AND id=?', [$clinicId, $c['patient_id'], $c['examination_id']])->getRowArray();
                $parentsValid = $clinic && $patient && $exam;
                $attachments = $db->query('SELECT * FROM file_attachments WHERE file_path=?', [$artifact['relative_path']])->getResultArray();
                if (!$journal->verify($artifact)) {
                    $state = 'missing-or-invalid-artifact';
                } elseif (!$attachments) {
                    $state = ($c && $c['status'] === 'signed') ? 'ambiguous-unreferenced' : 'unreferenced';
                } else {
                    $meta = json_decode($c['signature_meta_json'] ?? '', true);
                    $a = count($attachments) === 1 ? $attachments[0] : null;
                    $signatures = $db->query('SELECT * FROM treatment_contract_signatures WHERE clinic_id=? AND contract_id=?', [$clinicId, $contractId])->getResultArray();
                    $s = count($signatures) === 1 ? $signatures[0] : null;
                    $items = $db->query('SELECT * FROM treatment_contract_items WHERE clinic_id=? AND contract_id=? AND deleted_at IS NULL ORDER BY id', [$clinicId, $contractId])->getResultArray();
                    $snapshots = $db->query('SELECT * FROM treatment_contract_odontogram_snapshots WHERE clinic_id=? AND contract_id=? ORDER BY id', [$clinicId, $contractId])->getResultArray();
                    $consentFingerprint = $c && count($snapshots) === 1 ? $signing->immutableConsentFingerprint($c, $items, $snapshots[0]) : '';
                    $expected = $s && is_array($meta) ? hash('sha256', $consentFingerprint . "\n" . $s['signer_name'] . "\n" . $s['signature_data']) : '';
                    $complete = $parentsValid && $c && $c['status'] === 'signed' && empty($c['deleted_at']) && !empty($c['signed_at'])
                        && $a && $s && empty($a['deleted_at']) && (int) $a['clinic_id'] === $clinicId
                        && $a['entity_type'] === 'contract' && (int) $a['entity_id'] === $contractId
                        && $a['purpose'] === 'contract_pdf' && $a['mime_type'] === 'text/html'
                        && (int) $c['signed_attachment_id'] === (int) $a['id']
                        && ($meta['operation'] ?? null) === $operation
                        && (int) ($meta['signature_id'] ?? 0) === (int) $s['id']
                        && ($meta['signer_name'] ?? null) === $s['signer_name']
                        && $s['signed_at'] === $c['signed_at']
                        && $consentFingerprint !== ''
                        && hash_equals((string) ($meta['consent_fingerprint'] ?? ''), $consentFingerprint)
                        && hash_equals((string) ($artifact['metadata']['consent_fingerprint'] ?? ''), $consentFingerprint)
                        && ($meta['source_fingerprint'] ?? null) === ($artifact['metadata']['source_fingerprint'] ?? null)
                        && hash_equals((string) $artifact['hash'], (string) $a['file_hash'])
                        && hash_equals((string) $artifact['hash'], (string) ($meta['artifact_sha256'] ?? ''))
                        && hash_equals((string) $c['signature_hash'], $expected)
                        && $signing->verifyAttachment($a);
                    if ($complete) {
                        $auditRows = $db->query("SELECT metadata FROM activity_logs WHERE clinic_id=? AND action='contract_sign' AND entity_type='patient' AND entity_id=?", [$clinicId, $c['patient_id']])->getResultArray();
                        $auditMatched = false;
                        foreach ($auditRows as $auditRow) {
                            $auditMeta = json_decode($auditRow['metadata'] ?? '', true);
                            if (($auditMeta['operation'] ?? null) === $operation
                                && (int) ($auditMeta['signature_id'] ?? 0) === (int) $s['id']) { $auditMatched = true; }
                        }
                        $complete = $auditMatched;
                    }
                    $state = $complete ? 'committed' : 'partial-or-ambiguous';
                    if ($complete && $reconcile) {
                        $journal->markCommitted($artifact, (int) $s['id'], (int) $a['id']);
                        $state = 'committed-journal-reconciled';
                    }
                }
                CLI::write($operation . ' clinic=' . $clinicId . ' contract=' . $contractId . ' ' . $state);
            } catch (\Throwable $e) {
                CLI::error($operation . ' ambiguous: ' . $e->getMessage());
            }
        }
        CLI::write('Unreferenced artifacts are retained privately. Review against audit and backups before any manual retention action; this command never deletes files or rewrites consent.');
    }
}