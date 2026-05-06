<?php
$locale = service('request')->getLocale();
$isRtl = !empty($is_rtl);
$patientId = (int) ($patient['id'] ?? 0);
$patientName = trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')));
$patientNumber = (string) ($patient['patient_id'] ?? ('#' . $patientId));
$patientPhone = (string) ($patient['phone'] ?? '');
$returnTo = 'patient/' . $patientId;
$baseCurrency = (string) ($patient_command_base_currency ?? settings()->get('currency', 'ILS'));
$currencyOptions = $patient_command_currency_options ?? [$baseCurrency];
$chairOptions = $patient_command_chair_options ?? [];
$treatmentTypes = $patient_command_treatment_types ?? [];
$attachmentsEnabled = !empty($attachments_enabled);
$attachmentCategoryOptions = $attachment_category_options ?? [];
$today = date('Y-m-d');
$durationOptions = [30, 45, 60, 90, 120, 180];
$defaultDuration = (int) getAppointmentDuration();
if (!in_array($defaultDuration, $durationOptions, true) && $defaultDuration > 0) {
    $durationOptions[] = $defaultDuration;
    sort($durationOptions, SORT_NUMERIC);
}

$copy = $locale === 'ar' ? [
    'workspace' => 'إجراءات المريض',
    'workspaceHelp' => 'ابدأ عملاً مرتبطاً بهذا المريض بدون مغادرة ملفه. التفاصيل الكاملة تبقى في صفحاتها الأصلية.',
    'context' => 'سياق المريض',
    'fullWorkflow' => 'افتح الصفحة الكاملة',
    'cancel' => 'إلغاء',
    'save' => 'حفظ',
    'create' => 'إنشاء',
    'required' => 'مطلوب',
    'optional' => 'اختياري',
    'patientLocked' => 'سيتم ربط هذا الإجراء بالمريض الحالي.',
    'advancedFallback' => 'إذا احتجت تفاصيل أكثر، استخدم الصفحة الكاملة.',
    'addExam' => 'إضافة فحص',
    'addExamHelp' => 'سجل سبب الزيارة ونوع الفحص. يمكن إكمال التفاصيل السريرية لاحقاً من صفحة الفحص.',
    'examType' => 'نوع الفحص',
    'examDate' => 'تاريخ الفحص',
    'chiefComplaint' => 'سبب الزيارة',
    'examNotes' => 'ملاحظات الفحص',
    'initial' => 'فحص أولي',
    'periodic' => 'فحص دوري',
    'emergency' => 'طارئ',
    'followUp' => 'متابعة',
    'newAppointment' => 'موعد جديد',
    'newAppointmentHelp' => 'المريض محدد مسبقاً. اختر الطبيب/الكرسي والوقت المناسب.',
    'appointmentType' => 'نوع الموعد',
    'doctorChair' => 'الطبيب / الكرسي',
    'date' => 'التاريخ',
    'time' => 'الوقت',
    'duration' => 'المدة',
    'notes' => 'ملاحظات',
    'select' => 'اختر',
    'loadingSlots' => 'جاري تحميل الأوقات...',
    'noSlots' => 'لا توجد أوقات متاحة',
    'addTreatment' => 'إضافة علاج',
    'addTreatmentHelp' => 'أنشئ بند علاج مرتبط بالمريض. التسعير والتفاصيل المتقدمة تبقى في صفحة العلاج.',
    'treatmentType' => 'نوع العلاج',
    'tooth' => 'رقم السن',
    'description' => 'الوصف',
    'cost' => 'تكلفة تقديرية',
    'estimatedDuration' => 'المدة بالأيام',
    'recordPayment' => 'تسجيل دفعة',
    'addCharge' => 'إضافة مطالبة على المريض',
    'financeHelp' => 'إجراء مالي للمريض فقط. الحسابات والتسوية الكاملة تبقى في المالية.',
    'amount' => 'المبلغ',
    'method' => 'الطريقة',
    'serviceType' => 'نوع الخدمة',
    'paymentDate' => 'تاريخ الدفع',
    'dueDate' => 'تاريخ الاستحقاق',
    'patientCharge' => 'مطالبة على المريض',
    'document' => 'إضافة مستند',
    'documentHelp' => 'ارفع مستنداً إلى تخزين العيادة الخاص بالمريض.',
    'file' => 'الملف',
    'category' => 'التصنيف',
    'upload' => 'رفع',
    'minutes' => 'دقيقة',
] : [
    'workspace' => 'Patient actions',
    'workspaceHelp' => 'Start work linked to this patient without leaving the profile. Full workflows still belong to their modules.',
    'context' => 'Patient context',
    'fullWorkflow' => 'Open full page',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'create' => 'Create',
    'required' => 'Required',
    'optional' => 'Optional',
    'patientLocked' => 'This action will be linked to the current patient.',
    'advancedFallback' => 'Use the full page when you need advanced details.',
    'addExam' => 'Add examination',
    'addExamHelp' => 'Capture the visit reason and exam type. Clinical details can be completed from the full examination page.',
    'examType' => 'Examination type',
    'examDate' => 'Examination date',
    'chiefComplaint' => 'Reason for visit',
    'examNotes' => 'Examination notes',
    'initial' => 'Initial',
    'periodic' => 'Periodic',
    'emergency' => 'Emergency',
    'followUp' => 'Follow-up',
    'newAppointment' => 'New appointment',
    'newAppointmentHelp' => 'The patient is already selected. Choose the doctor/chair and available time.',
    'appointmentType' => 'Appointment type',
    'doctorChair' => 'Doctor / chair',
    'date' => 'Date',
    'time' => 'Time',
    'duration' => 'Duration',
    'notes' => 'Notes',
    'select' => 'Select',
    'loadingSlots' => 'Loading times...',
    'noSlots' => 'No available times',
    'addTreatment' => 'Add treatment',
    'addTreatmentHelp' => 'Create a treatment item linked to this patient. Advanced pricing and inventory stay in the treatment page.',
    'treatmentType' => 'Treatment type',
    'tooth' => 'Tooth number',
    'description' => 'Description',
    'cost' => 'Estimated cost',
    'estimatedDuration' => 'Duration in days',
    'recordPayment' => 'Record payment',
    'addCharge' => 'Add patient charge',
    'financeHelp' => 'Patient-level finance only. Full reconciliation stays in Finance.',
    'amount' => 'Amount',
    'method' => 'Method',
    'serviceType' => 'Service type',
    'paymentDate' => 'Payment date',
    'dueDate' => 'Due date',
    'patientCharge' => 'Patient charge',
    'document' => 'Add document',
    'documentHelp' => 'Upload a document to this patient using private clinic storage.',
    'file' => 'File',
    'category' => 'Category',
    'upload' => 'Upload',
    'minutes' => 'minutes',
];

