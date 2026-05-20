<?php
$patientId = (int) ($patient['id'] ?? 0);
$returnTo = 'patient/' . $patientId;
$isRtl = !empty($is_rtl);
$attachmentsEnabled = !empty($attachments_enabled);
$baseCurrency = (string) ($patient_command_base_currency ?? settings()->get('currency', 'ILS'));
$currencyOptions = $patient_command_currency_options ?? [$baseCurrency];
$chairOptions = $patient_command_chair_options ?? [];
$treatmentTypes = $patient_command_treatment_types ?? [];
$inventoryItems = $patient_command_inventory_items ?? [];
$clinicProviders = $patient_command_clinic_providers ?? [];
$clinicalChargeLines = $patient_command_clinical_charge_lines ?? [];
$liquidityOptions = $patient_command_liquidity_account_options ?? [];
$attachmentCategoryOptions = $attachment_category_options ?? [];
$maxFileMb = $attachments_max_file_mb ?? null;

$fullUrl = static function (string $path) use ($patientId, $returnTo): string {
    return base_url($path . '?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo));
};
$buttonClass = 'patient-command-action inline-flex min-h-[2.85rem] items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-sky-500/15';
$primaryButtonClass = 'patient-command-action patient-command-action-primary inline-flex min-h-[2.85rem] items-center justify-center gap-2 rounded-xl border border-teal-700 bg-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-800 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-teal-500/20';
$footer = static function (string $drawerId, string $submitLabel, string $fullPageUrl, string $formId): string {
    ob_start();
    ?>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="<?= esc($fullPageUrl) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-sky-700 hover:bg-sky-50">
            <i class="fas fa-up-right-from-square"></i>
            <span><?= esc(lang('UI.openFullPage')) ?></span>
        </a>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" data-app-drawer-close="<?= esc($drawerId) ?>"><?= esc(lang('UI.cancel')) ?></button>
            <button type="submit" form="<?= esc($formId) ?>" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-800"><?= esc($submitLabel) ?></button>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
};
$formWrap = static function (string $formId, string $action, string $body, ?string $returnTo = null, bool $multipart = false, string $attrs = ''): string {
    ob_start();
    ?>
    <form id="<?= esc($formId) ?>" action="<?= esc($action) ?>" method="post" <?= $multipart ? 'enctype="multipart/form-data"' : '' ?> class="space-y-5" <?= $attrs ?>>
        <?= csrf_field() ?>
        <?php if ($returnTo): ?><input type="hidden" name="return_to" value="<?= esc($returnTo) ?>"><?php endif; ?>
        <?= $body ?>
    </form>
    <?php
    return (string) ob_get_clean();
};
?>

<section class="patient-command-workspace overflow-hidden rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-teal-50 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.45)]">
    <div class="space-y-4 px-5 py-4">
        <div class="text-center">
            <h2 class="text-base font-bold text-slate-950"><?= esc(lang('UI.patientCommandActions')) ?></h2>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-2">
            <?php if (has_permission('examinations', 'create')): ?>
                <button type="button" class="<?= esc($primaryButtonClass) ?>" data-app-drawer-open="patientExamDrawer">
                    <i class="fas fa-stethoscope"></i><span><?= esc(lang('UI.addExamination')) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('appointments', 'create')): ?>
                <button type="button" class="<?= esc($buttonClass) ?>" data-app-drawer-open="patientAppointmentDrawer">
                    <i class="fas fa-calendar-plus text-emerald-600"></i><span><?= esc(lang('UI.newAppointment')) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('treatments', 'create')): ?>
                <button type="button" class="<?= esc($buttonClass) ?>" data-app-drawer-open="patientTreatmentDrawer">
                    <i class="fas fa-tooth text-purple-600"></i><span><?= esc(lang('UI.addTreatment')) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('finance', 'create')): ?>
                <button type="button" class="<?= esc($buttonClass) ?>" data-app-drawer-open="patientPaymentDrawer">
                    <i class="fas fa-money-bill-wave text-green-600"></i><span><?= esc(lang('UI.recordPayment')) ?></span>
                </button>
                <button type="button" class="<?= esc($buttonClass) ?>" data-app-drawer-open="patientChargeDrawer">
                    <i class="fas fa-file-invoice-dollar text-amber-600"></i><span><?= esc(lang('UI.addPatientCharge')) ?></span>
                </button>
            <?php endif; ?>
            <?php if ($attachmentsEnabled && has_permission('patients', 'edit')): ?>
                <button type="button" class="<?= esc($buttonClass) ?>" data-app-drawer-open="patientDocumentDrawer">
                    <i class="fas fa-paperclip text-cyan-600"></i><span><?= esc(lang('UI.addDocument')) ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (has_permission('examinations', 'create')): ?>
    <?php
    $examFormId = 'patientExamDrawerForm';
    $body = $formWrap($examFormId, base_url('examination/store'), view('examination/partials/form_fields', [
        'contextual' => true,
        'selected_patient' => $patient,
        'validation' => session()->getFlashdata('validation'),
        'is_rtl' => $isRtl,
    ]), $returnTo);
    ?>
    <?= view('components/app_drawer', [
        'id' => 'patientExamDrawer',
        'title' => lang('UI.addExamination'),
        'icon' => 'fas fa-stethoscope',
        'tone' => 'bg-sky-600',
        'size' => 'form',
        'body' => $body,
        'footer' => $footer('patientExamDrawer', lang('Examination.create_examination'), $fullUrl('examination/create'), $examFormId),
    ]) ?>
