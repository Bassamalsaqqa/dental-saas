<?php

namespace App\Controllers;

use App\Services\InvoiceCorrectionService;
use App\Services\PermissionService;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Stateful tenant web entrypoints; monetary policy and authorization remain service-owned. */
class InvoiceCorrections extends BaseController
{
    private ?PermissionService $permissions = null;

    private function permissions(): PermissionService
    {
        return $this->permissions ??= new PermissionService();
    }

    private function context(bool $write = false, bool $approval = false): array
    {
        $config = config('InvoiceCorrections');
        if (!$config->enabled || $config->documentMode !== 'operational') {
            throw PageNotFoundException::forPageNotFound();
        }
        $permission = $this->permissions();
        $clinicId = (int) ($permission->resolveCurrentClinicId() ?? 0);
        $actor = (int) $this->ionAuth->getUserId();
        if ($clinicId < 1 || $actor < 1 || !$permission->getTenantRoleId($actor, $clinicId)
            || !$permission->hasPermission($actor, 'finance', 'view')
            || ($write && (!$permission->hasPermission($actor, 'finance', 'edit')
                || !$permission->hasPermission($actor, 'finance', 'create')))
            || ($approval && !$permission->hasPermission($actor, 'finance', 'approve_corrections'))) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->response->setHeader('Cache-Control', 'private, no-store, max-age=0');
        return [$clinicId, $actor];
    }

    /** Conceal foreign or missing web records before rendering financial state. */
    private function assertInvoiceVisible(int $clinicId, int $invoiceId): void
    {
        try { (new InvoiceCorrectionService())->assertInvoiceAvailable($clinicId, $invoiceId); }
        catch (\DomainException $error) { throw PageNotFoundException::forPageNotFound(); }
    }

    private function visibleDocument(int $clinicId, int $correctionId): array
    {
        $service = new InvoiceCorrectionService();
        try { $invoiceId = $service->documentInvoiceId($clinicId, $correctionId); }
        catch (\DomainException $error) { throw PageNotFoundException::forPageNotFound(); }
        $this->assertInvoiceVisible($clinicId, $invoiceId);
        return $service->getDocument($clinicId, $correctionId);
    }