$appointmentTypes = [
    'consultation' => $locale === 'ar' ? 'استشارة' : 'Consultation',
    'treatment' => $locale === 'ar' ? 'علاج' : 'Treatment',
    'follow_up' => $copy['followUp'],
    'emergency' => $copy['emergency'],
    'cleaning' => $locale === 'ar' ? 'تنظيف' : 'Cleaning',
    'checkup' => $locale === 'ar' ? 'مراجعة' : 'Checkup',
];
$serviceTypes = [
    'consultation' => $appointmentTypes['consultation'],
    'treatment' => $appointmentTypes['treatment'],
    'medication' => $locale === 'ar' ? 'دواء' : 'Medication',
    'procedure' => $locale === 'ar' ? 'إجراء' : 'Procedure',
    'other' => $locale === 'ar' ? 'أخرى' : 'Other',
];
$paymentMethods = [
    'cash' => lang('Finance.cash'),
    'card' => lang('Finance.card'),
    'bank_transfer' => lang('Finance.bankTransfer'),
    'check' => lang('Finance.check'),
    'insurance' => lang('Finance.insurance') !== 'Finance.insurance' ? lang('Finance.insurance') : ($locale === 'ar' ? 'تأمين' : 'Insurance'),
    'other' => lang('Finance.other'),
];
?>

