<?php

namespace App\Services;

use App\Models\AppointmentModel;
use App\Models\ClinicModel;
use App\Models\ExaminationModel;
use App\Models\FinanceModel;
use App\Models\NotificationModel;
use App\Models\PatientModel;
use App\Models\SettingsModel;
use App\Models\WhatsAppTenantPolicyModel;

class WhatsAppWorkflowService
{
    protected NotificationService $notificationService;
    protected NotificationModel $notificationModel;
    protected AppointmentModel $appointmentModel;
    protected ExaminationModel $examinationModel;
    protected FinanceModel $financeModel;
    protected PatientModel $patientModel;
    protected ClinicModel $clinicModel;
    protected SettingsModel $settingsModel;
    protected WhatsAppTenantPolicyModel $policyModel;
    protected WhatsAppTriggerCatalog $triggerCatalog;
    protected WhatsAppTemplateRegistryService $templateRegistry;
    protected PlanGuard $planGuard;
    protected \CodeIgniter\Database\BaseConnection $db;
    protected array $policyCache = [];
    protected ?\DateTimeImmutable $clock = null;

    public function __construct(?\DateTimeImmutable $clock = null)
    {
        $this->clock = $clock;
        $this->notificationService = new NotificationService();
        $this->notificationModel = new NotificationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->examinationModel = new ExaminationModel();
        $this->financeModel = new FinanceModel();
        $this->patientModel = new PatientModel();
        $this->clinicModel = new ClinicModel();
        $this->settingsModel = new SettingsModel();
        $this->policyModel = new WhatsAppTenantPolicyModel();
        $this->triggerCatalog = new WhatsAppTriggerCatalog();
        $this->templateRegistry = new WhatsAppTemplateRegistryService();
        $this->planGuard = new PlanGuard();
        $this->db = \Config\Database::connect();
    }

    public function queueAppointmentConfirmation(int $clinicId, int $appointmentId, ?int $actorUserId = null): array
    {
        if (!$this->isTriggerEnabled($clinicId, 'appointment_confirmation')) {
            return $this->skipped('TRIGGER_DISABLED');
        }

        $appointment = $this->appointmentModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find($appointmentId);
        if (!$appointment) {
            return $this->skipped('APPOINTMENT_NOT_FOUND');
        }

        if ((string) ($appointment['status'] ?? 'scheduled') !== 'scheduled') {
            return $this->skipped('APPOINTMENT_NOT_SCHEDULED');
        }

        $patient = $this->patientModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find((int) ($appointment['patient_id'] ?? 0));
        if (!$patient) {
            return $this->skipped('PATIENT_NOT_FOUND');
        }

        $workflowKey = sprintf(
            'appointment_confirmation:%d:%s:%s',
            $appointmentId,
            (string) ($appointment['appointment_date'] ?? ''),
            (string) ($appointment['appointment_time'] ?? '')
        );

        try { $templateVars = $this->buildAppointmentTemplateVars($patient, $appointment); }
        catch (\DomainException $e) { return $this->skipped('INVALID_CLINIC_SETTINGS_OR_DATE'); }
        return $this->enqueueWorkflowTemplate(
            $clinicId,
            $patient,
            'appointment_confirmation',
            $templateVars,
            $workflowKey,
            $actorUserId
        );
    }

    public function queueFollowUpForCompletedExamination(int $clinicId, int $examinationId, ?int $actorUserId = null): array
    {
        if (!$this->isTriggerEnabled($clinicId, 'follow_up')) {
            return $this->skipped('TRIGGER_DISABLED');
        }

        $examination = $this->examinationModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find($examinationId);
        if (!$examination) {
            return $this->skipped('EXAMINATION_NOT_FOUND');
        }

        if ((string) ($examination['status'] ?? '') !== 'completed') {
            return $this->skipped('EXAMINATION_NOT_COMPLETED');
        }

        $patient = $this->patientModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find((int) ($examination['patient_id'] ?? 0));
        if (!$patient) {
            return $this->skipped('PATIENT_NOT_FOUND');
        }

        try { $templateVars = ClinicWorkflowFormatter::forClinic($this->db, $clinicId)->examinationVars($patient, $examination, $this->buildClinicTemplateVars($clinicId)); }
        catch (\DomainException $e) { return $this->skipped('INVALID_CLINIC_SETTINGS_OR_DATE'); }
        $workflowKey = sprintf('follow_up:%d', $examinationId);

        return $this->enqueueWorkflowTemplate(
            $clinicId,
            $patient,
            'follow_up',
            $templateVars,
            $workflowKey,
            $actorUserId
        );
    }

