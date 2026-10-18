<?php

namespace Tests\unit;

use DomainException;
use App\Services\InvoiceLifecyclePolicy;
use CodeIgniter\Test\CIUnitTestCase;

final class InvoiceLifecyclePolicyTest extends CIUnitTestCase
{
    public function testPersistedInvoiceCannotBePhysicallyDeleted(): void
    {
        $invoice = [
            'id' => 1,
            'transaction_type' => 'invoice',
            'payment_status' => 'pending',
            'amount' => 100,
            'balance_due' => 100,
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invoice issuance is not proven unissued; physical deletion is blocked. Preserve the invoice and its history.');
        InvoiceLifecyclePolicy::assertCanPhysicallyDelete($invoice);
    }

    public function testOverdueInvoiceCannotBePhysicallyDeleted(): void
    {
        $invoice = [
            'id' => 2,
            'transaction_type' => 'invoice',
            'payment_status' => 'overdue',
            'amount' => 100,
            'balance_due' => 100,
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invoice issuance is not proven unissued; physical deletion is blocked. Preserve the invoice and its history.');
        InvoiceLifecyclePolicy::assertCanPhysicallyDelete($invoice);
    }

    public function testPendingExpenseCanBePhysicallyDeleted(): void
    {
        $expense = [
            'id' => 3,
            'transaction_type' => 'expense',
            'payment_status' => 'pending',
            'amount' => 50,
            'balance_due' => 50,
        ];

        // Should not throw an exception
        InvoiceLifecyclePolicy::assertCanPhysicallyDelete($expense);
        $this->assertTrue(true);
    }

    public function testPaidExpenseCannotBePhysicallyDeleted(): void
    {
        $expense = [
            'id' => 4,
            'transaction_type' => 'expense',
            'payment_status' => 'paid',
            'amount' => 50,
            'balance_due' => 0,
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This entry has financial or payment status and cannot be deleted.');
        InvoiceLifecyclePolicy::assertCanPhysicallyDelete($expense);
    }

    public function testInvoiceRejectsBasisAndLineItemMutations(): void
    {
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
            'due_date' => '2026-11-01',
            'payment_status' => 'pending',
            'notes' => 'Old note',
        ];

        $protectedFields = [
            'amount', 'discount_amount', 'tax_percentage', 'currency', 'exchange_rate',
            'patient_id', 'examination_id', 'description', 'service_type', 'service_details',
            'due_date', 'transaction_date', 'payment_method', 'liquidity_account', 'transaction_type', 'notes',
        ];

        foreach ($protectedFields as $field) {
            try {
                InvoiceLifecyclePolicy::assertCanUpdate($invoice, [$field]);
                $this->fail("Expected mutation rejection for invoice field '{$field}'.");
            } catch (DomainException $e) {
                $this->assertStringContainsString("cannot be changed on a persisted invoice", $e->getMessage());
            }
        }
    }

    public function testInvoicePermitsAuditOnlyInternalNote(): void
    {
        $invoice = [
            'id' => 10,
            'transaction_type' => 'invoice',
            'notes' => 'Old note',
        ];

        // Audit-only internal note is allowed; printed notes are immutable.
        InvoiceLifecyclePolicy::assertCanUpdate($invoice, ['internal_note']);
        $this->assertTrue(true);
    }

    public function testSameValueEquivalence(): void
    {
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('amount', '100.00', 100));
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('amount', 100.0, '100.000'));
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('exchange_rate', 3.500000, '3.5'));
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('patient_id', '5', 5));
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('notes', 'hello', 'hello'));
        $this->assertTrue(InvoiceLifecyclePolicy::sameValue('notes', null, ''));

        $this->assertFalse(InvoiceLifecyclePolicy::sameValue('amount', 100.0, 101.0));
        $this->assertFalse(InvoiceLifecyclePolicy::sameValue('patient_id', 5, 6));
        $this->assertFalse(InvoiceLifecyclePolicy::sameValue('notes', 'hello', 'world'));
    }
}