<section class="patient-command-workspace rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.35)]">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900"><?= esc($copy['workspace']) ?></h2>
            <p class="mt-1 text-sm text-slate-500"><?= esc($copy['workspaceHelp']) ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if (has_permission('examinations', 'create')): ?>
                <button type="button" class="patient-command-btn patient-command-btn-primary" data-patient-drawer-open="patientExamDrawer">
                    <i class="fas fa-stethoscope"></i><span><?= esc($copy['addExam']) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('appointments', 'create')): ?>
                <button type="button" class="patient-command-btn" data-patient-drawer-open="patientAppointmentDrawer">
                    <i class="fas fa-calendar-plus text-emerald-600"></i><span><?= esc($copy['newAppointment']) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('treatments', 'create')): ?>
                <button type="button" class="patient-command-btn" data-patient-drawer-open="patientTreatmentDrawer">
                    <i class="fas fa-tooth text-purple-600"></i><span><?= esc($copy['addTreatment']) ?></span>
                </button>
            <?php endif; ?>
            <?php if (has_permission('finance', 'create')): ?>
                <button type="button" class="patient-command-btn" data-patient-drawer-open="patientPaymentDrawer">
                    <i class="fas fa-money-bill-wave text-green-600"></i><span><?= esc($copy['recordPayment']) ?></span>
                </button>
                <button type="button" class="patient-command-btn" data-patient-drawer-open="patientChargeDrawer">
                    <i class="fas fa-file-invoice-dollar text-amber-600"></i><span><?= esc($copy['addCharge']) ?></span>
                </button>
            <?php endif; ?>
            <?php if ($attachmentsEnabled && has_permission('patients', 'edit')): ?>
                <button type="button" class="patient-command-btn" data-patient-drawer-open="patientDocumentDrawer">
                    <i class="fas fa-paperclip text-cyan-600"></i><span><?= esc($copy['document']) ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid gap-3 px-5 py-4 md:grid-cols-3">
        <a href="<?= base_url('odontogram/' . $patientId) ?>" class="patient-command-link">
            <i class="fas fa-tooth text-cyan-600"></i><span><?= esc(lang('UI.patientOdontogram')) ?></span>
        </a>
        <a href="<?= base_url('examination/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo)) ?>" class="patient-command-link">
            <i class="fas fa-notes-medical text-sky-600"></i><span><?= esc($copy['fullWorkflow']) ?>: <?= esc($copy['addExam']) ?></span>
        </a>
        <a href="<?= base_url('finance/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo)) ?>" class="patient-command-link">
            <i class="fas fa-wallet text-amber-600"></i><span><?= esc($copy['fullWorkflow']) ?>: <?= esc(lang('UI.patientTabFinancials')) ?></span>
        </a>
    </div>
</section>

<?php
$drawerHeader = static function (string $id, string $title, string $description, string $iconClass, string $toneClass = 'patient-command-drawer-icon-blue') use ($copy, $patientName, $patientNumber, $patientPhone): void {
?>
<div id="<?= esc($id) ?>" class="patient-command-drawer hidden" role="dialog" aria-modal="true" aria-labelledby="<?= esc($id) ?>Title">
    <div class="patient-command-drawer-backdrop" data-patient-drawer-close="<?= esc($id) ?>"></div>
    <div class="patient-command-drawer-shell" data-patient-drawer-panel>
        <div class="patient-command-drawer-panel">
            <div class="patient-command-drawer-header">
                <div class="flex items-start gap-3">
                    <span class="patient-command-drawer-icon <?= esc($toneClass) ?>"><i class="<?= esc($iconClass) ?>"></i></span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"><?= esc($copy['context']) ?></p>
                        <h3 id="<?= esc($id) ?>Title" class="mt-1 text-xl font-bold text-slate-900"><?= esc($title) ?></h3>
                        <p class="mt-1 text-sm text-slate-500"><?= esc($description) ?></p>
                    </div>
                </div>
                <button type="button" class="patient-command-drawer-close" data-patient-drawer-close="<?= esc($id) ?>" aria-label="<?= esc($copy['cancel']) ?>">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="patient-command-context">
                <span class="patient-command-context-icon"><i class="fas fa-user"></i></span>
                <span>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400"><?= esc($copy['patientLocked']) ?></span>
                    <span class="block text-sm font-bold text-slate-900"><?= esc($patientName) ?> - <span dir="ltr"><?= esc($patientNumber) ?></span><?= $patientPhone !== '' ? ' - ' . esc($patientPhone) : '' ?></span>
                </span>
            </div>
<?php
};
$drawerFooter = static function (string $id, string $submitLabel, ?string $fullUrl = null) use ($copy): void {
?>
            <div class="patient-command-drawer-footer">
                <?php if ($fullUrl): ?>
                    <a href="<?= esc($fullUrl) ?>" class="patient-command-secondary-link">
                        <i class="fas fa-up-right-from-square"></i><span><?= esc($copy['fullWorkflow']) ?></span>
                    </a>
                <?php else: ?>
                    <span class="text-xs text-slate-500"><?= esc($copy['advancedFallback']) ?></span>
                <?php endif; ?>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" class="patient-command-cancel" data-patient-drawer-close="<?= esc($id) ?>"><?= esc($copy['cancel']) ?></button>
                    <button type="submit" class="patient-command-submit"><?= esc($submitLabel) ?></button>
                </div>
            </div>
<?php
};
$drawerClose = static function (): void {
?>
        </div>
    </div>
</div>
<?php
};
?>

