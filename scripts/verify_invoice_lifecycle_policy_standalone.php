<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Services/FinanceApiMutationService.php';
require_once __DIR__ . '/../app/Services/InvoiceLifecyclePolicy.php';

use App\Services\InvoiceLifecyclePolicy;

echo "Running focused standalone no-DB InvoiceLifecyclePolicy verification...\n";

$assertions = 0;
$assert = function (bool $condition, string $desc) use (&$assertions): void {
    if (!$condition) {
        throw new \RuntimeException("Assertion failed: " . $desc);
    }
    $assertions++;
    echo "  [PASS] {$desc}\n";
};

// 1. Invoice detection
$assert(InvoiceLifecyclePolicy::isInvoice('invoice'), 'isInvoice string');
$assert(InvoiceLifecyclePolicy::isInvoice(['transaction_type' => 'invoice']), 'isInvoice array');
$assert(!InvoiceLifecyclePolicy::isInvoice('expense'), 'isInvoice expense false');

// 2. No server positive proof
$assert(!InvoiceLifecyclePolicy::hasServerPositiveUnissuedProof(['transaction_type' => 'invoice', 'payment_status' => 'pending']),
    'Pending invoice has no server positive unissued proof');
$assert(!InvoiceLifecyclePolicy::hasServerPositiveUnissuedProof(['transaction_type' => 'invoice', 'payment_status' => 'overdue']),
    'Overdue invoice has no server positive unissued proof');

// 3. Physical deletion assertions
try {
    InvoiceLifecyclePolicy::assertCanPhysicallyDelete(['transaction_type' => 'invoice', 'payment_status' => 'pending']);
    throw new \RuntimeException('Should have thrown DomainException for invoice');
} catch (\DomainException $e) {
    $assert(str_contains($e->getMessage(), 'physical deletion is blocked'), 'Pending invoice physical deletion blocked with controlled message');
}

try {
    InvoiceLifecyclePolicy::assertCanPhysicallyDelete(['transaction_type' => 'invoice', 'payment_status' => 'overdue']);
    throw new \RuntimeException('Should have thrown DomainException for overdue invoice');
} catch (\DomainException $e) {
    $assert(str_contains($e->getMessage(), 'physical deletion is blocked'), 'Overdue invoice physical deletion blocked with controlled message');
}

// 4. Expense physical deletion
InvoiceLifecyclePolicy::assertCanPhysicallyDelete(['transaction_type' => 'expense', 'payment_status' => 'pending']);
$assert(true, 'Pending expense deletion allowed by policy');

try {
    InvoiceLifecyclePolicy::assertCanPhysicallyDelete(['transaction_type' => 'expense', 'payment_status' => 'paid']);
    throw new \RuntimeException('Should have thrown DomainException for paid expense');
} catch (\DomainException $e) {
    $assert(str_contains($e->getMessage(), 'payment status and cannot be deleted'), 'Paid expense physical deletion blocked');
}

// 5. Basis immutability on invoices
$invoice = [
    'id' => 10,
    'clinic_id' => 1,
    'transaction_id' => 'INV-001',
    'transaction_type' => 'invoice',
    'patient_id' => 5,
    'examination_id' => 2,
    'amount' => 100.0,
    'discount_amount' => 10.0,
    'tax_percentage' => 10.0,
    'currency' => 'ILS',
    'exchange_rate' => 1.0,
    'description' => 'Dental cleaning',
    'service_type' => 'treatment',
    'service_details' => 'Scaling',
    'due_date' => '2026-11-01',
    'transaction_date' => '2026-10-01',
    'payment_method' => 'cash',
    'liquidity_account' => 'safe_cash',
    'payment_status' => 'pending',
    'notes' => 'Old note',
];

$basisFields = [
    'amount', 'discount_amount', 'tax_percentage', 'currency', 'exchange_rate',
    'patient_id', 'examination_id', 'description', 'service_type', 'service_details',
    'due_date', 'transaction_date', 'payment_method', 'liquidity_account',
];

foreach ($basisFields as $field) {
    try {
        InvoiceLifecyclePolicy::assertCanUpdate($invoice, [$field]);
        throw new \RuntimeException("Should have rejected {$field}");
    } catch (DomainException $e) {
        $assert(str_contains($e->getMessage(), 'cannot be changed on a persisted invoice'), "Invoice protected from mutating: {$field}");
    }
}

// 6. Safe metadata (notes) allowed
InvoiceLifecyclePolicy::assertCanUpdate($invoice, ['internal_note']);
$assert(true, 'Audit-only internal note allowed on invoice');

// 7. Equivalence check
$assert(InvoiceLifecyclePolicy::sameValue('amount', '100.00', 100), 'Float/numeric string equivalence');
$assert(InvoiceLifecyclePolicy::sameValue('exchange_rate', '3.500000', 3.5), 'FX rate precision equivalence');
$assert(InvoiceLifecyclePolicy::sameValue('notes', '', null), 'Empty notes equivalence');
$assert(!InvoiceLifecyclePolicy::sameValue('amount', 100, 105), 'Different amount detected');
$assert(!InvoiceLifecyclePolicy::sameValue('notes', 'a', 'b'), 'Different notes detected');

$assert(InvoiceLifecyclePolicy::validateInternalNote('Audit addendum') === 'Audit addendum', 'Internal note plain text validated');
foreach (['', "\x00bad", str_repeat('x', 5001)] as $badNote) {
    try { InvoiceLifecyclePolicy::validateInternalNote($badNote); throw new RuntimeException('Invalid internal note accepted'); }
    catch (InvalidArgumentException $e) { $assert(true, 'Invalid internal note rejected'); }
}
try { InvoiceLifecyclePolicy::assertCanUpdate(['transaction_type'=>'invoice'], ['notes']); throw new RuntimeException('Printed notes update accepted'); }
catch (DomainException $e) { $assert(true, 'Printed invoice notes remain protected'); }

echo "\nAll {$assertions} standalone assertions PASSED successfully!\n";