<?php endif; ?>

<?php if (has_permission('appointments', 'create')): ?>
    <?php
    $appointmentFormId = 'patientAppointmentDrawerForm';
    $body = $formWrap($appointmentFormId, base_url('appointment/store'), view('appointment/partials/form_fields', [
        'contextual' => true,
        'selected_patient' => $patient,
        'chair_options' => $chairOptions,
        'requested_date' => date('Y-m-d'),
        'is_rtl' => $isRtl,
    ]), $returnTo);
    ?>
    <?= view('components/app_drawer', [
        'id' => 'patientAppointmentDrawer',
        'title' => lang('UI.newAppointment'),
        'icon' => 'fas fa-calendar-plus',
        'tone' => 'bg-emerald-600',
        'size' => 'form',
        'body' => $body,
        'footer' => $footer('patientAppointmentDrawer', lang('UI.appointmentSchedule'), $fullUrl('appointment/create'), $appointmentFormId),
    ]) ?>
<?php endif; ?>

<?php if (has_permission('treatments', 'create')): ?>
    <?php
    $treatmentFormId = 'patientTreatmentDrawerForm';
    $body = $formWrap($treatmentFormId, base_url('treatment/store'), view('treatment/partials/form_fields', [
        'contextual' => true,
        'selected_patient' => $patient,
        'patients' => [$patient],
        'treatment_types' => $treatmentTypes,
        'inventory_items' => $inventoryItems,
        'clinic_providers' => $clinicProviders,
        'validation' => null,
        'is_rtl' => $isRtl,
        'id_prefix' => 'patient_treatment_charge',
    ]), $returnTo);
    ?>
    <?= view('components/app_drawer', [
        'id' => 'patientTreatmentDrawer',
        'title' => lang('UI.addTreatment'),
        'icon' => 'fas fa-tooth',
        'tone' => 'bg-purple-600',
        'size' => 'form',
        'body' => $body,
        'footer' => $footer('patientTreatmentDrawer', lang('Treatment.create_treatment'), $fullUrl('treatment/create'), $treatmentFormId),
    ]) ?>
<?php endif; ?>

<?php if (has_permission('finance', 'create')): ?>
    <?php foreach ([
        'patientPaymentDrawer' => ['payment', lang('UI.recordPayment')],
        'patientChargeDrawer' => ['invoice', lang('UI.addPatientCharge')],
    ] as $drawerId => $config): ?>
        <?php
        [$type, $title] = $config;
        $financeFormId = $drawerId . 'Form';
        $body = $formWrap($financeFormId, base_url('finance/store'), view('finance/partials/form_fields', [
            'contextual' => true,
            'id_prefix' => $drawerId,
            'selected_patient' => $patient,
            'patients' => [$patient],
            'transaction_type_preset' => $type,
            'lock_transaction_type' => true,
            'base_currency' => $baseCurrency,
            'currency_options' => $currencyOptions,
            'liquidity_account_options' => $liquidityOptions,
            'clinic_providers' => $clinicProviders,
            'clinical_charge_lines' => $type === 'invoice' ? $clinicalChargeLines : [],
            'is_rtl' => $isRtl,
        ]), $returnTo, true, 'data-finance-calc data-provider-split data-base-currency="' . esc($baseCurrency) . '"');
        ?>
        <?= view('components/app_drawer', [
            'id' => $drawerId,
            'title' => $title,
            'icon' => 'fas fa-file-invoice-dollar',
            'tone' => 'bg-amber-600',
            'size' => 'wide',
            'body' => $body,
            'footer' => $footer($drawerId, lang('Finance.createTransaction'), $fullUrl('finance/create'), $financeFormId),
        ]) ?>
    <?php endforeach; ?>
    <script src="<?= base_url('assets/js/finance-calc.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>

<?php if ($attachmentsEnabled && has_permission('patients', 'edit')): ?>
    <?php
    $documentFormId = 'patientDocumentDrawerForm';
    $body = $formWrap($documentFormId, base_url('patient/' . $patientId . '/attachments/upload'), view('patient/partials/attachment_form_fields', [
        'patient' => $patient,
        'attachment_category_options' => $attachmentCategoryOptions,
        'max_file_mb' => $maxFileMb,
    ]), $returnTo, true);
    ?>
    <?= view('components/app_drawer', [
        'id' => 'patientDocumentDrawer',
        'title' => lang('UI.addDocument'),
        'icon' => 'fas fa-paperclip',
        'tone' => 'bg-cyan-600',
        'size' => 'form',
        'body' => $body,
        'footer' => $footer('patientDocumentDrawer', lang('UI.patientUploadFiles'), base_url('patient/' . $patientId . '?tab=attachments'), $documentFormId),
    ]) ?>
<?php endif; ?>
