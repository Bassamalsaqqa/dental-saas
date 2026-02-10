<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\TreatmentContractModel;
use App\Models\TreatmentContractItemModel;
use App\Models\TreatmentContractOdontogramSnapshotModel;
use App\Models\TreatmentContractSignatureModel;
use App\Models\OdontogramModel;
use App\Models\ExaminationModel;
use App\Models\ClinicModel;
use App\Services\PlanGuard;
use App\Services\StorageService;
use App\Services\ActivityLogger;

class TreatmentContracts extends BaseController
{
    protected $patientModel;
    protected $contractModel;
    protected $itemModel;
    protected $snapshotModel;
    protected $signatureModel;
    protected $odontogramModel;
    protected $examinationModel;
    protected $clinicModel;
    protected $planGuard;
    protected $storageService;
    protected $activityLogger;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->contractModel = new TreatmentContractModel();
        $this->itemModel = new TreatmentContractItemModel();
        $this->snapshotModel = new TreatmentContractOdontogramSnapshotModel();
        $this->signatureModel = new TreatmentContractSignatureModel();
        $this->odontogramModel = new OdontogramModel();
        $this->examinationModel = new ExaminationModel();
        $this->clinicModel = new ClinicModel();
        $this->planGuard = new PlanGuard();
        $this->storageService = service('storage');
        $this->activityLogger = new ActivityLogger();
    }

    public function create($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'patient',
            'entity_id' => $patientId
        ]);

        $patient = $this->patientModel->findByClinic($clinicId, $patientId);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $examinationId = $this->request->getGet('examination_id');
        $examinationId = is_numeric($examinationId) ? (int) $examinationId : null;
        if (!$examinationId) {
            return redirect()->to(base_url('patient/' . $patientId))
                ->with('error', 'Please start the treatment plan from an examination.');
        }

        $exam = $this->examinationModel->where('clinic_id', $clinicId)->find($examinationId);
        if (!$exam || (int) $exam['patient_id'] !== (int) $patientId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $data = [
            'title' => 'Create Treatment Contract',
            'mode' => 'create',
            'contract' => null,
            'patient' => $patient,
            'examination_id' => $examinationId,
            'odontogram' => $this->odontogramModel->getOdontogramByPatient($patientId),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'items' => [],
            'snapshot' => null,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP', 'BDT'],
        ];

        return $this->view('contracts/create', $data);
    }

    public function store($patientId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'patient',
            'entity_id' => $patientId
        ]);

        $patient = $this->patientModel->findByClinic($clinicId, $patientId);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $notes = trim((string) $this->request->getPost('notes'));
        $terms = trim((string) $this->request->getPost('terms_conditions'));
        $paymentTerms = trim((string) $this->request->getPost('payment_terms'));
        $durationPlan = trim((string) $this->request->getPost('duration_plan'));
        $costDetails = trim((string) $this->request->getPost('cost_details'));
        $currencyOptions = ['ILS', 'USD', 'JOD', 'EUR', 'GBP', 'BDT'];
        $baseCurrency = settings()->get('currency', 'ILS');
        $currency = trim((string) $this->request->getPost('currency')) ?: $baseCurrency;
        if (!in_array($currency, $currencyOptions, true)) {
            $currency = $baseCurrency;
        }
        $odontogramJson = (string) $this->request->getPost('odontogram_json');
        $odontogramSvg = (string) $this->request->getPost('odontogram_svg');
        $examinationId = $this->request->getPost('examination_id');
        $examinationId = is_numeric($examinationId) ? (int) $examinationId : null;
        if (!$examinationId) {
            return redirect()->back()->with('error', 'Examination context is required to create a treatment plan.');
        }

        $exam = $this->examinationModel->where('clinic_id', $clinicId)->find($examinationId);
        if (!$exam || (int) $exam['patient_id'] !== (int) $patientId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        $items = $this->request->getPost('items');
        $items = is_array($items) ? $items : [];

        $total = 0.0;
        foreach ($items as $item) {
            $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
            $price = isset($item['price']) ? (float) $item['price'] : 0;
            $total += ($qty > 0 ? $qty : 1) * $price;
        }

        $contractId = $this->contractModel->insert([
            'patient_id' => $patientId,
            'examination_id' => $examinationId,
            'status' => 'draft',
            'currency' => $currency,
            'total_cost' => $total,
            'notes' => $notes ?: null,
            'terms_conditions' => $terms ?: null,
            'payment_terms' => $paymentTerms ?: null,
            'duration_plan' => $durationPlan ?: null,
            'cost_details' => $costDetails ?: null,
            'created_by' => session()->get('user_id') ?? null
        ]);

        foreach ($items as $item) {
            $this->itemModel->insert([
                'contract_id' => $contractId,
                'procedure_code' => trim((string) ($item['code'] ?? '')) ?: null,
                'tooth_no' => trim((string) ($item['tooth'] ?? '')) ?: null,
                'description' => trim((string) ($item['description'] ?? '')) ?: null,
                'qty' => isset($item['qty']) ? (int) $item['qty'] : 1,
                'price' => isset($item['price']) ? (float) $item['price'] : 0,
            ]);
        }

        $this->snapshotModel->insert([
            'contract_id' => $contractId,
            'svg_snapshot' => $odontogramSvg ?: null,
            'data_snapshot_json' => $odontogramJson ?: null
        ]);

        $this->activityLogger->logPatientActivity(
            'contract_create',
            $patientId,
            "Treatment contract #{$contractId} created"
        );

        if ($examinationId) {
            return redirect()
                ->to(base_url('examination/' . $examinationId))
                ->with('success', 'Treatment plan contract created successfully.');
        }

        return redirect()
            ->to(base_url('contracts/' . $contractId))
            ->with('contracts_flash', '1');
    }

    public function edit($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $contract = $this->contractModel
            ->select('treatment_contracts.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = treatment_contracts.created_by', 'left')
            ->where('treatment_contracts.clinic_id', $clinicId)
            ->where('treatment_contracts.id', $contractId)
            ->first();
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Only draft contracts can be edited.');
        }

        $patient = $this->patientModel->findByClinic($clinicId, $contract['patient_id']);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $returnTo = $this->request->getGet('return');
        if ($returnTo && !preg_match('#^examination/\\d+$#', $returnTo)) {
            $returnTo = null;
        }
        if (!$returnTo && !empty($contract['examination_id'])) {
            $returnTo = 'examination/' . $contract['examination_id'];
        }

        $items = $this->itemModel->where('contract_id', $contractId)->findAll();
        $snapshot = $this->snapshotModel->where('contract_id', $contractId)->first();

        $data = [
            'title' => 'Edit Treatment Contract',
            'mode' => 'edit',
            'contract' => $contract,
            'patient' => $patient,
            'odontogram' => $this->odontogramModel->getOdontogramByPatient($contract['patient_id']),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'items' => $items,
            'snapshot' => $snapshot,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP', 'BDT'],
        ];

        return $this->view('contracts/create', $data);
    }

    public function update($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $contract = $this->contractModel
            ->select('treatment_contracts.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = treatment_contracts.created_by', 'left')
            ->where('treatment_contracts.clinic_id', $clinicId)
            ->where('treatment_contracts.id', $contractId)
            ->first();
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Only draft contracts can be edited.');
        }

        $notes = trim((string) $this->request->getPost('notes'));
        $terms = trim((string) $this->request->getPost('terms_conditions'));
        $paymentTerms = trim((string) $this->request->getPost('payment_terms'));
        $durationPlan = trim((string) $this->request->getPost('duration_plan'));
        $costDetails = trim((string) $this->request->getPost('cost_details'));
        $currencyOptions = ['ILS', 'USD', 'JOD', 'EUR', 'GBP', 'BDT'];
        $baseCurrency = settings()->get('currency', 'ILS');
        $currency = trim((string) $this->request->getPost('currency')) ?: $baseCurrency;
        if (!in_array($currency, $currencyOptions, true)) {
            $currency = $baseCurrency;
        }
        $odontogramJson = (string) $this->request->getPost('odontogram_json');
        $odontogramSvg = (string) $this->request->getPost('odontogram_svg');

        $items = $this->request->getPost('items');
        $items = is_array($items) ? $items : [];

        $total = 0.0;
        foreach ($items as $item) {
            $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
            $price = isset($item['price']) ? (float) $item['price'] : 0;
            $total += ($qty > 0 ? $qty : 1) * $price;
        }

        $this->contractModel->update($contractId, [
            'currency' => $currency,
            'total_cost' => $total,
            'notes' => $notes ?: null,
            'terms_conditions' => $terms ?: null,
            'payment_terms' => $paymentTerms ?: null,
            'duration_plan' => $durationPlan ?: null,
            'cost_details' => $costDetails ?: null,
        ]);

        $this->itemModel->where('contract_id', $contractId)->delete();
        foreach ($items as $item) {
            $this->itemModel->insert([
                'contract_id' => $contractId,
                'procedure_code' => trim((string) ($item['code'] ?? '')) ?: null,
                'tooth_no' => trim((string) ($item['tooth'] ?? '')) ?: null,
                'description' => trim((string) ($item['description'] ?? '')) ?: null,
                'qty' => isset($item['qty']) ? (int) $item['qty'] : 1,
                'price' => isset($item['price']) ? (float) $item['price'] : 0,
            ]);
        }

        $existingSnapshot = $this->snapshotModel->where('contract_id', $contractId)->first();
        if ($existingSnapshot) {
            $this->snapshotModel->update($existingSnapshot['id'], [
                'svg_snapshot' => $odontogramSvg ?: null,
                'data_snapshot_json' => $odontogramJson ?: null
            ]);
        } else {
            $this->snapshotModel->insert([
                'contract_id' => $contractId,
                'svg_snapshot' => $odontogramSvg ?: null,
                'data_snapshot_json' => $odontogramJson ?: null
            ]);
        }

        $this->activityLogger->logPatientActivity(
            'contract_update',
            $contract['patient_id'],
            "Treatment contract #{$contractId} updated"
        );

        return redirect()
            ->to(base_url('contracts/' . $contractId))
            ->with('success', 'Contract updated.')
            ->with('contracts_flash', '1');
    }

    public function delete($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $contract = $this->contractModel->findByClinic($clinicId, $contractId);
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Signed contracts cannot be deleted.');
        }

        $this->itemModel->where('contract_id', $contractId)->delete();
        $this->snapshotModel->where('contract_id', $contractId)->delete();
        $this->signatureModel->where('contract_id', $contractId)->delete();
        $this->contractModel->delete($contractId);

        $this->activityLogger->logPatientActivity(
            'contract_delete',
            $contract['patient_id'],
            "Treatment contract #{$contractId} deleted"
        );

        return redirect()
            ->to(base_url('patient/' . $contract['patient_id']))
            ->with('success', 'Contract deleted.')
            ->with('contracts_flash', '1');
    }

    public function show($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $contract = $this->contractModel->findByClinic($clinicId, $contractId);
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        $patient = $this->patientModel->findByClinic($clinicId, $contract['patient_id']);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $returnTo = $this->request->getGet('return');
        if ($returnTo && !preg_match('#^examination/\\d+$#', $returnTo)) {
            $returnTo = null;
        }
        if (!$returnTo && !empty($contract['examination_id'])) {
            $returnTo = 'examination/' . $contract['examination_id'];
        }

        $items = $this->itemModel->where('contract_id', $contractId)->findAll();
        $snapshot = $this->snapshotModel->where('contract_id', $contractId)->first();
        $signature = $this->signatureModel->where('contract_id', $contractId)->first();

        $data = [
            'title' => 'Treatment Contract',
            'contract' => $contract,
            'patient' => $patient,
            'items' => $items,
            'snapshot' => $snapshot,
            'signature' => $signature,
            'signature_enabled' => $this->planGuard->isFeatureEnabled($clinicId, 'contracts.signature.enabled', false),
            'return_to' => $returnTo,
            'condition_types' => $this->odontogramModel->getConditionTypes()
        ];

        return $this->view('contracts/show', $data);
    }

    public function sign($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        $contract = $this->contractModel->findByClinic($clinicId, $contractId);
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);
        $this->planGuard->assertFeature($clinicId, 'contracts.signature.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->back()->with('error', 'Contract is already signed.');
        }

        $signatureData = trim((string) $this->request->getPost('signature_data'));
        $signerName = trim((string) $this->request->getPost('signer_name'));

        if ($signatureData === '' || $signerName === '') {
            return redirect()->back()->with('error', 'Signature and name are required.');
        }

        $now = date('Y-m-d H:i:s');
        $userAgent = substr((string) $this->request->getUserAgent(), 0, 255);

        $signatureId = $this->signatureModel->insert([
            'contract_id' => $contractId,
            'signer_name' => $signerName,
            'signer_role' => 'patient',
            'signature_data' => $signatureData,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $userAgent,
            'signed_at' => $now
        ]);

        $snapshot = $this->snapshotModel->where('contract_id', $contractId)->first();
        $items = $this->itemModel->where('contract_id', $contractId)->findAll();

        $signatureHash = hash('sha256', json_encode([
            'contract' => $contractId,
            'signature' => $signatureData,
            'name' => $signerName,
            'snapshot' => $snapshot,
            'items' => $items,
            'notes' => $contract['notes'] ?? null
        ]));

        $this->contractModel->update($contractId, [
            'status' => 'signed',
            'signed_at' => $now,
            'signature_hash' => $signatureHash,
            'signature_meta_json' => json_encode([
                'signer_name' => $signerName,
                'signature_id' => $signatureId
            ])
        ]);

        $patient = $this->patientModel->findByClinic($clinicId, $contract['patient_id']);
        $contractForPrint = $this->contractModel
            ->select('treatment_contracts.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = treatment_contracts.created_by', 'left')
            ->where('treatment_contracts.clinic_id', $clinicId)
            ->where('treatment_contracts.id', $contractId)
            ->first();
        $html = view('contracts/print', [
            'contract' => $contractForPrint ?? $contract,
            'patient' => $patient,
            'clinic' => $this->clinicModel->find($clinicId),
            'items' => $items,
            'snapshot' => $snapshot,
            'signature' => $this->signatureModel->find($signatureId)
        ]);

        $fileName = 'contract_' . ($patient['patient_id'] ?? $contract['patient_id']) . '_' . date('Y-m-d') . '.html';
        $attachment = $this->storageService->storeGenerated(
            $html,
            $fileName,
            'text/html',
            $clinicId,
            'contract',
            $contractId,
            'contract_pdf'
        );

        $this->contractModel->update($contractId, [
            'signed_attachment_id' => $attachment['id']
        ]);

        $this->activityLogger->logPatientActivity(
            'contract_sign',
            $contract['patient_id'],
            "Treatment contract #{$contractId} signed"
        );

        $returnTo = $this->request->getGet('return') ?: $this->request->getPost('return');
        if ($returnTo && preg_match('#^examination/\\d+$#', $returnTo)) {
            return redirect()
                ->to(base_url($returnTo))
                ->with('success', 'Contract signed successfully.')
                ->with('contracts_flash', '1');
        }

        return redirect()
            ->to(base_url('contracts/' . $contractId))
            ->with('success', 'Contract signed successfully.')
            ->with('contracts_flash', '1');
    }

    public function print($contractId)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $contract = $this->contractModel->findByClinic($clinicId, $contractId);
        if (!$contract) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Contract not found');
        }

        $this->planGuard->assertFeature($clinicId, 'contracts.enabled', [
            'entity_type' => 'contract',
            'entity_id' => $contractId
        ]);

        $patient = $this->patientModel->findByClinic($clinicId, $contract['patient_id']);
        if (!$patient) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Patient not found');
        }

        $items = $this->itemModel->where('contract_id', $contractId)->findAll();
        $snapshot = $this->snapshotModel->where('contract_id', $contractId)->first();
        $signature = $this->signatureModel->where('contract_id', $contractId)->first();

        return $this->view('contracts/print', [
            'contract' => $contract,
            'patient' => $patient,
            'clinic' => $this->clinicModel->find($clinicId),
            'items' => $items,
            'snapshot' => $snapshot,
            'signature' => $signature
        ]);
    }
}
