<?php

namespace App\Services;

/** Pure correction-aware position; never writes invoice or receipt history. */
final class InvoiceCorrectionPosition
{
    /** Inputs are integer minor units, already normalized in a single accounting currency. */
    public static function calculate(int $gross, int $credits, int $cash, int $adjustments, int $outgoing = 0, int $incoming = 0): array
    {
        foreach ([$gross, $credits, $cash, $adjustments, $outgoing, $incoming] as $amount) {
            if ($amount < 0 || $amount > InvoiceCorrectionMoney::MAX_CENTS) {
                throw new \DomainException('Invalid correction position amount.');
            }
        }
        if ($credits > $gross || $outgoing > $cash) {
            throw new \DomainException('Correction or application exceeds the original financial capacity.');
        }
        $charge = $gross - $credits;
        $settled = InvoiceCorrectionMoney::sum($cash, $adjustments, -$outgoing, $incoming);
        $surplus = max(0, $settled - $charge);
        $cashBackedFunding = InvoiceCorrectionMoney::sum($cash, -$outgoing, $incoming);
        $liability = min($cashBackedFunding, $surplus);
        $position = [
            'gross' => $gross,
            'credits' => $credits,
            'corrected_charge' => $charge,
            'cash_net' => $cash,
            'noncash_adjustments' => $adjustments,
            'outgoing_allocations' => $outgoing,
            'incoming_allocations' => $incoming,
            'effective_settlement' => $settled,
            'outstanding' => max(0, $charge - $settled),
            'patient_credit' => $liability,
            'noncash_surplus' => $surplus - $liability,
        ];
        $result = [];
        foreach ($position as $name => $value) {
            $result[$name . '_cents'] = $value;
            $result[$name] = InvoiceCorrectionMoney::decimal($value);
        }
        return $result;
    }
}
