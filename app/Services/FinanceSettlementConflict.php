<?php

namespace App\Services;
final class FinanceSettlementConflict extends \DomainException  {
    public function __construct(public readonly string $reason,public readonly int $httpStatus=409) {
        parent::__construct($reason);
    }
}
