<?php

namespace App\Commands;

use App\Services\WhatsAppWorkflowService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WhatsAppQueueProductFlows extends BaseCommand
{
    protected $group = 'SaaS';
    protected $name = 'whatsapp:queue-product-flows';
    protected $description = 'Queues template-first WhatsApp reminders for real product workflows without sending directly.';
    protected $usage = 'whatsapp:queue-product-flows [--clinic-id 19] [--appointments] [--payments]';
    protected $options = [
        '--clinic-id' => 'Optional tenant clinic scope. Omit for all clinics.',
        '--appointments' => 'Queue appointment reminders only.',
        '--payments' => 'Queue invoice payment reminders only.',
    ];

    public function run(array $params)
    {
        $clinicId = (int) (CLI::getOption('clinic-id') ?? 0);
        $appointmentsOnly = CLI::getOption('appointments') !== null;
        $paymentsOnly = CLI::getOption('payments') !== null;

        $workflowService = new WhatsAppWorkflowService();

        $runAppointments = !$paymentsOnly;
        $runPayments = !$appointmentsOnly;

        $appointmentSummary = null;
        $paymentSummary = null;

        if ($runAppointments) {
            $appointmentSummary = $workflowService->queueDueAppointmentReminders($clinicId > 0 ? $clinicId : null);
        }

        if ($runPayments) {
            $paymentSummary = $workflowService->queueDueInvoicePaymentReminders($clinicId > 0 ? $clinicId : null);
        }

        CLI::write('WhatsApp Product Flow Queue Summary', 'cyan');
        CLI::write('- Scope: ' . ($clinicId > 0 ? 'clinic_id=' . $clinicId : 'all clinics'));

        if ($appointmentSummary !== null) {
            CLI::write(sprintf(
                '- Appointment reminders => clinics:%d scanned:%d queued:%d skipped:%d',
                $appointmentSummary['clinics'] ?? 0,
                $appointmentSummary['scanned'] ?? 0,
                $appointmentSummary['queued'] ?? 0,
                $appointmentSummary['skipped'] ?? 0
            ));
        }

        if ($paymentSummary !== null) {
            CLI::write(sprintf(
                '- Payment reminders => clinics:%d scanned:%d queued:%d skipped:%d',
                $paymentSummary['clinics'] ?? 0,
                $paymentSummary['scanned'] ?? 0,
                $paymentSummary['queued'] ?? 0,
                $paymentSummary['skipped'] ?? 0
            ));
        }
    }
}