    public function queueNoShowFollowUp(int $clinicId, int $appointmentId, ?int $actorUserId = null): array
    {
        if (!$this->isTriggerEnabled($clinicId, 'no_show_follow_up')) {
            return $this->skipped('TRIGGER_DISABLED');
        }

        $appointment = $this->appointmentModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find($appointmentId);
        if (!$appointment) {
            return $this->skipped('APPOINTMENT_NOT_FOUND');
        }

        if ((string) ($appointment['status'] ?? '') !== 'no_show') {
            return $this->skipped('APPOINTMENT_NOT_NO_SHOW');
        }

        $patient = $this->patientModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find((int) ($appointment['patient_id'] ?? 0));
        if (!$patient) {
            return $this->skipped('PATIENT_NOT_FOUND');
        }

        try { $today = ClinicWorkflowFormatter::forClinic($this->db, $clinicId)->currentDate($this->clock); }
        catch (\DomainException $e) { return $this->skipped('INVALID_CLINIC_SETTINGS'); }
        $workflowKey = sprintf('no_show_follow_up:%d:%s', $appointmentId, $today);

        try { $templateVars = $this->buildAppointmentTemplateVars($patient, $appointment); }
        catch (\DomainException $e) { return $this->skipped('INVALID_CLINIC_SETTINGS_OR_DATE'); }
        return $this->enqueueWorkflowTemplate(
            $clinicId,
            $patient,
            'no_show_follow_up',
            $templateVars,
            $workflowKey,
            $actorUserId
        );
    }

