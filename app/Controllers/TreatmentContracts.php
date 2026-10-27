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
use App\Services\OdontogramSnapshotService;
use App\Services\OdontogramSvgSnapshotService;
use App\Services\ContractSigningService;

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
    protected $odontogramSnapshotService;
    protected $odontogramSvgSnapshotService;
    protected $contractSigningService;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->patientModel = new PatientModel($db);
        $this->contractModel = new TreatmentContractModel($db);
        $this->itemModel = new TreatmentContractItemModel($db);
        $this->snapshotModel = new TreatmentContractOdontogramSnapshotModel($db);
        $this->signatureModel = new TreatmentContractSignatureModel($db);
        $this->odontogramModel = new OdontogramModel($db);
        $this->examinationModel = new ExaminationModel($db);
        $this->clinicModel = new ClinicModel($db);
        $this->planGuard = new PlanGuard($db);
        $this->storageService = service('storage');
        $this->activityLogger = new ActivityLogger();
        $this->odontogramSnapshotService = new OdontogramSnapshotService();
        $this->odontogramSvgSnapshotService = new OdontogramSvgSnapshotService();
        $this->contractSigningService = new ContractSigningService($db);
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
        $exam = $this->resolveExamContext($clinicId, (int) $patientId, $examinationId);
        $returnTo = $this->resolveContractReturnTo(
            $this->request->getGet('return_to'),
            (int) $patientId,
            $exam['id'] ?? null
        );

        $data = [
            'title' => 'Create Treatment Contract',
            'mode' => 'create',
            'contract' => null,
            'patient' => $patient,
            'examination_id' => $exam['id'] ?? null,
            'odontogram' => $this->getContractSeedOdontogram(
                (int) $patientId,
                isset($exam['id']) ? (int) $exam['id'] : null
            ),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'items' => [],
            'snapshot' => null,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'return_to' => $returnTo,
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
        $currencyOptions = ['ILS', 'USD', 'JOD', 'EUR', 'GBP'];
        $baseCurrency = settings()->get('currency', 'ILS');
        $currency = trim((string) $this->request->getPost('currency')) ?: $baseCurrency;
        if (!in_array($currency, $currencyOptions, true)) {
            $currency = $baseCurrency;
        }
        $odontogramJson = (string) $this->request->getPost('odontogram_json');
        $odontogramSvg = (string) $this->request->getPost('odontogram_svg');
        $examinationId = $this->request->getPost('examination_id');
        $examinationId = is_numeric($examinationId) ? (int) $examinationId : null;
        $exam = $this->resolveExamContext($clinicId, (int) $patientId, $examinationId);
        $returnTo = $this->resolveContractReturnTo(
            $this->request->getPost('return_to'),
            (int) $patientId,
            $exam['id'] ?? null
        );

        $items = $this->request->getPost('items');
        $items = is_array($items) ? $items : [];
        $totalInput = $this->request->getPost('total_cost');

        $calculatedTotal = 0.0;
        foreach ($items as $item) {
            $qty = isset($item['qty']) ? (int) $item['qty'] : 1;
            $price = isset($item['price']) ? (float) $item['price'] : 0;
            $calculatedTotal += ($qty > 0 ? $qty : 1) * $price;
        }
        $manualTotal = is_numeric($totalInput) ? (float) $totalInput : null;
        $total = ($manualTotal !== null && $manualTotal >= 0) ? $manualTotal : $calculatedTotal;
        $total = round($total, 2);

        $contractId = $this->contractModel->insert([
            'patient_id' => $patientId,
            'examination_id' => $exam['id'] ?? null,
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
            'svg_snapshot' => $this->odontogramSvgSnapshotService->encode($odontogramSvg),
            'data_snapshot_json' => $odontogramJson ?: null
        ]);

        $actorUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
        $this->odontogramSnapshotService->capturePatientBaselineSnapshot(
            $clinicId,
            (int) $patientId,
            'contract',
            (int) $contractId,
            $actorUserId,
            'Contract #' . $contractId
        );

        $this->activityLogger->logPatientActivity(
            'contract_create',
            $patientId,
            "Treatment contract #{$contractId} created"
        );

        if ($returnTo) {
            return $this->redirectAfterWorkflow($returnTo, ['patient', 'patients', 'examination', 'examinations', 'contracts'])
                ->with('success', 'Treatment plan contract created successfully.');
        }

        return $this->redirectAfterWorkflow('contracts/' . $contractId, ['patient', 'patients', 'examination', 'examinations', 'contracts'])
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

        $returnTo = $this->resolveContractReturnTo(
            $this->request->getGet('return_to') ?? $this->request->getGet('return'),
            (int) $contract['patient_id'],
            !empty($contract['examination_id']) ? (int) $contract['examination_id'] : null
        );

        $items = $this->itemModel->where('contract_id', $contractId)->findAll();
        $snapshot = $this->snapshotModel->where('contract_id', $contractId)->first();
        $draftRevision = $this->contractSigningService->immutableConsentFingerprint($contract, $items, $snapshot);
        if ($snapshot) {
            $snapshot['svg_snapshot'] = $this->odontogramSvgSnapshotService->decode($snapshot['svg_snapshot'] ?? null);
        }
        $exam = !empty($contract['examination_id'])
            ? $this->resolveExamContext($clinicId, (int) $contract['patient_id'], (int) $contract['examination_id'])
            : null;

        $data = [
            'draft_revision' => $draftRevision,
            'title' => 'Edit Treatment Contract',
            'mode' => 'edit',
            'contract' => $contract,
            'patient' => $patient,
            'odontogram' => $this->getContractSeedOdontogram(
                (int) $contract['patient_id'],
                isset($exam['id']) ? (int) $exam['id'] : null
            ),
            'condition_types' => $this->odontogramModel->getConditionTypes(),
            'items' => $items,
            'snapshot' => $snapshot,
            'base_currency' => settings()->get('currency', 'ILS'),
            'currency_options' => ['ILS', 'USD', 'JOD', 'EUR', 'GBP'],
            'return_to' => $returnTo,
            'examination_id' => $exam['id'] ?? null,
        ];

        return $this->view('contracts/create', $data);
    }

    private function resolveExamContext(int $clinicId, int $patientId, ?int $examinationId): ?array
    {
        if (!$examinationId) {
            return null;
        }

        $exam = $this->examinationModel->where('clinic_id', $clinicId)->find($examinationId);
        if (!$exam || (int) ($exam['patient_id'] ?? 0) !== $patientId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Examination not found');
        }

        return $exam;
    }

    private function getContractSeedOdontogram(int $patientId, ?int $examinationId = null): array
    {
        if ($examinationId) {
            return $this->odontogramModel->getEffectiveOdontogramForExamination($patientId, $examinationId);
        }

        return $this->odontogramModel->getOdontogramByPatient($patientId);
    }

    private function resolveContractReturnTo($returnToRaw, int $patientId, ?int $examinationId): string
    {
        $returnTo = $this->sanitizeInternalReturnTo((string) $returnToRaw, ['patient', 'patients', 'examination', 'examinations', 'contracts']);
        if ($returnTo) {
            return $returnTo;
        }

        if ($examinationId) {
            return 'examination/' . $examinationId;
        }

        return 'patient/' . $patientId;
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
            'entity_id'   => $contractId
        ]);

        // Pre-check (TOCTOU-safe: signing service re-validates under lock)
        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Only draft contracts can be edited.');
        }

        $notes        = trim((string) $this->request->getPost('notes'));
        $terms        = trim((string) $this->request->getPost('terms_conditions'));
        $paymentTerms = trim((string) $this->request->getPost('payment_terms'));
        $durationPlan = trim((string) $this->request->getPost('duration_plan'));
        $costDetails  = trim((string) $this->request->getPost('cost_details'));
        $currencyOptions = ['ILS', 'USD', 'JOD', 'EUR', 'GBP'];
        $baseCurrency = settings()->get('currency', 'ILS');
        $currency = trim((string) $this->request->getPost('currency')) ?: $baseCurrency;
        if (!in_array($currency, $currencyOptions, true)) {
            $currency = $baseCurrency;
        }
        $odontogramJson = (string) $this->request->getPost('odontogram_json');
        $odontogramSvg  = (string) $this->request->getPost('odontogram_svg');

        $items = $this->request->getPost('items');
        $items = is_array($items) ? $items : [];
        $totalInput = $this->request->getPost('total_cost');

        $calculatedTotal = 0.0;
        foreach ($items as $item) {
            $qty   = isset($item['qty'])   ? (int)   $item['qty']   : 1;
            $price = isset($item['price']) ? (float) $item['price'] : 0;
            $calculatedTotal += ($qty > 0 ? $qty : 1) * $price;
        }
        $manualTotal = is_numeric($totalInput) ? (float) $totalInput : null;
        $total = ($manualTotal !== null && $manualTotal >= 0) ? $manualTotal : $calculatedTotal;
        $total = round($total, 2);

        // Capture mutable state before entering lock
        $contractFields = [
            'currency'         => $currency,
            'total_cost'       => $total,
            'notes'            => $notes ?: null,
            'terms_conditions' => $terms ?: null,
            'payment_terms'    => $paymentTerms ?: null,
            'duration_plan'    => $durationPlan ?: null,
            'cost_details'     => $costDetails ?: null,
        ];
        $itemsToInsert     = $items;
        $draftRevision = (string) $this->request->getPost('draft_revision');
        $removedItems = $this->request->getPost('removed_item_ids') ?? [];
        $svgEncoded        = $this->odontogramSvgSnapshotService->encode($odontogramSvg);
        $snapshotJsonField = $odontogramJson ?: null;
        $contractIdInt     = (int) $contractId;
        $patientId         = $contract['patient_id'];

        try {
            $this->contractSigningService->updateDraftWithLock(
                (int) $clinicId,
                $contractIdInt,
                function ($db, array $locked) use ($contractIdInt, $contractFields, $itemsToInsert, $svgEncoded, $snapshotJsonField, $draftRevision, $removedItems): bool {
                    $clinicId = (int) $locked['clinic_id'];
                    // The signing service already owns clinic/patient/exam/contract/item locks.
                    $existing = $this->itemModel->where('clinic_id', $clinicId)
                        ->where('contract_id', $contractIdInt)->orderBy('id')->findAll();
                    $frozen = $this->snapshotModel->where('clinic_id', $clinicId)->where('contract_id', $contractIdInt)->first();
                    $revision = $this->contractSigningService->immutableConsentFingerprint($locked, $existing, $frozen);
                    if (!hash_equals($revision, $draftRevision)) {
                        throw new \DomainException('CONTRACT_DRAFT_STALE');
                    }
                    if (!is_array($removedItems)) { throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE'); }
                    $byId = array_column($existing, null, 'id');
                    $seen = [];
                    $remove = [];
                    foreach ($removedItems as $id) {
                        if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/D', (string) $id)
                            || !isset($byId[(int) $id]) || isset($remove[(int) $id])) {
                            throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE');
                        }
                        $remove[(int) $id] = true;
                    }
                    $writes = [];
                    foreach ($itemsToInsert as $item) {
                        if (!is_array($item)) { throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE'); }
                        $id = $item['id'] ?? '';
                        if (!is_scalar($id) || ($id !== '' && (!preg_match('/^[1-9][0-9]*$/D', (string) $id)
                            || !isset($byId[(int) $id]) || isset($seen[(int) $id]) || isset($remove[(int) $id])))) {
                            throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE');
                        }
                        if ($id !== '') { $seen[(int) $id] = true; }
                        foreach (['code', 'tooth', 'description', 'qty', 'price'] as $field) {
                            if (!array_key_exists($field, $item) || !is_scalar($item[$field])) {
                                throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE');
                            }
                        }
                        if (!preg_match('/^[1-9][0-9]*$/D', (string) $item['qty'])
                            || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', (string) $item['price'])) {
                            throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE');
                        }
                        $writes[] = [$id, [
                            'procedure_code' => $id !== '' ? (string) $item['code'] : (trim((string) $item['code']) ?: null),
                            'tooth_no' => $id !== '' ? (string) $item['tooth'] : (trim((string) $item['tooth']) ?: null),
                            'description' => $id !== '' ? (string) $item['description'] : (trim((string) $item['description']) ?: null),
                            'qty' => (int) $item['qty'], 'price' => $item['price'],
                        ]];
                    }
                    if (count($seen) + count($remove) !== count($byId)) {
                        throw new \DomainException('CONTRACT_ITEMS_INCOMPLETE');
                    }
                    if (!$this->contractModel->update($contractIdInt, $contractFields)) { return false; }
                    foreach ($writes as [$id, $values]) {
                        if ($id !== '') {
                            // Equivalent roundtrips leave persisted line bytes and timestamps alone.
                            $old = $byId[(int) $id];
                            $changes = [];
                            foreach ($values as $field => $value) {
                                $equal = in_array($field, ['qty', 'price'], true)
                                    ? (string) (0 + $old[$field]) === (string) (0 + $value)
                                    : ($old[$field] ?? '') === ($value ?? '');
                                if (!$equal) { $changes[$field] = $value; }
                            }
                            if ($changes && !$this->itemModel->update((int) $id, $changes)) { return false; }
                        } elseif (!$this->itemModel->insert($values + ['clinic_id' => $clinicId, 'contract_id' => $contractIdInt])) {
                            return false;
                        }
                    }
                    foreach (array_keys($remove) as $id) {
                        if (!$this->itemModel->delete($id)) { return false; }
                    }
                    $existingSnapshot = $this->snapshotModel->where('clinic_id', $clinicId)->where('contract_id', $contractIdInt)->first();
                    $snapshot = ['svg_snapshot' => $svgEncoded, 'data_snapshot_json' => $snapshotJsonField];
                    if ($existingSnapshot) {
                        return $this->snapshotModel->update($existingSnapshot['id'], $snapshot) === true;
                    }
                    return (bool) $this->snapshotModel->insert($snapshot + ['clinic_id' => $clinicId, 'contract_id' => $contractIdInt]);
                }
            );
        } catch (\DomainException $e) {
            return redirect()->to(base_url('contracts/' . $contractId . '/edit'))
                ->with('error', lang('Studio.cbDraftConflict'));
        } catch (\RuntimeException $e) {
            $code = explode(':', $e->getMessage())[0];
            if ($code === 'SIGN_ERR_NOT_DRAFT') {
                return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Only draft contracts can be edited.');
            }
            throw $e;
        }


        return $this->redirectAfterWorkflow('contracts/' . $contractId, ['patient', 'patients', 'examination', 'examinations', 'contracts'])
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
            'entity_id'   => $contractId
        ]);

        // Pre-check (re-validated under lock)
        if (($contract['status'] ?? 'draft') !== 'draft') {
            return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Signed contracts cannot be deleted.');
        }

        $contractIdInt = (int) $contractId;
        $patientId     = $contract['patient_id'];

        try {
            $this->contractSigningService->deleteDraftWithLock(
                (int) $clinicId,
                $contractIdInt,
                function ($db, array $locked) use ($contractIdInt): bool {
                    $clinicId = (int) $locked['clinic_id'];
                    return $this->itemModel->where('clinic_id', $clinicId)->where('contract_id', $contractIdInt)->delete() === true
                        && $this->snapshotModel->where('clinic_id', $clinicId)->where('contract_id', $contractIdInt)->delete() === true
                        && $this->contractModel->delete($contractIdInt) === true;
                }
            );
        } catch (\RuntimeException $e) {
            $code = explode(':', $e->getMessage())[0];
            if ($code === 'SIGN_ERR_NOT_DRAFT') {
                return redirect()->to(base_url('contracts/' . $contractId))->with('error', 'Signed contracts cannot be deleted.');
            }
            throw $e;
        }


        return redirect()
            ->to(base_url('patient/' . $patientId))
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
        if ($snapshot) {
            $snapshot['svg_snapshot'] = $this->odontogramSvgSnapshotService->decode($snapshot['svg_snapshot'] ?? null);
        }
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
            'entity_id'   => $contractId
        ]);
        $this->planGuard->assertFeature($clinicId, 'contracts.signature.enabled', [
            'entity_type' => 'contract',
            'entity_id'   => $contractId
        ]);

        $signatureData = trim((string) $this->request->getPost('signature_data'));
        $signerName    = trim((string) $this->request->getPost('signer_name'));

        if ($signatureData === '' || $signerName === '') {
            return redirect()->back()->with('error', 'Signature and name are required.');
        }

        $staffUserId = session()->get('user_id') ? (int) session()->get('user_id') : null;
        $patientId   = (int) $contract['patient_id'];

        try {
            $result = $this->contractSigningService->sign(
                (int) $clinicId,
                (int) $contractId,
                $patientId,
                $signerName,
                $signatureData,
                ContractSigningService::ACTOR_STAFF,
                $staffUserId,
                (string) $this->request->getIPAddress(),
                substr((string) $this->request->getUserAgent(), 0, 255),
                function (array $viewData): string {
                    return view('contracts/print', $viewData);
                },
                !empty($contract['examination_id']) ? (int) $contract['examination_id'] : null
            );
        } catch (\RuntimeException $e) {
            $code = explode(':', $e->getMessage())[0];
            if ($code === 'SIGN_ERR_MISSING_INPUT') {
                return redirect()->back()->with('error', 'Signature and name are required.');
            }
            if ($code === 'SIGN_ERR_PARTIAL_STATE') {
                return redirect()->back()->with('error', 'Contract has inconsistent signed state. Please contact support.');
            }
            // Fail closed for all other signing errors
            log_message('error', 'ContractSigningService::sign failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Contract signing failed. Please try again or contact support.');
        }

        $returnTo = $this->request->getGet('return') ?: $this->request->getPost('return');
        if ($returnTo && preg_match('#^examination/\d+$#', $returnTo)) {
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
        if ($snapshot) {
            $snapshot['svg_snapshot'] = $this->odontogramSvgSnapshotService->decode($snapshot['svg_snapshot'] ?? null);
        }
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
