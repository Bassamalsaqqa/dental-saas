<?php

namespace App\Services;

use App\Models\InventoryUsageModel;
use Config\Database;

class InventoryUsageService
{
    private InventoryUsageModel $usageModel;
    private InventoryMovementService $movementService;
    private FinanceLedgerService $financeLedgerService;
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->usageModel = new InventoryUsageModel();
        $this->movementService = new InventoryMovementService();
        $this->financeLedgerService = new FinanceLedgerService();
        $this->db = Database::connect();
    }

    public function record(int $clinicId, array $payload, ?int $actorUserId, array $options = []): array
    {
        try {
            $date = $payload['usage_date'] ?? null;
            $parsed = is_string($date) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
            if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < '1000-01-01') {
                throw new \DomainException('Usage date must be a valid accounting date.');
            }
            $notes = trim((string) ($payload['notes'] ?? ''));
            $treatmentId = ($payload['treatment_id'] ?? null) !== null && ($payload['treatment_id'] ?? '') !== ''
                ? InventoryStockService::integer($payload['treatment_id']) : null;
            $items = $this->normalizeItems($payload['items'] ?? []);
            $stock = new InventoryStockService();
            return $stock->transaction($clinicId, function () use ($clinicId,$date,$notes,$treatmentId,$items,$actorUserId,$options,$stock) {
                $patientId = null;
                $patientName = null;
                $treatmentLabel = null;
                if ($treatmentId !== null) {
                    // Clinic is locked before discovery; then lock canonical parents before treatment.
                    $treatment = $this->db->table('treatments')->where('clinic_id',$clinicId)->where('id',$treatmentId)->get()->getRowArray();
                    if (!$treatment) {
                        throw new \DomainException('Treatment not found in this clinic.');
                    }
                    $patientId = InventoryStockService::integer($treatment['patient_id']);
                    $examId = !empty($treatment['examination_id']) ? InventoryStockService::integer($treatment['examination_id']) : null;
                    (new FinanceCreationGuard($this->db))->lockParents($clinicId,$patientId,$examId);
                    $query = $this->db->query('SELECT id, patient_id, examination_id, treatment_id FROM '.$this->db->escapeIdentifiers($this->db->prefixTable('treatments')).' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT',[$clinicId,$treatmentId]);
                    if ($query === false) {
                        throw new \DomainException('Treatment is busy. Refresh and retry.');
                    }
                    $locked = $query->getRowArray();
                    if (!$locked || (int)$locked['patient_id'] !== $patientId || ((int)($locked['examination_id']??0)) !== (int)($examId??0)) {
                        throw new \DomainException('Treatment changed. Refresh and retry.');
                    }
                    $treatmentLabel = (string) ($locked['treatment_id'] ?: '#'.$treatmentId);
                    $patient = $this->db->table('patients')->select('first_name,last_name')->where('clinic_id',$clinicId)->where('id',$patientId)->get()->getRowArray();
                    $patientName = trim(($patient['first_name']??'').' '.($patient['last_name']??''));
                }
                $rows = [];
                $usageData = [];
                $totalCost = 0.0;
                foreach ($items as $id=>$quantity) {
                    $row = $stock->lockRow($clinicId,$id);
                    if ((int)$row['quantity'] < $quantity) {
                        throw new \DomainException('Insufficient stock for '. $row['item_name'].'.');
                    }
                    $cost = $row['unit_price'] ?? null;
                    if (!is_numeric($cost) || !is_finite((float)$cost) || (float)$cost < 0 || (float)$cost > 99999999.99) {
                        throw new \DomainException('Inventory unit cost is invalid; correct the item before recording usage.');
                    }
                    $rows[$id] = $row;
                    $lineCost = round((float)$cost*$quantity,2);
                    if (!is_finite($lineCost) || $lineCost > 99999999.99 || $totalCost+$lineCost > 99999999.99) {
                        throw new \DomainException('Inventory usage cost exceeds the supported accounting range.');
                    }
                    $usageData[] = ['item_id'=>$id,'item_name'=>(string)$row['item_name'],'quantity_used'=>$quantity,'unit_cost'=>(float)$row['unit_price'],'total_cost'=>$lineCost];
                    $totalCost += $lineCost;
                }
                $totalCost = round($totalCost,2);
                $usage = ['clinic_id'=>$clinicId,'treatment_id'=>$treatmentId,'usage_date'=>$date,'items_used'=>json_encode($usageData,JSON_THROW_ON_ERROR),'total_cost'=>$totalCost,'notes'=>$notes!==''?$notes:null,'recorded_by'=>$actorUserId?:0,'created_at'=>date('Y-m-d H:i:s')];
                if (!$this->usageModel->insert($usage)) {
                    throw new \RuntimeException('Failed to record inventory usage: '.implode(', ',$this->usageModel->errors()));
                }
                $usageId = (int)$this->usageModel->getInsertID();
                foreach ($usageData as $line) {
                    $row = $rows[$line['item_id']];
                    $before = (int)$row['quantity'];
                    $after = $before-$line['quantity_used'];
                    $stock->writeQuantity($clinicId,$row,$after);
                    $this->movementService->record($clinicId,$line['item_id'],'usage_consume',-$line['quantity_used'],$before,$after,$line['unit_cost'],'inventory_usage',$usageId,trim((string)($options['movement_note']??'Inventory usage recorded')),$actorUserId);
                }
                if ((int)settings()->get('finance_auto_post_inventory_cogs',1) === 1) {
                    // FinanceLedgerService joins this transaction. A failure cannot leave stock committed.
                    $this->financeLedgerService->upsertInventoryUsageCogsExpense($clinicId,$usageId,$usage,$patientId,$actorUserId,['patient_name'=>$patientName,'treatment_label'=>$treatmentLabel]);
                }
                return ['success'=>true,'usage_id'=>$usageId,'usage_data'=>$usageData,'total_cost'=>$totalCost,'patient_name'=>$patientName,'treatment_label'=>$treatmentLabel,'message'=>'Inventory usage recorded.'];
            });
        } catch (\Throwable $e) {
            // The transaction wrapper rolls back only its own transaction; Treatment owns its rollback.
            log_message('error','Inventory usage failed: '.$e->getMessage());
            return ['success'=>false,'message'=>InventoryStockService::failureMessage($e)];
        }
    }

    private function normalizeItems($items): array
    {
        if (!is_array($items) || $items === []) {
            throw new \DomainException('Please select at least one inventory item.');
        }
        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new \DomainException('Invalid inventory item.');
            }
            $id = InventoryStockService::integer($item['item_id']??null);
            $quantity = InventoryStockService::integer($item['quantity']??null);
            $sum = ($normalized[$id]??0)+$quantity;
            if ($sum > 2147483647) {
                throw new \DomainException('Inventory quantity exceeds the supported range.');
            }
            $normalized[$id] = $sum;
        }
        ksort($normalized,SORT_NUMERIC);
        return $normalized;
    }
}
