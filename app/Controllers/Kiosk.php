<?php

namespace App\Controllers;

use App\Libraries\ApiRuntimeContext;
use App\Models\AppointmentModel;
use App\Models\ClinicModel;
use App\Models\SettingsModel;
use App\Services\PlanGuard;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kiosk extends BaseController
{
    protected ClinicModel $clinicModel;
    protected SettingsModel $settingsModel;
    protected AppointmentModel $appointmentModel;
    protected PlanGuard $planGuard;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->settingsModel = new SettingsModel();
        $this->appointmentModel = new AppointmentModel();
        $this->planGuard = new PlanGuard();
    }

    public function checkIn(string $clinicUuid)
    {
        $clinic = $this->resolveClinic($clinicUuid);
        if (!$this->isKioskFeatureEnabled((int) $clinic['id'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $settings = $this->getClinicSettings((int) $clinic['id']);

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getGet('reset')) {
            $this->clearKioskSession();
        }

        $data = [
            'title' => 'Self Check-In',
            'clinic' => $clinic,
            'settings' => $settings,
            'pin_required' => true,
            'pin_verified' => $this->isPinVerified((int) $clinic['id']),
            'appointments' => [],
            'lookup_phone' => '',
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

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $pin = trim((string) $this->request->getPost('kiosk_pin'));
        if ($pin === '' || !$this->verifyPin($settings, $pin)) {
            return redirect()->back()->withInput()->with('error', 'Invalid kiosk PIN.');
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

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->isPinVerified((int) $clinic['id'])) {
            return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
                ->with('error', 'Please enter the kiosk PIN first.');
        }

        $rawPhone = trim((string) $this->request->getPost('phone'));
        if ($rawPhone === '' || !preg_match('/^[0-9\s\-()+]{6,20}$/', $rawPhone)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid mobile number.');
        }

        $normalizedPhone = $this->normalizePhone($rawPhone);
        if ($normalizedPhone === '') {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid mobile number.');
        }

        $ipAddress = $this->request->getIPAddress() ?? '0.0.0.0';
        $phoneKey = sha1($normalizedPhone);

        if (!$this->rateLimit('kiosk_lookup_ip_' . $clinic['id'] . '_' . $ipAddress, 30, 600)) {
            return redirect()->back()->withInput()->with('error', 'Too many attempts. Please wait a few minutes.');
        }

        if (!$this->rateLimit('kiosk_lookup_phone_' . $clinic['id'] . '_' . $phoneKey, 5, 600)) {
            return redirect()->back()->withInput()->with('error', 'Too many attempts. Please wait a few minutes.');
        }

        $today = date('Y-m-d');
        $appointments = $this->appointmentModel
            ->select('appointments.id, appointments.appointment_date, appointments.appointment_time, appointments.appointment_type, appointments.status, patients.first_name, patients.last_name, patients.phone')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.appointment_date', $today)
            ->where('appointments.status', 'scheduled')
            ->where('patients.clinic_id', (int) $clinic['id'])
            ->orderBy('appointments.appointment_time', 'ASC')
            ->findAll();

        $matching = [];
        foreach ($appointments as $appointment) {
            $storedPhone = $this->normalizePhone((string) ($appointment['phone'] ?? ''));
            if ($storedPhone !== '' && $storedPhone === $normalizedPhone) {
                $matching[] = $this->formatAppointmentCard($appointment, $settings);
            }
        }

        $allowedIds = array_map(static fn($row) => $row['id'], $matching);
        session()->set([
            'kiosk_last_phone' => $normalizedPhone,
            'kiosk_last_clinic' => (int) $clinic['id'],
            'kiosk_allowed_appointments' => $allowedIds,
        ]);

        $data = [
            'title' => 'Self Check-In',
            'clinic' => $clinic,
            'settings' => $settings,
            'pin_required' => true,
            'pin_verified' => true,
            'appointments' => $matching,
            'lookup_phone' => $normalizedPhone,
        ];

        if (empty($matching)) {
            $data['info_message'] = 'No scheduled appointments found for today.';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'appointments' => $matching,
                'lookup_phone' => $normalizedPhone,
                'info_message' => $data['info_message'] ?? '',
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

        if (!$this->isKioskEnabled($settings)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->isPinVerified((int) $clinic['id'])) {
            return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
                ->with('error', 'Please enter the kiosk PIN first.');
        }

        $appointmentId = (int) $this->request->getPost('appointment_id');
        if (!$appointmentId) {
            return redirect()->back()->with('error', 'Invalid appointment selection.');
        }

        $allowed = session()->get('kiosk_allowed_appointments') ?? [];
        if (!in_array($appointmentId, $allowed, true)) {
            return redirect()->back()->with('error', 'This appointment is not available for check-in.');
        }

        $normalizedPhone = session()->get('kiosk_last_phone');
        $clinicMatch = session()->get('kiosk_last_clinic');
        if (!$normalizedPhone || (int) $clinicMatch !== (int) $clinic['id']) {
            return redirect()->back()->with('error', 'Please re-enter your mobile number.');
        }

        $today = date('Y-m-d');
        $appointment = $this->appointmentModel
            ->select('appointments.id, appointments.status, appointments.appointment_date, patients.phone')
            ->join('patients', 'patients.id = appointments.patient_id')
            ->where('appointments.id', $appointmentId)
            ->where('appointments.appointment_date', $today)
            ->where('appointments.status', 'scheduled')
            ->where('patients.clinic_id', (int) $clinic['id'])
            ->first();

        if (!$appointment) {
            return redirect()->back()->with('error', 'Appointment not found or already confirmed.');
        }

        $storedPhone = $this->normalizePhone((string) ($appointment['phone'] ?? ''));
        if ($storedPhone === '' || $storedPhone !== $normalizedPhone) {
            return redirect()->back()->with('error', 'Mobile number mismatch.');
        }

        $updated = $this->appointmentModel->update($appointmentId, [
            'status' => 'confirmed',
        ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Unable to confirm the appointment.');
        }

        $remaining = array_values(array_filter($allowed, static fn($id) => (int) $id !== $appointmentId));
        session()->set('kiosk_allowed_appointments', $remaining);

        return redirect()->to(base_url('kiosk/' . $clinic['uuid'] . '/check-in'))
            ->with('success', 'Appointment confirmed. Please have a seat.');
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
            'kiosk_pin_ok_until' => time() + (2 * 60 * 60),
            'kiosk_pin_clinic_id' => $clinicId,
        ]);
    }

    private function clearKioskSession(): void
    {
        session()->remove([
            'kiosk_last_phone',
            'kiosk_last_clinic',
            'kiosk_allowed_appointments',
        ]);
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

        $time = $appointment['appointment_time'] ?? '';
        $displayTime = $this->formatTime((string) $time, $settings['time_format'] ?? '12');

        return [
            'id' => (int) $appointment['id'],
            'patient_display' => trim($firstName . ' ' . $lastInitial),
            'appointment_time' => $displayTime,
            'appointment_type' => $this->formatAppointmentType((string) ($appointment['appointment_type'] ?? '')),
        ];
    }

    private function formatAppointmentType(string $type): string
    {
        $type = str_replace('_', ' ', $type);
        $type = trim($type);
        if ($type === '') {
            return 'Appointment';
        }

        return ucwords($type);
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
}
