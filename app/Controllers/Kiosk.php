<?php

namespace App\Controllers;

use App\Libraries\ApiRuntimeContext;
use App\Models\AppointmentModel;
use App\Models\ClinicModel;
use App\Models\FinanceModel;
use App\Models\PatientModel;
use App\Models\SettingsModel;
use App\Models\TreatmentContractItemModel;
use App\Models\TreatmentContractModel;
use App\Models\TreatmentContractOdontogramSnapshotModel;
use App\Models\TreatmentContractSignatureModel;
use App\Services\OdontogramSvgSnapshotService;
use App\Services\PlanGuard;
use App\Services\StorageService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kiosk extends BaseController
{
    protected ClinicModel $clinicModel;
    protected SettingsModel $settingsModel;
    protected AppointmentModel $appointmentModel;
    protected PatientModel $patientModel;
    protected FinanceModel $financeModel;
    protected TreatmentContractModel $contractModel;
    protected TreatmentContractItemModel $contractItemModel;
    protected TreatmentContractOdontogramSnapshotModel $contractSnapshotModel;
    protected TreatmentContractSignatureModel $contractSignatureModel;
    protected OdontogramSvgSnapshotService $odontogramSvgSnapshotService;
    protected PlanGuard $planGuard;
    protected StorageService $storageService;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->settingsModel = new SettingsModel();
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->financeModel = new FinanceModel();
        $this->contractModel = new TreatmentContractModel();
        $this->contractItemModel = new TreatmentContractItemModel();
        $this->contractSnapshotModel = new TreatmentContractOdontogramSnapshotModel();
        $this->contractSignatureModel = new TreatmentContractSignatureModel();
        $this->odontogramSvgSnapshotService = new OdontogramSvgSnapshotService();
        $this->planGuard = new PlanGuard();
        $this->storageService = new StorageService();
        $this->localeResolver = service('localeResolver');
    }

    public function checkIn(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $locale = $this->applyKioskLocale((int) $clinic['id'], $settings);

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getGet('reset')) {
            $this->clearKioskSession();
        }

        $data = [
            'title' => $this->tr('title'),
            'clinic' => $clinic,
            'settings' => $settings,
            'locale' => $locale,
            'is_rtl' => $this->localeResolver->isRtl($locale),
            'pin_required' => true,
            'pin_verified' => $this->isPinVerified((int) $clinic['id']),
            'appointments' => [],
            'lookup_phone' => '',
            'balance_summary' => null,
            'completion' => session()->getFlashdata('kiosk_completion'),
        ];

        return $this->view('kiosk/check_in', $data);
    }

    public function reset(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->clearKioskSession();

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'));
    }

    public function unlock(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $this->applyKioskLocale((int) $clinic['id'], $settings);

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $pin = trim((string) $this->request->getPost('kiosk_pin'));
        if ($pin === '' || !$this->verifyPin($settings, $pin)) {
            return redirect()->back()->withInput()->with('error', $this->tr('msgInvalidPin'));
        }

        $this->setPinVerified((int) $clinic['id']);

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'));
    }

    public function lookup(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $locale = $this->applyKioskLocale((int) $clinic['id'], $settings);

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->isPinVerified((int) $clinic['id'])) {
            return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
                ->with('error', $this->tr('msgEnterPinFirst'));
        }

        $rawPhone = trim((string) $this->request->getPost('phone'));
        if ($rawPhone === '' || !preg_match('/^[0-9\s\-()+]{6,20}$/', $rawPhone)) {
            return redirect()->back()->withInput()->with('error', $this->tr('msgInvalidMobile'));
        }

        $normalizedPhone = $this->normalizePhone($rawPhone);
        if ($normalizedPhone === '') {
            return redirect()->back()->withInput()->with('error', $this->tr('msgInvalidMobile'));
        }

        $ipAddress = $this->request->getIPAddress() ?? '0.0.0.0';
        $phoneKey = sha1($normalizedPhone);

        if (!$this->rateLimit('kiosk_lookup_ip_' . $clinic['id'] . '_' . $ipAddress, 30, 600)) {
            return redirect()->back()->withInput()->with('error', $this->tr('msgTooManyAttempts'));
        }

        if (!$this->rateLimit('kiosk_lookup_phone_' . $clinic['id'] . '_' . $phoneKey, 5, 600)) {
            return redirect()->back()->withInput()->with('error', $this->tr('msgTooManyAttempts'));
        }

        $patients = $this->patientModel
            ->select('id, first_name, last_name, phone, email')
            ->where('clinic_id', (int) $clinic['id'])
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->findAll();

        $patientMap = [];
        foreach ($patients as $patient) {
            $storedPhone = $this->normalizePhone((string) ($patient['phone'] ?? ''));
            if ($storedPhone !== '' && $storedPhone === $normalizedPhone) {
                $patientMap[(int) $patient['id']] = [
                    'id' => (int) $patient['id'],
                    'name' => trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')),
                    'phone' => $patient['phone'] ?? '',
                    'email' => $patient['email'] ?? ''
                ];
            }
        }

        $allowedPatients = array_keys($patientMap);
        $matching = [];
        $allowedIds = [];
        if (!empty($allowedPatients)) {
            $today = $this->clinicToday($settings);
            $appointments = $this->appointmentModel
                ->select('appointments.id, appointments.appointment_date, appointments.appointment_time, appointments.appointment_type, appointments.status, patients.id as patient_id, patients.first_name, patients.last_name, patients.phone, patients.email')
                ->join('patients', 'patients.id = appointments.patient_id')
                ->where('patients.clinic_id', (int) $clinic['id'])
                ->whereIn('appointments.patient_id', $allowedPatients)
                ->where('appointments.appointment_date >=', $today)
                ->orderBy('appointments.appointment_date', 'ASC')
                ->orderBy('appointments.appointment_time', 'ASC')
                ->findAll();

            foreach ($appointments as $appointment) {
                $formatted = $this->formatAppointmentCard($appointment, $settings);
                $matching[] = $formatted;
                if (!empty($formatted['can_confirm'])) {
                    $allowedIds[] = (int) $formatted['id'];
                }
            }
        }
        $contractsEnabled = $this->planGuard->isFeatureEnabled((int) $clinic['id'], 'contracts.enabled', false);
        $signatureEnabled = $this->planGuard->isFeatureEnabled((int) $clinic['id'], 'contracts.signature.enabled', false);
        $balanceSummary = $this->buildBalanceSummary((int) $clinic['id'], $allowedPatients);
        $contracts = [];
        if ($contractsEnabled && !empty($allowedPatients)) {
            foreach ($allowedPatients as $patientId) {
                $contract = $this->contractModel
                    ->where('clinic_id', (int) $clinic['id'])
                    ->where('patient_id', (int) $patientId)
                    ->where('status', 'draft')
                    ->orderBy('created_at', 'DESC')
                    ->first();
                if (!$contract) {
                    continue;
                }
                $items = $this->contractItemModel->where('contract_id', $contract['id'])->findAll();
                $snapshot = $this->contractSnapshotModel->where('contract_id', $contract['id'])->first();
                $contracts[] = [
                    'id' => (int) $contract['id'],
                    'patient_id' => (int) $patientId,
                    'patient_name' => $patientMap[(int) $patientId]['name'] ?? '',
                    'currency' => $contract['currency'] ?? 'ILS',
                    'total_cost' => $contract['total_cost'] ?? 0,
                    'created_at' => $contract['created_at'] ?? null,
                    'items' => $items,
                    'snapshot_svg' => $this->odontogramSvgSnapshotService->decode($snapshot['svg_snapshot'] ?? ''),
                ];
            }
        }

        session()->set([
            'kiosk_last_phone' => $normalizedPhone,
            'kiosk_last_clinic' => (int) $clinic['id'],
            'kiosk_allowed_appointments' => $allowedIds,
            'kiosk_allowed_patients' => $allowedPatients,
            'kiosk_allowed_contracts' => array_map(static fn($row) => (int) $row['id'], $contracts),
        ]);

        $data = [
            'title' => $this->tr('title'),
            'clinic' => $clinic,
            'settings' => $settings,
            'locale' => $locale,
            'is_rtl' => $this->localeResolver->isRtl($locale),
            'pin_required' => true,
            'pin_verified' => true,
            'appointments' => $matching,
            'lookup_phone' => $normalizedPhone,
            'patients' => array_values($patientMap),
            'contracts' => $contracts,
            'contracts_enabled' => $contractsEnabled,
            'signature_enabled' => $signatureEnabled,
            'balance_summary' => $balanceSummary,
            'completion' => null,
        ];

        if (empty($allowedPatients)) {
            $data['info_message'] = $this->tr('msgNoPatientRecord');
        } elseif (empty($matching)) {
            $data['info_message'] = $this->tr('msgNoUpcomingAppointments');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'appointments' => $matching,
                'lookup_phone' => $normalizedPhone,
                'info_message' => $data['info_message'] ?? '',
                'patients' => array_values($patientMap),
                'contracts' => $contracts,
                'contracts_enabled' => $contractsEnabled,
                'signature_enabled' => $signatureEnabled,
                'balance_summary' => $balanceSummary,
                'csrf_name' => csrf_token(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        return $this->view('kiosk/check_in', $data);
    }

    public function confirm(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $this->applyKioskLocale((int) $clinic['id'], $settings);

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->isPinVerified((int) $clinic['id'])) {
            return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
                ->with('error', $this->tr('msgEnterPinFirst'));
        }

        $appointmentId = (int) $this->request->getPost('appointment_id');
        if (!$appointmentId) {
            return redirect()->back()->with('error', $this->tr('msgInvalidAppointment'));
        }

        $allowed = session()->get('kiosk_allowed_appointments') ?? [];
        if (!in_array($appointmentId, $allowed, true)) {
            return redirect()->back()->with('error', $this->tr('msgAppointmentUnavailable'));
        }

        $normalizedPhone = session()->get('kiosk_last_phone');
        $clinicMatch = session()->get('kiosk_last_clinic');
        if (!$normalizedPhone || (int) $clinicMatch !== (int) $clinic['id']) {
            return redirect()->back()->with('error', $this->tr('msgReenterMobile'));
        }

        $today = $this->clinicToday($settings);
        $appointment = $this->appointmentModel
            ->select('appointments.id, appointments.status, appointments.appointment_date, appointments.appointment_time, appointments.appointment_type, appointments.patient_id, patients.phone, patients.first_name, patients.last_name')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.id', $appointmentId)
            ->where('appointments.appointment_date', $today)
            ->where('appointments.status', 'scheduled')
            ->where('patients.clinic_id', (int) $clinic['id'])
            ->first();

        if (!$appointment) {
            return redirect()->back()->with('error', $this->tr('msgAppointmentNotFound'));
        }

        $storedPhone = $this->normalizePhone((string) ($appointment['phone'] ?? ''));
        if ($storedPhone === '' || $storedPhone !== $normalizedPhone) {
            return redirect()->back()->with('error', $this->tr('msgMobileMismatch'));
        }

        $updated = $this->appointmentModel->update($appointmentId, [
            'status' => 'confirmed',
        ]);

        if (!$updated) {
            return redirect()->back()->with('error', $this->tr('msgUnableConfirmAppointment'));
        }

        $remaining = array_values(array_filter($allowed, static fn($id) => (int) $id !== $appointmentId));
        session()->set('kiosk_allowed_appointments', $remaining);

        $completion = [
            'message' => $this->tr('msgAppointmentConfirmed'),
            'patient_name' => trim(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '')),
            'appointment_date' => $appointment['appointment_date'] ?? '',
            'appointment_time' => $appointment['appointment_time'] ?? '',
            'appointment_type' => $appointment['appointment_type'] ?? '',
            'balance_summary' => $this->buildBalanceSummary((int) $clinic['id'], [(int) ($appointment['patient_id'] ?? 0)]),
        ];

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
            ->with('kiosk_completion', $completion);
    }

    public function updateDetails(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $this->applyKioskLocale((int) $clinic['id'], $settings);
        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }
        if (!$this->isPinVerified((int) $clinic['id'])) {
            return $this->respondKioskError($clinic, $this->tr('msgEnterPinFirst'));
        }

        $patientId = (int) $this->request->getPost('patient_id');
        $allowedPatients = session()->get('kiosk_allowed_patients') ?? [];
        if (!$patientId || !in_array($patientId, $allowedPatients, true)) {
            return $this->respondKioskError($clinic, $this->tr('msgInvalidPatient'));
        }

        $phone = trim((string) $this->request->getPost('phone'));
        $email = trim((string) $this->request->getPost('email'));
        if ($phone !== '' && !preg_match('/^[0-9\s\-()+]{6,20}$/', $phone)) {
            return $this->respondKioskError($clinic, $this->tr('msgInvalidMobile'));
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->respondKioskError($clinic, $this->tr('msgInvalidEmail'));
        }

        $updated = $this->patientModel
            ->where('clinic_id', (int) $clinic['id'])
            ->update($patientId, [
                'phone' => $phone ?: null,
                'email' => $email ?: null,
            ]);

        if (!$updated) {
            return $this->respondKioskError($clinic, $this->tr('msgUnableUpdateDetailsNow'));
        }

        return $this->respondKioskSuccess($clinic, $this->tr('msgDetailsUpdated'));
    }

    public function signContract(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);
        $this->applyKioskLocale((int) $clinic['id'], $settings);
        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }
        if (!$this->isPinVerified((int) $clinic['id'])) {
            return $this->respondKioskError($clinic, $this->tr('msgEnterPinFirst'));
        }

        $this->planGuard->assertFeature((int) $clinic['id'], 'contracts.enabled');
        $this->planGuard->assertFeature((int) $clinic['id'], 'contracts.signature.enabled');

        $contractId = (int) $this->request->getPost('contract_id');
        $patientId = (int) $this->request->getPost('patient_id');
        $allowedContracts = session()->get('kiosk_allowed_contracts') ?? [];
        $allowedPatients = session()->get('kiosk_allowed_patients') ?? [];

        if (!$contractId || !in_array($contractId, $allowedContracts, true)) {
            return $this->respondKioskError($clinic, $this->tr('msgInvalidContract'));
        }
        if (!$patientId || !in_array($patientId, $allowedPatients, true)) {
            return $this->respondKioskError($clinic, $this->tr('msgInvalidPatient'));
        }

        $contract = $this->contractModel->findByClinic((int) $clinic['id'], $contractId);
        if (!$contract || (int) $contract['patient_id'] !== $patientId) {
            return $this->respondKioskError($clinic, $this->tr('msgContractNotFound'));
        }
        if (($contract['status'] ?? 'draft') !== 'draft') {
            return $this->respondKioskError($clinic, $this->tr('msgContractAlreadySigned'));
        }

        $signatureData = trim((string) $this->request->getPost('signature_data'));
        $signerName = trim((string) $this->request->getPost('signer_name'));
        if ($signatureData === '' || $signerName === '') {
            return $this->respondKioskError($clinic, $this->tr('msgSignatureRequired'));
        }

        $now = date('Y-m-d H:i:s');
        $userAgent = substr((string) $this->request->getUserAgent(), 0, 255);

        $signatureId = $this->contractSignatureModel->insert([
            'contract_id' => $contractId,
            'signer_name' => $signerName,
            'signer_role' => 'patient',
            'signature_data' => $signatureData,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $userAgent,
            'signed_at' => $now
        ]);

        $snapshot = $this->contractSnapshotModel->where('contract_id', $contractId)->first();
        $items = $this->contractItemModel->where('contract_id', $contractId)->findAll();

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

        $patient = $this->patientModel->findByClinic((int) $clinic['id'], $contract['patient_id']);
        $contractForPrint = $this->contractModel
            ->select('treatment_contracts.*, users.first_name as creator_first_name, users.last_name as creator_last_name')
            ->join('users', 'users.id = treatment_contracts.created_by', 'left')
            ->where('treatment_contracts.clinic_id', (int) $clinic['id'])
            ->where('treatment_contracts.id', $contractId)
            ->first();
        $html = view('contracts/print', [
            'contract' => $contractForPrint ?? $contract,
            'patient' => $patient,
            'clinic' => $this->clinicModel->find((int) $clinic['id']),
            'items' => $items,
            'snapshot' => $snapshot,
            'signature' => $this->contractSignatureModel->find($signatureId)
        ]);

        $fileName = 'contract_' . ($patient['patient_id'] ?? $contract['patient_id']) . '_' . date('Y-m-d') . '.html';
        $attachment = $this->storageService->storeGenerated(
            $html,
            $fileName,
            'text/html',
            (int) $clinic['id'],
            'contract',
            $contractId,
            'contract_pdf'
        );

        $this->contractModel->update($contractId, [
            'signed_attachment_id' => $attachment['id']
        ]);

        return $this->respondKioskSuccess($clinic, $this->tr('msgContractSigned'), [
            'contract_id' => $contractId
        ]);
    }

    private function resolveClinic(string $clinicUuid): array
    {
        $clinic = $this->clinicModel
            ->where('uuid', $clinicUuid)
            ->where('status', 'active')
            ->first();

        if (!$clinic) {
            throw PageNotFoundException::forPageNotFound();
        }

        ApiRuntimeContext::setClinicId((int) $clinic['id']);

        return $clinic;
    }

    private function getClinicSettings(int $clinicId): array
    {
        $settingsRows = $this->settingsModel
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->findAll();

        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $this->parseSettingValue($row['setting_value'], $row['setting_type']);
        }

        $defaults = [
            'clinic_name' => 'DentalCare Clinic',
            'clinic_logo_path' => '',
            'clinic_tagline' => 'Professional Suite',
            'kiosk_enabled' => 0,
            'kiosk_pin_hash' => '',
            'kiosk_header' => 'Self Check-In',
            'kiosk_subheader' => 'Confirm your appointment in seconds.',
            'kiosk_footer_note' => 'Need help? Please ask the reception desk.',
            'kiosk_default_language' => 'ar',
            'timezone' => 'Asia/Jerusalem',
            'time_format' => '12',
        ];

        return array_merge($defaults, $settings);
    }

    private function parseSettingValue($value, $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float) $value : $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    private function isKioskEnabled(array $settings): bool
    {
        return !empty($settings['kiosk_enabled']) && !empty($settings['kiosk_pin_hash']);
    }

    private function verifyPin(array $settings, string $pin): bool
    {
        $hash = $settings['kiosk_pin_hash'] ?? '';
        if ($hash === '') {
            return false;
        }

        return password_verify($pin, $hash);
    }

    private function isPinVerified(int $clinicId): bool
    {
        $pinClinic = (int) (session()->get('kiosk_pin_clinic_id') ?? 0);
        $pinUntil = (int) (session()->get('kiosk_pin_ok_until') ?? 0);

        if ($pinClinic !== $clinicId) {
            return false;
        }

        return $pinUntil > time();
    }

    private function setPinVerified(int $clinicId): void
    {
        session()->set([
            'kiosk_pin_ok_until' => time() + (7 * 24 * 60 * 60),
            'kiosk_pin_clinic_id' => $clinicId,
        ]);
    }

    private function clearKioskSession(): void
    {
        session()->remove([
            'kiosk_last_phone',
            'kiosk_last_clinic',
            'kiosk_allowed_appointments',
            'kiosk_allowed_patients',
            'kiosk_allowed_contracts',
        ]);
    }

    private function buildBalanceSummary(int $clinicId, array $patientIds): ?array
    {
        $patientIds = array_values(array_filter(array_map('intval', $patientIds), static fn($id) => $id > 0));
        if ($patientIds === []) {
            return null;
        }

        $rows = $this->financeModel
            ->select('patient_id, total_amount, amount, discount_amount, tax_amount, balance_due')
            ->where('clinic_id', $clinicId)
            ->whereIn('patient_id', $patientIds)
            ->where('transaction_type', 'invoice')
            ->findAll();

        if ($rows === []) {
            return [
                'outstanding_amount' => 0.0,
                'outstanding_formatted' => formatCurrency(0),
                'open_invoices' => 0,
                'has_due' => false,
            ];
        }

        $outstanding = 0.0;
        $openInvoices = 0;
        foreach ($rows as $row) {
            $totalAmount = isset($row['total_amount'])
                ? (float) $row['total_amount']
                : (float) (($row['amount'] ?? 0) - ($row['discount_amount'] ?? 0) + ($row['tax_amount'] ?? 0));
            $balanceDue = isset($row['balance_due']) ? max(0, (float) $row['balance_due']) : max(0, $totalAmount);
            $outstanding += $balanceDue;
            if ($balanceDue > 0.009) {
                $openInvoices++;
            }
        }

        return [
            'outstanding_amount' => round($outstanding, 2),
            'outstanding_formatted' => formatCurrency($outstanding),
            'open_invoices' => $openInvoices,
            'has_due' => $outstanding > 0.009,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        return $digits ?? '';
    }

    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 2) {
            return $phone;
        }

        return str_repeat('*', max(0, $length - 2)) . substr($phone, -2);
    }

    private function formatAppointmentCard(array $appointment, array $settings): array
    {
        $firstName = trim((string) ($appointment['first_name'] ?? ''));
        $lastName = trim((string) ($appointment['last_name'] ?? ''));
        $lastInitial = '';
        if ($lastName !== '') {
            $initial = $this->safeInitial($lastName);
            if ($initial !== '') {
                $lastInitial = strtoupper($initial) . '.';
            }
        }

        $date = $appointment['appointment_date'] ?? '';
        $time = $appointment['appointment_time'] ?? '';
        $displayTime = $this->formatTime((string) $time, $settings['time_format'] ?? '12');
        $displayDate = $date ? date('M j, Y', strtotime($date)) : '';
        $isToday = $date === $this->clinicToday($settings);
        $confirmAllowed = $isToday && ($appointment['status'] ?? '') === 'scheduled';

        return [
            'id' => (int) $appointment['id'],
            'patient_display' => trim($firstName . ' ' . $lastInitial),
            'appointment_time' => $displayTime,
            'appointment_type' => $this->formatAppointmentType((string) ($appointment['appointment_type'] ?? '')),
            'appointment_date' => $displayDate,
            'can_confirm' => $confirmAllowed,
            'status' => $appointment['status'] ?? '',
            'status_label' => $this->formatAppointmentStatus((string) ($appointment['status'] ?? '')),
        ];
    }

    private function formatAppointmentType(string $type): string
    {
        $type = strtolower(trim($type));
        $map = [
            'consultation' => 'typeConsultation',
            'treatment' => 'typeTreatment',
            'follow_up' => 'typeFollowUp',
            'follow-up' => 'typeFollowUp',
            'emergency' => 'typeEmergency',
            'cleaning' => 'typeCleaning',
            'checkup' => 'typeCheckup',
        ];
        if (isset($map[$type])) {
            return $this->tr($map[$type]);
        }

        $type = str_replace('_', ' ', $type);
        return $type !== '' ? ucwords($type) : $this->tr('typeAppointment');
    }

    private function formatAppointmentStatus(string $status): string
    {
        $status = strtolower(trim($status));
        $map = [
            'scheduled' => 'statusScheduled',
            'confirmed' => 'statusConfirmed',
            'completed' => 'statusCompleted',
            'cancelled' => 'statusCancelled',
            'no_show' => 'statusNoShow',
            'no-show' => 'statusNoShow',
        ];
        if (isset($map[$status])) {
            return $this->tr($map[$status]);
        }

        if ($status === '') {
            return '';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    private function formatTime(string $time, string $format): string
    {
        if ($time === '') {
            return '';
        }

        $timestamp = strtotime($time);
        if ($timestamp === false) {
            return $time;
        }

        if ($format === '24') {
            return date('H:i', $timestamp);
        }

        return date('g:i A', $timestamp);
    }

    private function safeInitial(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $initial = $value[0];
        if (!preg_match('/[A-Za-z]/', $initial)) {
            return '';
        }

        return $initial;
    }

    private function tr(string $key, array $params = []): string
    {
        return lang('Kiosk.' . $key, $params);
    }

    private function applyKioskLocale(int $clinicId, array $settings): string
    {
        $locale = $this->localeResolver->resolve($this->request, [
            'supported' => ['ar', 'en'],
            'default' => 'ar',
            'session_key' => 'kiosk_locale_' . $clinicId,
            'settings_locale' => $settings['kiosk_default_language'] ?? 'ar',
        ]);

        $this->localeResolver->apply($this->request, $locale);
        return $locale;
    }

    private function clinicToday(array $settings): string
    {
        $timezone = (string) ($settings['timezone'] ?? 'UTC');
        try {
            $now = new \DateTimeImmutable('now', new \DateTimeZone($timezone));
        } catch (\Throwable $e) {
            $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        return $now->format('Y-m-d');
    }

    private function rateLimit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $cache = cache();
        $now = time();
        $entries = $cache->get($key);

        if (!is_array($entries)) {
            $entries = [];
        }

        $entries = array_values(array_filter($entries, static fn($ts) => $ts >= ($now - $windowSeconds)));

        if (count($entries) >= $maxAttempts) {
            return false;
        }

        $entries[] = $now;
        $cache->save($key, $entries, $windowSeconds);

        return true;
    }

    private function isKioskFeatureEnabled(int $clinicId): bool
    {
        return $this->planGuard->isFeatureEnabled($clinicId, 'kiosk.enabled', false);
    }

    private function respondKioskError(array $clinic, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $message,
                'csrf_name' => csrf_token(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))->with('error', $message);
    }

    private function respondKioskSuccess(array $clinic, string $message, array $extra = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => $message,
                'csrf_name' => csrf_token(),
                'csrf_token' => csrf_hash(),
            ], $extra));
        }

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))->with('success', $message);
    }
}
