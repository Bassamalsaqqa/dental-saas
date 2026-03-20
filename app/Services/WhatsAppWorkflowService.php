<?php

namespace App\Services;

use App\Models\AppointmentModel;
use App\Models\ClinicModel;
use App\Models\ExaminationModel;
use App\Models\FinanceModel;
use App\Models\NotificationModel;
use App\Models\PatientModel;
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
    protected WhatsAppTenantPolicyModel $policyModel;
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->notificationModel = new NotificationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->examinationModel = new ExaminationModel();
        $this->financeModel = new FinanceModel();
        $this->patientModel = new PatientModel();
        $this->clinicModel = new ClinicModel();
        $this->policyModel = new WhatsAppTenantPolicyModel();
        $this->db = \Config\Database::connect();
    }

    public function queueAppointmentConfirmation(int $clinicId, int $appointmentId, ?int $actorUserId = null): array
    {
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

        return $this->enqueuePatientTemplate(
            $clinicId,
            $patient,
            'appointment_confirmation',
            $this->buildAppointmentTemplateVars($patient, $appointment),
            $workflowKey,
            $actorUserId
        );
    }

    public function queueFollowUpForCompletedExamination(int $clinicId, int $examinationId, ?int $actorUserId = null): array
    {
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

        $workflowKey = sprintf('follow_up:%d', $examinationId);

        return $this->enqueuePatientTemplate(
            $clinicId,
            $patient,
            'follow_up',
            [
                'patient_name' => trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))),
                'examination_date' => formatDate((string) ($examination['examination_date'] ?? '')),
            ],
            $workflowKey,
            $actorUserId
        );
    }

    public function queueInvoicePaymentReminder(int $clinicId, int $financeId, ?int $actorUserId = null, ?string $workflowSuffix = null): array
    {
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

        $workflowKey = sprintf(
            'payment_reminder:%d:%s',
            $financeId,
            $workflowSuffix ?: date('Y-m-d')
        );

        return $this->enqueuePatientTemplate(
            $clinicId,
            $patient,
            'payment_reminder',
            [
                'patient_name' => trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))),
                'amount' => $this->formatMoney($balanceDue),
                'due_date' => !empty($finance['due_date']) ? formatDate((string) $finance['due_date']) : '',
                'invoice_number' => (string) ($finance['transaction_id'] ?? ('INV-' . $financeId)),
            ],
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
            $leadMinutes = $this->resolveReminderLeadMinutes($currentClinicId);
            $windowStart = date('Y-m-d H:i:s');
            $windowEnd = date('Y-m-d H:i:s', time() + ($leadMinutes * 60));

            $rows = $this->db->query(
                'SELECT id, clinic_id, patient_id, appointment_date, appointment_time, status
                 FROM appointments
                 WHERE clinic_id = ?
                   AND status = ?
                   AND TIMESTAMP(appointment_date, appointment_time) BETWEEN ? AND ?',
                [$currentClinicId, 'scheduled', $windowStart, $windowEnd]
            )->getResultArray();

            foreach ($rows as $row) {
                $summary['scanned']++;
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

                $result = $this->enqueuePatientTemplate(
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
        $today = date('Y-m-d');

        foreach ($clinicIds as $currentClinicId) {
            $summary['clinics']++;

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

    private function enqueuePatientTemplate(
        int $clinicId,
        array $patient,
        string $templateKey,
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

        try {
            $result = $this->notificationService->enqueue(
                $clinicId,
                'whatsapp',
                [[
                    'type' => 'patient',
                    'id' => $patientId,
                    'address' => null,
                ]],
                [
                    'message_template_key' => $templateKey,
                    'template_vars' => $templateVars,
                    'workflow_key' => $workflowKey,
                ],
                $actorUserId
            );

            return [
                'queued' => !empty($result['notification_ids']),
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            log_message('warning', 'WhatsApp workflow enqueue skipped for clinic_id={clinic_id} patient_id={patient_id} template={template}: {message}', [
                'clinic_id' => $clinicId,
                'patient_id' => $patientId,
                'template' => $templateKey,
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
        return [
            'patient_name' => trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))),
            'appointment_date' => formatDate((string) ($appointment['appointment_date'] ?? '')),
            'appointment_time' => formatTime((string) ($appointment['appointment_time'] ?? '')),
        ];
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
        $policy = $this->policyModel->withoutTenantScope()->where('clinic_id', $clinicId)->first();
        $leadMinutes = (int) ($policy['reminder_lead_time_minutes'] ?? 1440);
        return max(15, min($leadMinutes, 10080));
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