<?php if (has_permission('examinations', 'create')): ?>
<?php $drawerHeader('patientExamDrawer', $copy['addExam'], $copy['addExamHelp'], 'fas fa-stethoscope', 'patient-command-drawer-icon-blue'); ?>
<form action="<?= base_url('examination/store') ?>" method="post" class="patient-command-drawer-form">
    <?= csrf_field() ?>
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
    <div class="patient-command-grid">
        <label class="patient-command-field"><span><?= esc($copy['examType']) ?> *</span><select name="examination_type" required><option value="initial"><?= esc($copy['initial']) ?></option><option value="periodic"><?= esc($copy['periodic']) ?></option><option value="emergency"><?= esc($copy['emergency']) ?></option><option value="follow_up"><?= esc($copy['followUp']) ?></option></select></label>
        <label class="patient-command-field"><span><?= esc($copy['examDate']) ?> *</span><input type="date" name="examination_date" value="<?= esc($today) ?>" required></label>
    </div>
    <label class="patient-command-field"><span><?= esc($copy['chiefComplaint']) ?> *</span><textarea name="chief_complaint" rows="3" required></textarea></label>
    <label class="patient-command-field"><span><?= esc($copy['examNotes']) ?></span><textarea name="examination_notes" rows="3"></textarea></label>
