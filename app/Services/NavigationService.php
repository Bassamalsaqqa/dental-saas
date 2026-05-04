<?php

namespace App\Services;

class NavigationService
{
    private const SESSION_KEY = 'nav_trail';
    private const MAX_STACK = 20;

    private array $skipSegments = ['auth', 'api', 'clinic', 'language', 'webhooks', 'assets', 'files'];

    public function push(string $label, string $url): void
    {
        $trail = $this->trail();
        $last = end($trail);
        if ($last && $last['url'] === $url) {
            return;
        }

        $trail[] = ['label' => $label, 'url' => $url, 'ts' => time()];

        if (count($trail) > self::MAX_STACK) {
            $trail = array_slice($trail, -self::MAX_STACK);
        }

        session()->set(self::SESSION_KEY, $trail);
    }

    public function pop(): ?array
    {
        $trail = $this->trail();
        array_pop($trail);
        session()->set(self::SESSION_KEY, $trail);

        $last = end($trail);
        return $last ?: null;
    }

    public function previous(): ?array
    {
        $trail = $this->trail();
        if (count($trail) < 2) {
            return null;
        }
        return $trail[count($trail) - 2] ?? null;
    }

    public function trail(): array
    {
        return session()->get(self::SESSION_KEY) ?? [];
    }

    public function shouldSkip(): bool
    {
        $uri = service('uri');
        $segments = $uri->getSegments();
        $first = $segments[0] ?? '';
        if (in_array($first, $this->skipSegments, true)) {
            return true;
        }
        if ($uri->isAJAX()) {
            return true;
        }
        if (service('request')->getMethod() !== 'get') {
            return true;
        }
        return false;
    }

    public function nextActions(string $currentRoute, array $context = []): array
    {
        $actions = [];

        // Patient detail → clinical actions
        if (preg_match('#^patient/\d+$#', $currentRoute)) {
            $id = $context['patient_id'] ?? null;
            if ($id) {
                if (has_permission('appointments', 'create')) {
                    $actions[] = ['label' => lang('UI.patientBookAppointment'), 'icon' => 'fa-calendar-plus', 'url' => base_url("appointment/create?patient_id={$id}")];
                }
                if (has_permission('examinations', 'create')) {
                    $actions[] = ['label' => lang('UI.patientStartAssessment'), 'icon' => 'fa-stethoscope', 'url' => base_url("examination/create?patient_id={$id}")];
                }
                if (has_permission('finance', 'create')) {
                    $actions[] = ['label' => lang('UI.patientCreateInvoice'), 'icon' => 'fa-file-invoice-dollar', 'url' => base_url("finance/create?patient_id={$id}")];
                }
            }
            return $actions;
        }

        // Examination → treatment plan / treatment / follow-up
        if (preg_match('#^examination/\d+$#', $currentRoute)) {
            $examId = $context['examination_id'] ?? null;
            $patientId = $context['patient_id'] ?? null;
            if ($examId && $patientId) {
                if (has_permission('treatments', 'create')) {
                    $actions[] = ['label' => lang('Examination.add_treatment_plan'), 'icon' => 'fa-file-signature', 'url' => base_url("contracts/create/{$patientId}?examination_id={$examId}")];
                    $actions[] = ['label' => lang('Examination.add_treatment'), 'icon' => 'fa-plus', 'url' => base_url("treatment/create?examination_id={$examId}")];
                }
                if (has_permission('appointments', 'create')) {
                    $actions[] = ['label' => lang('Examination.schedule_follow_up'), 'icon' => 'fa-calendar-plus', 'url' => base_url("appointment/create?patient_id={$patientId}&appointment_type=follow_up")];
                }
            }
            return $actions;
        }

        // Treatment → completion / printing
        if (preg_match('#^treatment/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Treatment.mark_as_completed'), 'icon' => 'fa-check-circle', 'url' => '#'];
            $actions[] = ['label' => lang('Treatment.print_treatment'), 'icon' => 'fa-print', 'url' => '#'];
            return $actions;
        }

        // Finance → payment / refund / export
        if (preg_match('#^finance/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Finance.recordInvoicePayment'), 'icon' => 'fa-plus-circle', 'url' => '#'];
            $actions[] = ['label' => lang('Finance.exportPdf'), 'icon' => 'fa-file-pdf', 'url' => '#'];
            return $actions;
        }

        // Prescription → print
        if (preg_match('#^prescription/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Prescription.print_prescription'), 'icon' => 'fa-print', 'url' => '#'];
            return $actions;
        }

        return $actions;
    }
}
