<?php

namespace App\Services;

class InvoiceLifecyclePolicy
{
    /**
     * Fields strictly immutable on all financial records.
     */
    public const STRICT_IMMUTABLE_FIELDS = [
        'id',
        'clinic_id',
        'transaction_id',
        'transaction_type',
        'source_entity_type',
        'source_entity_id',
        'created_by',
        'created_at',
    ];

    /**
     * Computed/ledger fields managed exclusively by settlement or ledger services.
     */
    public const COMPUTED_FIELDS = [
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'paid_date',
        'payment_status',
    ];

    /**
     * Financial basis and document line fields protected on persisted invoices.
     * Issuance cannot be proven unissued for persisted invoices; preserve their document basis.
     */
    public const INVOICE_PROTECTED_BASIS_FIELDS = [
        // Clinical / patient association
        'patient_id',
        'examination_id',

        // Monetary basis, FX, and rates
        'amount',
        'amount_original',
        'currency',
        'exchange_rate',
        'discount_amount',
        'tax_percentage',

        // Payment mechanics and treasury
        'payment_method',
        'liquidity_account',

        // Issued document lines and terms
        'description',
        'service_type',
        'service_details',
        'due_date',
        'transaction_date',
        'counterparty_name',
        'supplier_id',
        'expense_category',
        'notes',
    ];

    /**
     * Genuinely safe metadata fields editable on persisted invoices.
     * Only a separate audit-only addendum is mutable; existing notes are printed on invoices.
     */
    public const INVOICE_SAFE_METADATA_FIELDS = [
        'internal_note',
    ];

    /**
     * History-sensitive fields on expenses (editable ONLY when pending and unlinked).
     */
    public const EXPENSE_HISTORY_SENSITIVE_FIELDS = [
        'amount',
        'amount_original',
        'currency',
        'exchange_rate',
        'discount_amount',
        'tax_percentage',
        'payment_method',
        'liquidity_account',
        'counterparty_name',
        'supplier_id',
        'expense_category',
        'transaction_date',
        'due_date',
        'description',
        'service_details',
    ];

    /**
     * Whether the transaction is an invoice.
     */
    public static function isInvoice(string|array $typeOrRow): bool
    {
        $type = is_array($typeOrRow) ? ($typeOrRow['transaction_type'] ?? '') : $typeOrRow;
        return $type === 'invoice';
    }

    /**
     * Whether the server has established positive unissued draft proof for this invoice.
     * In the current schema and runtime, ordinary persisted invoices have no server
     * positive unissued draft proof. Pending and overdue are payment collection states,
     * not positive draft proof. All persisted ordinary invoices must be treated as
     * legacy-unknown / issued.
     */
    public static function hasServerPositiveUnissuedProof(array $row): bool
    {
        return false;
    }

    /**
     * Asserts whether a persisted financial entry may be physically deleted.
     *
     * Invoices: Require positive unissued proof AND no finance/clinical/provider/export/
     * reminder/attempt/communicated history. Since no server positive proof exists today,
     * physical deletion of persisted invoices is fail-closed rejected. Reminder history
     * is preserved and must not be erased as a substitute.
     *
     * Expenses: May be deleted if genuinely pending and unlinked from payments, payouts,
     * clinical history, liquidity transfers, and attachments.
     *
     * @throws \DomainException
     */
    public static function assertCanPhysicallyDelete(array $row): void
    {
        $type = (string) ($row['transaction_type'] ?? '');

        if ($type === 'invoice') {
            if (!self::hasServerPositiveUnissuedProof($row)) {
                throw new \DomainException('Invoice issuance is not proven unissued; physical deletion is blocked. Preserve the invoice and its history.');
            }
        } elseif ($type === 'expense') {
            $status = (string) ($row['payment_status'] ?? '');
            if ($status !== 'pending') {
                throw new \DomainException('This entry has financial or payment status and cannot be deleted. Use the existing reversal or correction workflow.');
            }
        } else {
            throw new \DomainException('This financial entry type cannot be deleted.');
        }
    }

    /**
     * Scalar and numeric equivalence comparison to filter identical roundtrips.
     */
    public static function sameValue(string $field, mixed $new, mixed $old): bool
    {
        $numeric = [
            'amount', 'discount_amount', 'tax_percentage', 'amount_original', 'exchange_rate',
            'tax_amount', 'total_amount', 'amount_paid', 'balance_due', 'id', 'clinic_id', 'created_by',
            'patient_id', 'examination_id', 'supplier_id', 'source_entity_id'
        ];

        if ($new === null || $new === '') {
            return $old === null || $old === '';
        }

        if (in_array($field, $numeric, true)) {
            return is_numeric($new) && is_numeric($old) && is_finite((float) $new)
                && (float) $new === (float) $old;
        }

        return (string) $new === (string) ($old ?? '');
    }

    public static function validateInternalNote(mixed $note): string
    {
        if (!is_string($note) || !preg_match('//u', $note) || trim($note) === ''
            || mb_strlen($note, 'UTF-8') > 5000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $note)) {
            throw new \InvalidArgumentException('Internal note must contain 1 to 5000 plain-text characters.');
        }
        return trim($note);
    }

    /**
     * Asserts whether the proposed field mutations are permitted for an invoice or expense.
     *
     * @param array $existing Persisted database row
     * @param array $changedFields Array of field names that differ from the persisted row
     * @throws FinanceConflictException|\DomainException
     */
    public static function assertCanUpdate(array $existing, array $changedFields): void
    {
        $type = (string) ($existing['transaction_type'] ?? '');

        if ($type === 'invoice') {
            // Persisted invoices cannot have clinical association, financial basis,
            // payment mechanics, or issued line items modified.
            $forbiddenChanges = array_intersect($changedFields, array_merge(
                self::STRICT_IMMUTABLE_FIELDS,
                self::COMPUTED_FIELDS,
                self::INVOICE_PROTECTED_BASIS_FIELDS
            ));

            if (!empty($forbiddenChanges)) {
                $first = reset($forbiddenChanges);
                $message = "Field '{$first}' cannot be changed on a persisted invoice. Issuance is not proven unissued; preserve the invoice document and use an approved correction workflow.";
                throw new \DomainException($message);
            }

            // Any field not in safe metadata (internal notes) is rejected
            $nonSafeMetadata = array_diff($changedFields, self::INVOICE_SAFE_METADATA_FIELDS);
            if (!empty($nonSafeMetadata)) {
                $first = reset($nonSafeMetadata);
                $message = "Field '{$first}' cannot be changed on a persisted invoice.";
                throw new \DomainException($message);
            }
        }
    }
}