<?php $drawerFooter('patientExamDrawer', $copy['create'], base_url('examination/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo))); ?>
</form>
<?php $drawerClose(); ?>
<?php endif; ?>

<?php if (has_permission('appointments', 'create')): ?>
<?php $drawerHeader('patientAppointmentDrawer', $copy['newAppointment'], $copy['newAppointmentHelp'], 'fas fa-calendar-plus', 'patient-command-drawer-icon-green'); ?>
<form action="<?= base_url('appointment/store') ?>" method="post" class="patient-command-drawer-form">
    <?= csrf_field() ?>
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
    <div class="patient-command-grid">
        <label class="patient-command-field"><span><?= esc($copy['appointmentType']) ?> *</span><select name="appointment_type" required><?php foreach ($appointmentTypes as $value => $label): ?><option value="<?= esc($value) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['doctorChair']) ?> *</span><select name="chair_name" id="patientCommandChair" required><option value=""><?= esc($copy['select']) ?></option><?php foreach ($chairOptions as $chair): ?><option value="<?= esc($chair) ?>"><?= esc($chair) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['date']) ?> *</span><input type="date" name="appointment_date" id="patientCommandAppointmentDate" value="<?= esc($today) ?>" required></label>
        <label class="patient-command-field"><span><?= esc($copy['duration']) ?> *</span><select name="duration" id="patientCommandDuration" required><?php foreach ($durationOptions as $minutes): ?><option value="<?= (int) $minutes ?>" <?= (int) $minutes === $defaultDuration ? 'selected' : '' ?>><?= (int) $minutes ?> <?= esc($copy['minutes']) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['time']) ?> *</span><select name="appointment_time" id="patientCommandAppointmentTime" required><option value=""><?= esc($copy['select']) ?></option></select></label>
    </div>
    <label class="patient-command-field"><span><?= esc($copy['notes']) ?></span><textarea name="notes" rows="3"></textarea></label>
<?php $drawerFooter('patientAppointmentDrawer', $copy['create'], base_url('appointment/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo))); ?>
</form>
<?php $drawerClose(); ?>
<?php endif; ?>

<?php if (has_permission('treatments', 'create')): ?>
<?php $drawerHeader('patientTreatmentDrawer', $copy['addTreatment'], $copy['addTreatmentHelp'], 'fas fa-tooth', 'patient-command-drawer-icon-purple'); ?>
<form action="<?= base_url('treatment/store') ?>" method="post" class="patient-command-drawer-form">
    <?= csrf_field() ?>
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
    <div class="patient-command-grid">
        <label class="patient-command-field"><span><?= esc($copy['treatmentType']) ?> *</span><select name="treatment_type" required><option value=""><?= esc($copy['select']) ?></option><?php foreach ($treatmentTypes as $value => $label): ?><option value="<?= esc($value) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['tooth']) ?></span><input type="text" name="tooth_number" maxlength="10"></label>
        <label class="patient-command-field"><span><?= esc($copy['date']) ?> *</span><input type="date" name="start_date" value="<?= esc($today) ?>" required></label>
        <label class="patient-command-field"><span><?= esc($copy['estimatedDuration']) ?> *</span><input type="number" name="estimated_duration" value="1" min="1" required></label>
        <label class="patient-command-field"><span><?= esc($copy['cost']) ?></span><input type="number" name="cost" step="0.01" min="0"></label>
    </div>
    <label class="patient-command-field"><span><?= esc($copy['description']) ?> *</span><textarea name="description" rows="3" minlength="10" required></textarea></label>
<?php $drawerFooter('patientTreatmentDrawer', $copy['create'], base_url('treatment/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo))); ?>
</form>
<?php $drawerClose(); ?>
<?php endif; ?>

<?php if (has_permission('finance', 'create')): ?>
<?php foreach (['patientPaymentDrawer' => ['payment', $copy['recordPayment'], $copy['paymentDate']], 'patientChargeDrawer' => ['invoice', $copy['addCharge'], $copy['dueDate']]] as $drawerId => $financeConfig): ?>
<?php [$transactionType, $financeTitle, $dateLabel] = $financeConfig; ?>
<?php $drawerHeader($drawerId, $financeTitle, $copy['financeHelp'], 'fas fa-file-invoice-dollar', 'patient-command-drawer-icon-amber'); ?>
<form action="<?= base_url('finance/store') ?>" method="post" class="patient-command-drawer-form">
    <?= csrf_field() ?>
    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
    <input type="hidden" name="transaction_type" value="<?= esc($transactionType) ?>">
    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
    <input type="hidden" name="exchange_rate" value="1">
    <input type="hidden" name="tax_percentage" value="0">
    <div class="patient-command-grid">
        <label class="patient-command-field"><span><?= esc($copy['amount']) ?> *</span><input type="number" name="amount_original" step="0.01" min="0.01" required></label>
        <label class="patient-command-field"><span><?= esc(lang('Finance.currency')) ?> *</span><select name="currency" required><?php foreach ($currencyOptions as $currency): ?><option value="<?= esc($currency) ?>" <?= $currency === $baseCurrency ? 'selected' : '' ?>><?= esc($currency) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['method']) ?> *</span><select name="payment_method" required><?php foreach ($paymentMethods as $value => $label): ?><option value="<?= esc($value) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($copy['serviceType']) ?> *</span><select name="service_type" required><?php foreach ($serviceTypes as $value => $label): ?><option value="<?= esc($value) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <label class="patient-command-field"><span><?= esc($dateLabel) ?></span><input type="date" name="<?= $transactionType === 'invoice' ? 'due_date' : 'paid_date' ?>" value="<?= esc($today) ?>"></label>
    </div>
    <label class="patient-command-field"><span><?= esc($copy['description']) ?></span><textarea name="description" rows="3"></textarea></label>
<?php $drawerFooter($drawerId, $copy['create'], base_url('finance/create?patient_id=' . $patientId . '&return_to=' . urlencode($returnTo))); ?>
</form>
<?php $drawerClose(); ?>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($attachmentsEnabled && has_permission('patients', 'edit')): ?>
<?php $drawerHeader('patientDocumentDrawer', $copy['document'], $copy['documentHelp'], 'fas fa-paperclip', 'patient-command-drawer-icon-cyan'); ?>
<form action="<?= base_url('patient/' . $patientId . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" class="patient-command-drawer-form">
    <?= csrf_field() ?>
    <label class="patient-command-field"><span><?= esc($copy['file']) ?> *</span><input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip"></label>
    <label class="patient-command-field"><span><?= esc($copy['category']) ?></span><select name="category"><option value=""><?= esc($copy['select']) ?></option><?php foreach ($attachmentCategoryOptions as $groupLabel => $groupOptions): ?><optgroup label="<?= esc($groupLabel) ?>"><?php foreach ($groupOptions as $optionLabel): ?><option value="<?= esc($optionLabel) ?>"><?= esc($optionLabel) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></label>
    <label class="patient-command-field"><span><?= esc($copy['notes']) ?></span><textarea name="notes" rows="3"></textarea></label>
<?php $drawerFooter('patientDocumentDrawer', $copy['upload'], null); ?>
</form>
<?php $drawerClose(); ?>
<?php endif; ?>

<style>
.patient-command-workspace { overflow: hidden; }
.patient-command-btn, .patient-command-link { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.82rem; font-weight: 700; gap: 0.5rem; transition: all 0.18s ease; }
.patient-command-btn { background: #fff; border: 1px solid #e2e8f0; color: #334155; padding: 0.68rem 0.9rem; }
.patient-command-btn:hover, .patient-command-link:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); }
.patient-command-btn-primary { background: #0f766e; border-color: #0f766e; color: #fff; }
.patient-command-btn-primary:hover { background: #115e59; border-color: #115e59; }
.patient-command-link { border: 1px solid #e2e8f0; color: #475569; padding: 0.75rem 0.9rem; }
.patient-command-drawer { position: fixed; inset: 0; z-index: 80; }
.patient-command-drawer-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, 0.48); backdrop-filter: blur(4px); }
.patient-command-drawer-shell { position: relative; z-index: 1; display: flex; min-height: 100%; justify-content: flex-end; pointer-events: none; }
[dir="rtl"] .patient-command-drawer-shell { justify-content: flex-start; }
.patient-command-drawer-panel { background: #fff; box-shadow: 0 28px 80px rgba(15, 23, 42, 0.25); display: flex; flex-direction: column; max-width: 42rem; min-height: 100vh; pointer-events: auto; width: min(100%, 42rem); }
.patient-command-drawer-header { align-items: flex-start; border-bottom: 1px solid #e2e8f0; display: flex; gap: 1rem; justify-content: space-between; padding: 1.25rem; }
.patient-command-drawer-icon { align-items: center; border-radius: 1rem; color: #fff; display: inline-flex; flex-shrink: 0; height: 2.75rem; justify-content: center; width: 2.75rem; }
.patient-command-drawer-icon-blue { background: linear-gradient(135deg, #2563eb, #0891b2); }
.patient-command-drawer-icon-green { background: linear-gradient(135deg, #059669, #10b981); }
.patient-command-drawer-icon-purple { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.patient-command-drawer-icon-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
.patient-command-drawer-icon-cyan { background: linear-gradient(135deg, #0891b2, #06b6d4); }
.patient-command-drawer-close { align-items: center; border-radius: 999px; color: #64748b; display: inline-flex; height: 2.25rem; justify-content: center; width: 2.25rem; }
.patient-command-drawer-close:hover { background: #f1f5f9; color: #0f172a; }
.patient-command-context { align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; display: flex; gap: 0.75rem; margin: 1rem 1.25rem 0; padding: 0.85rem; }
.patient-command-context-icon { align-items: center; background: #e0f2fe; border-radius: 0.8rem; color: #0369a1; display: inline-flex; height: 2.25rem; justify-content: center; width: 2.25rem; }
.patient-command-drawer-form { display: flex; flex: 1; flex-direction: column; gap: 1rem; padding: 1.25rem; }
.patient-command-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.patient-command-field { display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.88rem; font-weight: 700; color: #334155; }
.patient-command-field input, .patient-command-field select, .patient-command-field textarea { border: 1px solid #cbd5e1; border-radius: 0.85rem; color: #0f172a; font-size: 0.92rem; font-weight: 500; padding: 0.72rem 0.85rem; width: 100%; }
.patient-command-field textarea { resize: vertical; }
.patient-command-drawer-footer { align-items: center; border-top: 1px solid #e2e8f0; display: flex; gap: 1rem; justify-content: space-between; margin-top: auto; padding-top: 1rem; }
.patient-command-secondary-link, .patient-command-cancel, .patient-command-submit { align-items: center; border-radius: 0.85rem; display: inline-flex; font-size: 0.88rem; font-weight: 800; gap: 0.45rem; padding: 0.7rem 1rem; }
.patient-command-secondary-link { color: #0369a1; }
.patient-command-cancel { border: 1px solid #cbd5e1; color: #475569; }
.patient-command-submit { background: #0f766e; color: #fff; }
@media (max-width: 640px) {
    .patient-command-grid { grid-template-columns: 1fr; }
    .patient-command-drawer-panel { max-width: 100%; }
    .patient-command-drawer-footer { align-items: stretch; flex-direction: column; }
}
</style>

<script>
(function () {
    const openDrawers = new Map();

    function focusableElements(root) {
        return Array.from(root.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
            .filter((element) => element.offsetParent !== null || element === document.activeElement);
    }

    function openDrawer(id, trigger) {
        const drawer = document.getElementById(id);
        if (!drawer) return;
        drawer.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        openDrawers.set(id, trigger || document.activeElement);
        window.setTimeout(() => {
            const focusable = focusableElements(drawer);
            (focusable.find((el) => ['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) || focusable[0])?.focus();
        }, 20);
    }

    function closeDrawer(id) {
        const drawer = document.getElementById(id);
        if (!drawer) return;
        drawer.classList.add('hidden');
        const hasOpen = Array.from(document.querySelectorAll('.patient-command-drawer')).some((item) => !item.classList.contains('hidden'));
        if (!hasOpen) document.body.classList.remove('overflow-hidden');
        const trigger = openDrawers.get(id);
        openDrawers.delete(id);
        if (trigger && typeof trigger.focus === 'function') trigger.focus();
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-patient-drawer-open]');
        if (opener) {
            event.preventDefault();
            openDrawer(opener.getAttribute('data-patient-drawer-open'), opener);
            return;
        }
        const closer = event.target.closest('[data-patient-drawer-close]');
        if (closer) {
            event.preventDefault();
            closeDrawer(closer.getAttribute('data-patient-drawer-close'));
        }
    });

    document.addEventListener('keydown', function (event) {
        const activeDrawer = Array.from(document.querySelectorAll('.patient-command-drawer')).find((item) => !item.classList.contains('hidden'));
        if (!activeDrawer) return;
        if (event.key === 'Escape') {
            closeDrawer(activeDrawer.id);
            return;
        }
        if (event.key !== 'Tab') return;
        const focusable = focusableElements(activeDrawer);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    const dateInput = document.getElementById('patientCommandAppointmentDate');
    const durationSelect = document.getElementById('patientCommandDuration');
    const chairSelect = document.getElementById('patientCommandChair');
    const timeSelect = document.getElementById('patientCommandAppointmentTime');
    const slotsUrl = <?= json_encode(base_url('appointment/available-time-slots'), JSON_UNESCAPED_SLASHES) ?>;
    const loadingLabel = <?= json_encode($copy['loadingSlots'], JSON_UNESCAPED_UNICODE) ?>;
    const noSlotsLabel = <?= json_encode($copy['noSlots'], JSON_UNESCAPED_UNICODE) ?>;
    const selectLabel = <?= json_encode($copy['select'], JSON_UNESCAPED_UNICODE) ?>;

    function setTimeOptions(options) {
        if (!timeSelect) return;
        timeSelect.replaceChildren();
        options.forEach((option) => {
            const element = document.createElement('option');
            element.value = option.value;
            element.textContent = option.label;
            if (option.disabled) element.disabled = true;
            timeSelect.appendChild(element);
        });
    }

    function loadSlots() {
        if (!dateInput || !durationSelect || !chairSelect || !timeSelect) return;
        const date = dateInput.value;
        const duration = durationSelect.value;
        const chairName = chairSelect.value;
        if (!date || !duration || !chairName) {
            setTimeOptions([{ value: '', label: selectLabel }]);
            return;
        }
        setTimeOptions([{ value: '', label: loadingLabel, disabled: true }]);
        fetch(`${slotsUrl}?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(duration)}&chair_name=${encodeURIComponent(chairName)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then((response) => response.json())
            .then((payload) => {
                const slots = Array.isArray(payload) ? payload : (Array.isArray(payload?.slots) ? payload.slots : []);
                if (!slots.length) {
                    setTimeOptions([{ value: '', label: noSlotsLabel, disabled: true }]);
                    return;
                }
                setTimeOptions([{ value: '', label: selectLabel }].concat(slots.map((slot) => ({
                    value: slot.time || slot.value || slot,
                    label: slot.label || slot.display || slot.time || slot.value || slot,
                }))));
            })
            .catch(() => setTimeOptions([{ value: '', label: noSlotsLabel, disabled: true }]));
    }

    [dateInput, durationSelect, chairSelect].forEach((element) => element?.addEventListener('change', loadSlots));
})();
</script>
