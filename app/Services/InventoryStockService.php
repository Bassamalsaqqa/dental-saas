<?php

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use Config\Database;

/** Shared clinic-first transaction and stock-row contract for inventory writers. */
class InventoryStockService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public static function integer($value, bool $allowZero = false): int
    {
        if ((!is_int($value) && !is_string($value))
            || !preg_match($allowZero ? '/^(0|[1-9][0-9]*)$/D' : '/^[1-9][0-9]*$/D', (string) $value)
            || strlen((string) $value) > 10 || (float) $value > 2147483647) {
            throw new \DomainException('Inventory identity and quantity must be valid whole numbers.');
        }
        return (int) $value;
    }

    public static function failureMessage(\Throwable $error): string
    {
        log_message('error', 'Inventory operation failed: '.$error->getMessage());
        return $error instanceof \DomainException ? $error->getMessage() : lang('Inventory.stockOperationFailed');
    }

    public function transaction(int $clinicId, callable $operation)
    {
        $active = (int) (ApiRuntimeContext::getClinicId() ?: session()->get('active_clinic_id'));
        if ($clinicId <= 0 || $clinicId !== $active) {
            throw new \DomainException('Inventory clinic does not match the active context.');
        }
        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Inventory requires a healthy transaction.');
        }
        $owns = (int) $this->db->transDepth === 0;
        if ($owns && !$this->db->transBegin()) {
            throw new \RuntimeException('Cannot start inventory transaction.');
        }
        try {
            (new FinanceCreationGuard($this->db))->lockParents($clinicId);
            $result = $operation();
            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Inventory transaction failed. Roll back and retry.');
            }
            if ($owns && !$this->db->transCommit()) {
                throw new \RuntimeException('Cannot commit inventory transaction.');
            }
            return $result;
        } catch (\Throwable $e) {
            if ($owns && (int) $this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            throw $e;
        }
    }

    /** Caller holds the clinic lock. NOWAIT avoids child-first legacy writer cycles. */
    public function lockRow(int $clinicId, int $id): array
    {
        if ((int) $this->db->transDepth < 1 || !$this->db->transStatus()) {
            throw new \RuntimeException('Stock locking requires a healthy transaction.');
        }
        $query = $this->db->query('SELECT * FROM '.$this->db->escapeIdentifiers($this->db->prefixTable('inventory')).' WHERE clinic_id = ? AND id = ? FOR UPDATE NOWAIT', [$clinicId, $id]);
        if ($query === false) {
            throw new \DomainException('Inventory is busy. Refresh and retry.');
        }
        $row = $query->getRowArray();
        if (!$row) {
            throw new \DomainException('Inventory item not found in this clinic.');
        }
        return $row;
    }

    public function writeQuantity(int $clinicId, array $row, int $after, array $extra = []): void
    {
        self::integer($after, true);
        $data = array_merge($extra, ['quantity'=>$after, 'updated_at'=>date('Y-m-d H:i:s')]);
        $ok = $this->db->table('inventory')->where('clinic_id', $clinicId)->where('id', $row['id'])->where('quantity', $row['quantity'])->update($data);
        if (!$ok || ($after !== (int) $row['quantity'] && $this->db->affectedRows() !== 1)) {
            throw new \RuntimeException('Inventory changed. Refresh and retry.');
        }
    }

    public function adjust(int $clinicId, $id, $quantity, string $type, string $reason, ?int $actor): int
    {
        $id = self::integer($id);
        $quantity = self::integer($quantity);
        if (!in_array($type, ['add','subtract'], true)) {
            throw new \DomainException('Adjustment type must be add or subtract.');
        }
        return $this->transaction($clinicId, function () use ($clinicId,$id,$quantity,$type,$reason,$actor) {
            $row = $this->lockRow($clinicId,$id);
            $before = (int) $row['quantity'];
            $delta = $type === 'add' ? $quantity : -$quantity;
            $after = $before + $delta;
            if ($after < 0 || $after > 2147483647) {
                throw new \DomainException('Insufficient stock or quantity exceeds the supported range.');
            }
            $this->writeQuantity($clinicId,$row,$after);
            (new InventoryMovementService())->record($clinicId,$id,$type==='add'?'manual_adjust_in':'manual_adjust_out',$delta,$before,$after,(float)$row['unit_price'],'inventory_adjustment',$id,$reason,$actor);
            return $after;
        });
    }
}