    private function payload(array $keys): array
    {
        $payload = [];
        foreach ($keys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null && !is_string($value)) {
                throw new \InvalidArgumentException('Invalid scalar input.');
            }
            $payload[$key] = $value ?? '';
        }
        return $payload;
    }

    private function mutationFailure(\Throwable $error, int $invoiceId)
    {
        // Never expose SQL, financial snapshots or arbitrary exception HTML to users.
        log_message('error', 'Invoice correction rejected: {class}', ['class' => get_class($error)]);
        return redirect()->to(site_url('finance/' . $invoiceId . '/corrections'))
            ->withInput()->with('correction_error', lang('InvoiceCorrections.rejected'));
    }

    public function index(int $invoiceId)
    {
        [$clinicId, $actor] = $this->context();
        $this->assertInvoiceVisible($clinicId, $invoiceId);
        $service = new InvoiceCorrectionService();
        $position = $service->position($clinicId, $invoiceId);
        return view('finance/corrections/index', [
            'title' => lang('InvoiceCorrections.title'),
            'invoice' => $position['original'], 'position' => $position,
            'corrections' => $service->listForInvoice($clinicId, $invoiceId),
            'idempotencyKey' => bin2hex(random_bytes(24)),
            'canWrite' => $this->permissions()->hasPermission($actor, 'finance', 'edit')
                && $this->permissions()->hasPermission($actor, 'finance', 'create'),
        ]);
    }

    public function preview(int $invoiceId)
    {
        [$clinicId, $actor] = $this->context(true);
        try {
            $payload = $this->payload(['amount_original', 'reason', 'idempotency_key']);
            $preview = (new InvoiceCorrectionService())->preview($clinicId, $invoiceId, $actor, $payload);
            return $this->previewPage($preview, $payload, $actor);
        } catch (\Throwable $error) {
            return $this->mutationFailure($error, $invoiceId);
        }
    }

    private function previewPage(array $preview, array $payload, int $actor)
    {
        return view('finance/corrections/preview', [
            'title' => lang('InvoiceCorrections.preview'), 'preview' => $preview, 'payload' => $payload,
            'canApprove' => $this->permissions()->hasPermission($actor, 'finance', 'approve_corrections'),
        ]);
    }

    public function approve(int $invoiceId)
    {
        [$clinicId, $actor] = $this->context(true, true);
        try {
            $payload = $this->payload(['amount_original', 'reason', 'idempotency_key', 'source_hash']);
            if ($this->request->getPost('confirm') !== '1') {
                throw new \InvalidArgumentException('Explicit approval confirmation required.');
            }
            $service = new InvoiceCorrectionService();
            $approval = $service->approve($clinicId, $invoiceId, $actor, $payload);
            $preview = $service->preview($clinicId, $invoiceId, $actor, $payload);
            $payload['approval_id'] = (string) $approval['approval_id'];
            $payload['source_hash'] = (string) $approval['source_hash'];
            // A state change between approval and preview cannot be represented as an approved preview.
            if (!hash_equals($payload['source_hash'], (string) $preview['source_hash'])) {
                throw new \RuntimeException('Approval is stale.');
            }
            return $this->previewPage($preview, $payload, $actor);
        } catch (\Throwable $error) {
            return $this->mutationFailure($error, $invoiceId);
        }
    }

    public function issue(int $invoiceId)
    {
        [$clinicId, $actor] = $this->context(true);
        try {
            $payload = $this->payload(['amount_original', 'reason', 'idempotency_key', 'source_hash', 'approval_id']);
            if ($this->request->getPost('confirm') !== '1') {
                throw new \InvalidArgumentException('Explicit issuance confirmation required.');
            }
            $result = (new InvoiceCorrectionService())->issue($clinicId, $invoiceId, $actor, $payload);
            return redirect()->to(site_url('finance/corrections/' . (int) $result['id']))
                ->with('correction_success', lang('InvoiceCorrections.issued'));
        } catch (\Throwable $error) {
            return $this->mutationFailure($error, $invoiceId);
        }
    }

    public function show(int $correctionId)
    {
        [$clinicId, $actor] = $this->context();
        $document = $this->visibleDocument($clinicId, $correctionId);
        return view('finance/corrections/document', [
            'title' => lang('InvoiceCorrections.document'), 'document' => $document,
            'idempotencyKey' => bin2hex(random_bytes(24)),
            'canWrite' => $this->permissions()->hasPermission($actor, 'finance', 'edit')
                && $this->permissions()->hasPermission($actor, 'finance', 'create'),
        ]);
    }

    public function allocate(int $correctionId)
    {
        [$clinicId, $actor] = $this->context(true);
        $service = new InvoiceCorrectionService();
        $document = $this->visibleDocument($clinicId, $correctionId);
        $invoiceId = (int) $document['correction']['finance_id'];
        try {
            $payload = $this->payload(['target_finance_id', 'amount_original', 'idempotency_key', 'source_hash']);
            if ($this->request->getPost('confirm') !== '1') {
                throw new \InvalidArgumentException('Explicit allocation confirmation required.');
            }
            $service->allocate($clinicId, $correctionId, $actor, $payload);
            return redirect()->to(site_url('finance/corrections/' . $correctionId))
                ->with('correction_success', lang('InvoiceCorrections.allocated'));
        } catch (\Throwable $error) {
            return $this->mutationFailure($error, $invoiceId);
        }
    }

    public function linkReplacement(int $invoiceId)
    {
        [$clinicId, $actor] = $this->context(true);
        try {
            $payload = $this->payload(['target_finance_id']);
            if (!ctype_digit($payload['target_finance_id']) || $this->request->getPost('confirm') !== '1') {
                throw new \InvalidArgumentException('Explicit valid replacement required.');
            }
            (new InvoiceCorrectionService())->linkReplacement($clinicId, $invoiceId, (int) $payload['target_finance_id'], $actor);
            return redirect()->to(site_url('finance/' . $invoiceId . '/corrections'))
                ->with('correction_success', lang('InvoiceCorrections.linked'));
        } catch (\Throwable $error) {
            return $this->mutationFailure($error, $invoiceId);
        }
    }
    public function returnApplication(int $correctionId, int $applicationId)
    {
        [$clinicId, $actor] = $this->context(true);
        $service = new InvoiceCorrectionService();
        $document = $this->visibleDocument($clinicId, $correctionId);
        try {
            $matches = array_filter($document['applications'], static fn(array $row): bool => (int) $row['id'] === $applicationId);
            if (count($matches) !== 1 || $this->request->getPost('confirm') !== '1') { throw new \InvalidArgumentException('Explicit linked application return required.'); }
            $payload = $this->payload(['reason', 'idempotency_key']);
            $service->reverseApplication($clinicId, $applicationId, $actor, $payload);
            return redirect()->to(site_url('finance/corrections/' . $correctionId))->with('correction_success', lang('InvoiceCorrections.returned'));
        } catch (\Throwable $error) { return $this->mutationFailure($error, (int) $document['correction']['finance_id']); }
    }

}
