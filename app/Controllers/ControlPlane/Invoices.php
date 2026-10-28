<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Libraries\IonAuth;
use App\Models\ClinicModel;
use App\Models\ControlPlaneInvoiceModel;
use App\Models\ControlPlaneInvoicePaymentModel;
use App\Services\ControlPlaneAuditService;

class Invoices extends BaseController
{
    protected ControlPlaneInvoiceModel $invoiceModel;
    protected ControlPlaneInvoicePaymentModel $paymentModel;
    protected ClinicModel $clinicModel;
    protected ControlPlaneAuditService $auditService;
    protected $ionAuth;

    public function __construct()
    {
        $this->invoiceModel = new ControlPlaneInvoiceModel();
        $this->paymentModel = new ControlPlaneInvoicePaymentModel();
        $this->clinicModel = new ClinicModel();
        $this->auditService = new ControlPlaneAuditService();
        $this->ionAuth = new IonAuth();
    }

    public function index()
    {
        $filters = [];
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $clinicId = (int) ($this->request->getGet('clinic_id') ?? 0);
        $from = $this->normalizeDateOnly((string) $this->request->getGet('from'));
        $to = $this->normalizeDateOnly((string) $this->request->getGet('to'));

        if ($status !== '') {
            $filters['status'] = $status;
        }
        if ($clinicId > 0) {
            $filters['clinic_id'] = $clinicId;
        }
        if ($from) {
            $filters['from'] = $from;
        }
        if ($to) {
            $filters['to'] = $to;
        }

        $invoices = $this->invoiceModel->getInvoicesWithClinic($filters);
        $stats = [
            'outstanding' => 0,
            'revenue_month' => 0,
            'overdue' => 0,
        ];
        $currentMonth = date('Y-m');
        foreach ($invoices as $invoiceRow) {
            $amount = (float) ($invoiceRow['total_amount'] ?? 0);
            if (($invoiceRow['status'] ?? '') !== 'paid') {
                $stats['outstanding'] += $amount;
            }
            if (($invoiceRow['status'] ?? '') === 'overdue') {
                $stats['overdue'] += 1;
            }
            if (!empty($invoiceRow['issue_date']) && str_starts_with($invoiceRow['issue_date'], $currentMonth) && ($invoiceRow['status'] ?? '') === 'paid') {
                $stats['revenue_month'] += $amount;
            }
        }
        $stats['outstanding'] = round($stats['outstanding'], 2);
        $stats['revenue_month'] = round($stats['revenue_month'], 2);
        $clinics = $this->clinicModel->orderBy('name')->findAll();

        $this->auditService->logEvent('invoice_index', ['route' => '/controlplane/invoices']);

        return $this->view('control_plane/invoices/index', [
            'title' => 'Invoices',
            'invoices' => $invoices,
            'clinics' => $clinics,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $clinics = $this->clinicModel->orderBy('name')->findAll();
        $this->auditService->logEvent('invoice_create_view', ['route' => '/controlplane/invoices/create']);

        return $this->view('control_plane/invoices/create', [
            'title' => 'Create Invoice',
            'clinics' => $clinics,
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $rules = [
            'clinic_id' => 'required|integer|greater_than[0]',
            'issue_date' => 'required|valid_date',
            'due_date' => 'required|valid_date',
            'tax_percentage' => 'required|numeric',
            'line_items_json' => 'required',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $items = json_decode($this->request->getPost('line_items_json'), true);
        if (!is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'At least one line item is required.');
        }

        $taxPercentage = (float) $this->request->getPost('tax_percentage');
        $totals = $this->invoiceModel->calculateTotals($items, $taxPercentage);

        $payload = [
            'clinic_id' => (int) $this->request->getPost('clinic_id'),
            'invoice_number' => $this->invoiceModel->generateInvoiceNumber(),
            'status' => 'sent',
            'issue_date' => $this->normalizePostedDateOnly('issue_date') ?: date('Y-m-d'),
            'due_date' => $this->normalizePostedDateOnly('due_date') ?: date('Y-m-d'),
            'currency' => $this->request->getPost('currency') ?: 'USD',
            'subtotal' => $totals['subtotal'],
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $totals['tax_amount'],
            'total_amount' => $totals['total_amount'],
            'items_json' => json_encode($items),
            'notes' => $this->request->getPost('notes'),
            'private_notes' => $this->request->getPost('private_notes'),
            'created_by' => $this->getCurrentUserId(),
        ];

        $this->invoiceModel->insert($payload);
        $invoiceId = $this->invoiceModel->getInsertID();

        $this->auditService->logEvent('invoice_create', [
            'route' => '/controlplane/invoices',
            'metadata' => ['invoice_id' => $invoiceId, 'clinic_id' => $payload['clinic_id']],
        ]);

        return redirect()->to(base_url('controlplane/invoices'))->with('success', 'Invoice created.');
    }

    public function show(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice not found');
        }

        $clinic = $this->clinicModel->find($invoice['clinic_id']);
        $payments = $this->paymentModel->getPaymentsForInvoice($id);

        return $this->view('control_plane/invoices/view', [
            'title' => 'Invoice ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'clinic' => $clinic,
            'payments' => $payments,
        ]);
    }

    public function markPaid(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice not found');
        }

        $paymentAmount = max(0, (float) ($this->request->getPost('payment_amount') ?? $invoice['total_amount']));
        $paymentMethod = $this->request->getPost('payment_method') ?: 'manual';
        $transactionReference = $this->request->getPost('transaction_reference');
        $notes = $this->request->getPost('payment_notes');
        $now = date('Y-m-d H:i:s');

        $this->paymentModel->insert([
            'invoice_id' => $id,
            'amount' => $paymentAmount,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'transaction_reference' => $transactionReference,
            'notes' => $notes,
            'paid_at' => $now,
            'created_by' => $this->getCurrentUserId(),
        ]);

        $this->invoiceModel->update($id, [
            'status' => 'paid',
            'paid_at' => $now,
        ]);

        $this->auditService->logEvent('invoice_paid', [
            'route' => '/controlplane/invoices/' . $id . '/mark-paid',
            'metadata' => ['invoice_id' => $id, 'amount' => $paymentAmount],
        ]);

        return redirect()->back()->with('success', 'Invoice marked as paid.');
    }

    public function email(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice not found');
        }

        $this->auditService->logEvent('invoice_email', [
            'route' => '/controlplane/invoices/' . $id . '/email',
            'metadata' => ['invoice_id' => $id],
        ]);

        return redirect()->back()->with('success', 'Invoice email action logged.');
    }

    public function delete(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice not found');
        }

        $status = $invoice['status'] ?? 'draft';
        $deletableStatuses = ['draft', 'void', 'cancelled'];
        if (!in_array($status, $deletableStatuses, true)) {
            return redirect()->back()->with('error', 'Only draft, void, or cancelled invoices can be deleted.');
        }

        if (!$this->invoiceModel->delete($id)) {
            return redirect()->back()->with('error', 'Failed to delete invoice.');
        }

        $this->auditService->logEvent('invoice_delete', [
            'route' => '/controlplane/invoices/' . $id . '/delete',
            'metadata' => [
                'invoice_id' => $id,
                'clinic_id' => $invoice['clinic_id'] ?? null,
                'status' => $status,
            ],
        ]);

        return redirect()->to(base_url('controlplane/invoices'))->with('success', 'Invoice deleted.');
    }

    private function getCurrentUserId(): ?int
    {
        return $this->ionAuth->loggedIn() ? (int) $this->ionAuth->getUserId() : null;
    }
}
