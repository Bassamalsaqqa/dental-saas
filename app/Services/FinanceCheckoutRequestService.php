<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Persistent replay protection inside the invoice creator's single owned transaction. */
final class FinanceCheckoutRequestService
{
    public function __construct(private BaseConnection $db) {}

    private function requireTransaction(): void
    {
        if ((int) $this->db->transDepth !== 1 || $this->db->transStatus() === false) {
            throw new \DomainException('Checkout requires one healthy owner transaction.');
        }
    }

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function canonical(array $payload): array
    {
        if (!array_is_list($payload)) { ksort($payload, SORT_STRING); }
        foreach ($payload as &$value) {
            if (is_array($value)) { $value = $this->canonical($value); }
            elseif (is_object($value) || is_resource($value)) { throw new \DomainException('Unsupported checkout request value.'); }
        }
        unset($value);
        return $payload;
    }

    /**
     * Canonical payload must include all submitted invoice/receipt/provider/charge terms.
     * It must not include CSRF tokens or return-navigation state. No transaction is started here.
     * @return array{request_id:int,idempotent:bool,finance_id:?int,payment_id:?int}
     */
    public function claim(int $clinicId, int $actorId, string $key, array $canonicalPayload, int $patientId, ?int $examinationId = null): array
    {
        $this->requireTransaction();
        if ($actorId <= 0 || !preg_match('/^[A-Za-z0-9_-]{24,128}$/D', $key)) {
            throw new \DomainException('Invalid checkout request identity.');
        }
        $json = json_encode($this->canonical($canonicalPayload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if (strlen($json) > 131072) { throw new \DomainException('Checkout request terms exceed the supported size.'); }
        $hash = hash('sha256', $json);
        (new FinanceCreationGuard($this->db))->lockParents($clinicId, $patientId, $examinationId, true);
        $result = $this->db->query('SELECT * FROM ' . $this->table('finance_checkout_requests') . ' WHERE clinic_id = ? AND request_key = ? FOR UPDATE', [$clinicId, $key]);
        if ($result === false) { throw new \RuntimeException('Unable to lock checkout request.'); }
        $row = $result->getRowArray();
        if ($row) {
            if ((int) $row['created_by'] !== $actorId || !hash_equals((string) $row['request_hash'], $hash) || (int) $row['patient_id'] !== $patientId || (int) ($row['examination_id'] ?? 0) !== (int) $examinationId) {
                throw new \DomainException('Checkout request identity was already used for different terms.');
            }
            if (empty($row['completed_at']) || empty($row['finance_id']) || empty($row['payment_id'])) {
                throw new \DomainException('Incomplete checkout request requires operator investigation.');
            }
            $this->verifyLinks($row, (int) $row['finance_id'], (int) $row['payment_id']);
            return ['request_id' => (int) $row['id'], 'idempotent' => true, 'finance_id' => (int) $row['finance_id'], 'payment_id' => (int) $row['payment_id']];
        }
        if (!$this->db->table('finance_checkout_requests')->insert([
            'clinic_id' => $clinicId, 'patient_id' => $patientId, 'examination_id' => $examinationId,
            'request_key' => $key, 'request_hash' => $hash, 'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ])) { throw new \RuntimeException('Unable to claim checkout request.'); }
        $id = (int) $this->db->insertID();
        if ($id <= 0 || $this->db->affectedRows() !== 1) { throw new \RuntimeException('Unable to identify checkout request.'); }
        return ['request_id' => $id, 'idempotent' => false, 'finance_id' => null, 'payment_id' => null];
    }

    private function verifyLinks(array $request, int $financeId, int $paymentId): void
    {
        if ($financeId <= 0 || $paymentId <= 0) { throw new \DomainException('Invalid checkout result identity.'); }
        $invoice = $this->db->query('SELECT id, clinic_id, patient_id, examination_id, transaction_type FROM ' . $this->table('finances') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT', [(int) $request['clinic_id'], $financeId]);
        if ($invoice === false) { throw new \RuntimeException('Unable to verify checkout invoice.'); }
        $invoice = $invoice->getRowArray();
        if (!$invoice || $invoice['transaction_type'] !== 'invoice' || (int) $invoice['patient_id'] !== (int) $request['patient_id'] || (!empty($request['examination_id']) && (int) ($invoice['examination_id'] ?? 0) !== (int) $request['examination_id'])) {
            throw new \DomainException('Checkout invoice does not match its protected identity.');
        }
        // Clinical charge selection may derive an examination from the selected lines
        // when the form did not explicitly choose one. Validate that actual identity
        // under the existing NOWAIT parent contract without changing request terms.
        if (!empty($invoice['examination_id'])) {
            (new FinanceCreationGuard($this->db))->lockParents((int) $request['clinic_id'], (int) $request['patient_id'], (int) $invoice['examination_id'], true);
        }
        $payment = $this->db->query('SELECT id, finance_id, type FROM ' . $this->table('finance_payments') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT', [(int) $request['clinic_id'], $paymentId]);
        if ($payment === false) { throw new \RuntimeException('Unable to verify checkout receipt.'); }
        $payment = $payment->getRowArray();
        if (!$payment || (int) $payment['finance_id'] !== $financeId || $payment['type'] !== 'payment') {
            throw new \DomainException('Checkout receipt does not match its protected invoice.');
        }
    }

    public function finalize(int $clinicId, int $requestId, int $financeId, int $paymentId): void
    {
        $this->requireTransaction();
        // Read immutable parent identity first, then acquire the canonical parent locks
        // before the request lock even when called independently of claim().
        $identity = $this->db->query('SELECT patient_id, examination_id FROM ' . $this->table('finance_checkout_requests') . ' WHERE clinic_id = ? AND id = ?', [$clinicId, $requestId]);
        if ($identity === false || !($identity = $identity->getRowArray())) { throw new \DomainException('Checkout request is unavailable in this clinic.'); }
        (new FinanceCreationGuard($this->db))->lockParents($clinicId, (int) $identity['patient_id'], !empty($identity['examination_id']) ? (int) $identity['examination_id'] : null, true);
        $result = $this->db->query('SELECT * FROM ' . $this->table('finance_checkout_requests') . ' WHERE clinic_id = ? AND id = ? FOR UPDATE', [$clinicId, $requestId]);
        if ($result === false) { throw new \RuntimeException('Unable to lock checkout result.'); }
        $row = $result->getRowArray();
        if (!$row) { throw new \DomainException('Checkout request is unavailable in this clinic.'); }
        $this->verifyLinks($row, $financeId, $paymentId);
        if (!empty($row['completed_at'])) {
            if ((int) $row['finance_id'] !== $financeId || (int) $row['payment_id'] !== $paymentId) {
                throw new \DomainException('Completed checkout identity is immutable.');
            }
            return;
        }
        if (!$this->db->table('finance_checkout_requests')->where('clinic_id', $clinicId)->where('id', $requestId)->where('completed_at', null)->update([
            'finance_id' => $financeId, 'payment_id' => $paymentId, 'completed_at' => date('Y-m-d H:i:s'),
        ]) || $this->db->affectedRows() !== 1) { throw new \RuntimeException('Unable to finalize checkout request.'); }
    }
}