    public function queueInvoicePaymentReminder(int $clinicId, int $financeId, ?int $actorUserId = null, ?string $workflowSuffix = null): array
    {
        if (!$this->isTriggerEnabled($clinicId, 'payment_reminder')) {
            return $this->skipped('TRIGGER_DISABLED');
        }

        $finance = $this->financeModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find($financeId);
        if (!$finance) {
            return $this->skipped('INVOICE_NOT_FOUND');
        }

        if ((string) ($finance['transaction_type'] ?? '') !== 'invoice') {
            return $this->skipped('FINANCE_NOT_INVOICE');
        }

        $balanceDue = round(floatval($finance['balance_due'] ?? 0), 2);
        if ($balanceDue <= 0) {
            return $this->skipped('INVOICE_ALREADY_SETTLED');
        }

        $patient = $this->patientModel->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->find((int) ($finance['patient_id'] ?? 0));
        if (!$patient) {
            return $this->skipped('PATIENT_NOT_FOUND');
        }

        try {
            $formatter = new InvoiceReminderFormatter($this->db);
            $workflowKey = sprintf('payment_reminder:%d:%s', $financeId, $workflowSuffix ?: $formatter->currentDate($clinicId));
            $amountFormatted = $formatter->formatAmount($clinicId, $balanceDue);
            $dueDateFormatted = $formatter->formatDueDate($clinicId, !empty($finance['due_date']) ? (string) $finance['due_date'] : null);
        } catch (\Throwable $e) {
            return $this->skipped('INVALID_CLINIC_SETTINGS');
        }

        return $this->enqueueWorkflowTemplate(
            $clinicId,
            $patient,
            'payment_reminder',
            [
                'patient_name' => trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))),
                'amount' => $amountFormatted,
                'due_date' => $dueDateFormatted,
                'invoice_number' => (string) ($finance['transaction_id'] ?? ('INV-' . $financeId)),
            ] + $this->buildClinicTemplateVars($clinicId),
            $workflowKey,
            $actorUserId
        );
    }

    public function queueDueAppointmentReminders(?int $clinicId = null, ?int $actorUserId = null): array
    {
        $clinicIds = $this->resolveClinicIds($clinicId);
        $summary = ['clinics' => 0, 'scanned' => 0, 'queued' => 0, 'skipped' => 0];

        foreach ($clinicIds as $currentClinicId) {
            $summary['clinics']++;
            if (!$this->isTriggerEnabled($currentClinicId, 'appointment_reminder')) {
                continue;
            }

            $leadMinutes = $this->resolveReminderLeadMinutes($currentClinicId);
            try {
                $formatter = ClinicWorkflowFormatter::forClinic($this->db, $currentClinicId);
                [$windowStart, $windowEnd] = $formatter->discoveryDates($leadMinutes, $this->clock);
            } catch (\DomainException $e) { $summary['skipped']++; continue; }

            $rows = $this->db->query(
                'SELECT id, clinic_id, patient_id, appointment_date, appointment_time, status
                 FROM appointments
                 WHERE clinic_id = ?
                   AND status = ?
                   AND appointment_date BETWEEN ? AND ?',
                [$currentClinicId, 'scheduled', $windowStart, $windowEnd]
            )->getResultArray();

            foreach ($rows as $row) {
                $summary['scanned']++;
                try {
                    if (!$formatter->reminderEligible($row, $leadMinutes, $this->clock)) { $summary['skipped']++; continue; }
                } catch (\DomainException $e) { $summary['skipped']++; continue; }
                $patient = $this->patientModel->withoutTenantScope()
                    ->where('clinic_id', $currentClinicId)
                    ->find((int) ($row['patient_id'] ?? 0));
                if (!$patient) {
                    $summary['skipped']++;
                    continue;
                }

                $workflowKey = sprintf(
                    'appointment_reminder:%d:%s:%s',
                    (int) ($row['id'] ?? 0),
                    (string) ($row['appointment_date'] ?? ''),
                    (string) ($row['appointment_time'] ?? '')
                );

                $result = $this->enqueueWorkflowTemplate(
                    $currentClinicId,
                    $patient,
                    'appointment_reminder',
                    $this->buildAppointmentTemplateVars($patient, $row),
                    $workflowKey,
                    $actorUserId
                );

                if (!empty($result['queued'])) {
                    $summary['queued']++;
                } else {
                    $summary['skipped']++;
                }
            }
        }

        return $summary;
    }

    public function queueDueInvoicePaymentReminders(?int $clinicId = null, ?int $actorUserId = null): array
    {
        $clinicIds = $this->resolveClinicIds($clinicId);
        $summary = ['clinics' => 0, 'scanned' => 0, 'queued' => 0, 'skipped' => 0];
        $formatter = new InvoiceReminderFormatter($this->db);

        foreach ($clinicIds as $currentClinicId) {
            $summary['clinics']++;
            try { $today = $formatter->currentDate($currentClinicId); }
            catch (\DomainException $e) { $summary['skipped']++; continue; }

            $rows = $this->db->query(
                'SELECT id
                 FROM finances
                 WHERE clinic_id = ?
                   AND transaction_type = ?
                   AND balance_due > 0
                   AND payment_status IN (?, ?, ?)
                   AND due_date IS NOT NULL
                   AND due_date <= ?',
                [$currentClinicId, 'invoice', 'pending', 'partial', 'overdue', $today]
            )->getResultArray();

            foreach ($rows as $row) {
                $summary['scanned']++;
                $result = $this->queueInvoicePaymentReminder($currentClinicId, (int) ($row['id'] ?? 0), $actorUserId, $today);
                if (!empty($result['queued'])) {
                    $summary['queued']++;
                } else {
                    $summary['skipped']++;
                }
            }
        }

        return $summary;
    }

    private function enqueueWorkflowTemplate(
        int $clinicId,
        array $patient,
        string $triggerKey,
        array $templateVars,
        string $workflowKey,
        ?int $actorUserId = null
    ): array {
        $patientId = (int) ($patient['id'] ?? 0);
        if ($clinicId <= 0 || $patientId <= 0) {
            return $this->skipped('INVALID_TEMPLATE_CONTEXT');
        }

        if ($this->workflowAlreadyQueued($clinicId, $patientId, $workflowKey)) {
            return $this->skipped('DUPLICATE_WORKFLOW');
        }

        $workflowPayload = $this->buildTriggerPayload($clinicId, $triggerKey, $templateVars);
        if (!$workflowPayload['ok']) {
            return $this->skipped((string) ($workflowPayload['reason'] ?? 'TRIGGER_TEMPLATE_INVALID'));
        }

        try {
            $result = $this->notificationService->enqueue(
                $clinicId,
                'whatsapp',
                [[
                    'type' => 'patient',
                    'id' => $patientId,
                    'address' => null,
                ]],
                $workflowPayload['payload'] + ['workflow_key' => $workflowKey],
                $actorUserId
            );

            return [
                'queued' => !empty($result['notification_ids']),
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            log_message('warning', 'WhatsApp workflow enqueue skipped for clinic_id={clinic_id} patient_id={patient_id} trigger={trigger}: {message}', [
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'trigger' => $triggerKey,
                'message' => substr($e->getMessage(), 0, 255),
            ]);

            return $this->skipped('ENQUEUE_EXCEPTION');
        }
    }

    private function workflowAlreadyQueued(int $clinicId, int $patientId, string $workflowKey): bool
    {
        return $this->notificationModel
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('channel_type', 'whatsapp')
            ->where('recipient_type', 'patient')
            ->where('recipient_id', $patientId)
            ->where("payload_json LIKE '%\"workflow_key\":\"" . addslashes($workflowKey) . "\"%'", null, false)
            ->first() !== null;
    }

    private function buildAppointmentTemplateVars(array $patient, array $appointment): array
    {
        $clinicId = (int) ($appointment['clinic_id'] ?? 0);
        return ClinicWorkflowFormatter::forClinic($this->db, $clinicId)->appointmentVars($patient, $appointment, $this->buildClinicTemplateVars($clinicId));
    }

    private function buildClinicTemplateVars(int $clinicId): array
    {
        $vars = [
            'clinic_name' => '',
            'clinic_phone' => '',
            'clinic_address' => '',
        ];

        if ($clinicId <= 0) {
            return $vars;
        }

        $settingsRows = $this->settingsModel
            ->withoutTenantScope()
            ->select('setting_key, setting_value')
            ->where('clinic_id', $clinicId)
            ->whereIn('setting_key', ['clinic_name', 'clinic_phone', 'clinic_address'])
            ->findAll();

        foreach ($settingsRows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if (array_key_exists($key, $vars)) {
                $vars[$key] = trim((string) ($row['setting_value'] ?? ''));
            }
        }

        if ($vars['clinic_name'] === '') {
            $clinic = $this->clinicModel->find($clinicId);
            $vars['clinic_name'] = trim((string) ($clinic['name'] ?? ''));
        }

        return $vars;
    }

    private function resolveClinicIds(?int $clinicId = null): array
    {
        if ($clinicId !== null && $clinicId > 0) {
            return [$clinicId];
        }

        $rows = $this->clinicModel->select('id')->findAll();
        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }

    private function resolveReminderLeadMinutes(int $clinicId): int
    {
        $policy = $this->resolveTenantPolicy($clinicId);
        $leadMinutes = (int) ($policy['reminder_lead_time_minutes'] ?? 1440);
        return max(15, min($leadMinutes, 10080));
    }

    private function isTriggerEnabled(int $clinicId, string $triggerKey): bool
    {
        $defaults = $this->triggerCatalog->defaultTriggerToggles();
        if (!array_key_exists($triggerKey, $defaults)) {
            return true;
        }

        if (!$this->planGuard->isFeatureEnabled($clinicId, 'notifications.whatsapp.enabled', false)) {
            return false;
        }

        if (!$this->templateRegistry->isGlobalTriggerEnabled($triggerKey)) {
            return false;
        }

        $policy = $this->resolveTenantPolicy($clinicId);
        if ((int) ($policy['suspended'] ?? 0) === 1) {
            return false;
        }

        $toggles = $policy['trigger_toggles_json'] ?? [];
        if (is_string($toggles)) {
            $toggles = json_decode($toggles, true);
        }

        if (!is_array($toggles)) {
            $toggles = [];
        }

        return (int) ($toggles[$triggerKey] ?? $defaults[$triggerKey]) === 1;
    }

    private function resolveTenantPolicy(int $clinicId): array
    {
        if (!array_key_exists($clinicId, $this->policyCache)) {
            $this->policyCache[$clinicId] = $this->policyModel
                ->withoutTenantScope()
                ->where('clinic_id', $clinicId)
                ->first() ?? [];
        }

        return $this->policyCache[$clinicId];
    }

    private function defaultTriggerToggles(): array
    {
        return $this->triggerCatalog->defaultTriggerToggles();
    }

    private function buildTriggerPayload(int $clinicId, string $triggerKey, array $templateVars): array
    {
        $triggerTemplates = $this->templateRegistry->getGlobalTriggerTemplates();
        $triggerConfig = is_array($triggerTemplates[$triggerKey] ?? null) ? $triggerTemplates[$triggerKey] : null;
        if ($triggerConfig === null) {
            return ['ok' => false, 'reason' => 'TRIGGER_TEMPLATE_CONFIG_MISSING'];
        }

        $templateName = trim((string) ($triggerConfig['name'] ?? ''));
        if ($templateName === '') {
            return ['ok' => false, 'reason' => 'TRIGGER_TEMPLATE_NAME_MISSING'];
        }

        $templateLanguage = trim((string) ($triggerConfig['language'] ?? 'ar')) ?: 'ar';
        $templateComponents = $this->buildTemplateComponents($triggerConfig, $templateVars);

        return [
            'ok' => true,
            'payload' => [
                'workflow_trigger' => $triggerKey,
                'template_name' => $templateName,
                'template_language' => $templateLanguage,
                'template_components' => $templateComponents,
                'template_vars' => $templateVars,
                'template_source' => 'workflow_trigger',
            ],
        ];
    }

    private function buildTemplateComponents(array $triggerConfig, array $templateVars): array
    {
        return WorkflowTemplatePayload::components($triggerConfig, $templateVars);
    }

    private function formatMoney(float $amount): string
    {
        return settings()->getCurrencySymbol() . number_format($amount, 2);
    }

    private function skipped(string $reason): array
    {
        return [
            'queued' => false,
            'reason' => $reason,
        ];
    }
}
