<?= $this->extend('layouts/kiosk') ?>

<?= $this->section('content') ?>
<?php
$localeCode = $locale ?? 'ar';
$isArabic = str_starts_with($localeCode, 'ar');
$stepFindLabel = $isArabic ? 'ابحث عن الملف' : 'Find record';
$stepConfirmLabel = $isArabic ? 'تأكيد الزيارة' : 'Confirm visit';
$stepDetailsLabel = $isArabic ? 'تحديث البيانات' : 'Update details';
$stepContractsLabel = $isArabic ? 'مراجعة وتوقيع' : 'Review & sign';
$workflowTitle = $isArabic ? 'رحلة تسجيل الوصول' : 'Check-in flow';
$privacyNote = $isArabic ? 'يتم مسح الجلسة تلقائياً بعد عدم الاستخدام لحماية الخصوصية.' : 'This session clears automatically after inactivity to protect privacy.';
$helpNote = $isArabic ? 'إذا احتجت مساعدة، اطلب من موظف الاستقبال.' : 'If you need help, ask the reception desk.';
$secureAccessLabel = $isArabic ? 'وصول آمن' : 'Secure access';
$readyLabel = $isArabic ? 'جاهز للبدء' : 'Ready to begin';
$taskQueueLabel = $isArabic ? 'المهام المتاحة' : 'Available tasks';
$searchCardTitle = $isArabic ? 'ابدأ بالعثور على الملف' : 'Start by finding the record';
$resultsHeading = $isArabic ? 'نتيجة البحث' : 'Lookup result';
$resultsEmptyLabel = $isArabic ? 'لا توجد نتائج حتى الآن' : 'No results yet';
$resultsEmptyHint = $isArabic ? 'بعد إدخال رقم الجوال ستظهر المواعيد أو البيانات أو العقود المطلوبة هنا.' : 'Appointments, details, and pending signatures will appear here after a phone lookup.';
$contractModalHint = $isArabic ? 'راجع الخطة ثم وقع لإكمال الطلب.' : 'Review the plan, then sign to complete the request.';
$clinicTagline = trim((string) ($settings['clinic_tagline'] ?? ''));
$heroLine = $clinicTagline !== '' ? $clinicTagline : lang('Kiosk.headerCompactLine');
$footerHelp = $isArabic ? 'إذا احتجت مساعدة، راجع موظف الاستقبال.' : 'If you need help, please ask the reception desk.';


$logoPath = $settings['clinic_logo_path'] ?? '';
$logoSrc = '';
if (!empty($logoPath)) {
    $logoSrc = (strpos($logoPath, 'http://') === 0 || strpos($logoPath, 'https://') === 0)
        ? $logoPath
        : base_url(ltrim($logoPath, '/'));
}

$hasConfirmable = false;
foreach (($appointments ?? []) as $appointment) {
    if (!empty($appointment['can_confirm'])) {
        $hasConfirmable = true;
        break;
    }
}
$hasAppointments = !empty($appointments);
$hasPatients = !empty($patients);
$hasContracts = !empty($contracts);
$primaryAction = $hasConfirmable ? 'checkin' : ($hasPatients ? 'details' : ($hasContracts ? 'contracts' : 'checkin'));
$priorityTaskTitle = $hasConfirmable
    ? ($isArabic ? 'موعد اليوم جاهز للتأكيد' : 'Today\'s visit is ready to confirm')
    : ($hasPatients
        ? ($isArabic ? 'راجع البيانات قبل الاستقبال' : 'Review patient details before arrival')
        : ($hasContracts
            ? ($isArabic ? 'يوجد توقيع مطلوب' : 'A signature is waiting')
            : ($isArabic ? 'ابدأ بالبحث عن الملف' : 'Start by finding the record')));
$priorityTaskHelp = $hasConfirmable
    ? ($isArabic ? 'إذا كان الموعد لليوم، أكد الزيارة أولاً ثم أكمل أي بيانات أو توقيع مطلوب.' : 'If the appointment is for today, confirm the visit first, then complete any details or signatures.')
    : ($hasPatients
        ? ($isArabic ? 'يمكن للمريض تحديث الجوال أو البريد الإلكتروني قبل إنهاء الوصول.' : 'The patient can update phone or email before finishing check-in.')
        : ($hasContracts
            ? ($isArabic ? 'أكمل مراجعة الخطة والتوقيع إذا طُلب ذلك.' : 'Complete treatment-plan review and signature if needed.')
            : ($isArabic ? 'أدخل رقم الجوال لعرض المهام المناسبة.' : 'Enter the mobile number to show the relevant tasks.')));
$workflowSteps = [
    ['number' => '1', 'label' => $stepFindLabel, 'active' => true],
    ['number' => '2', 'label' => $stepConfirmLabel, 'active' => $hasAppointments],
    ['number' => '3', 'label' => $stepDetailsLabel, 'active' => $hasPatients],
    ['number' => '4', 'label' => $stepContractsLabel, 'active' => $hasContracts],
];
$balanceSummary = $balance_summary ?? null;
$completionState = $completion ?? null;
$completionBalance = $completionState['balance_summary'] ?? null;
?>
<div id="kioskSessionRoot"
     class="min-h-screen denta-kiosk denta-kiosk-workspace"
     data-pin-verified="<?= !empty($pin_verified) ? '1' : '0' ?>"
     data-reset-url="<?= esc(base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset')) ?>"
     data-timeout-ms="90000">
    <div class="v3-kiosk-shell">
        <div class="v3-kiosk-access-bar">
            <div class="inline-flex v3-corner border v3-border v3-surface p-1">
                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in?lang=ar') ?>"
                   class="v3-radius px-4 py-2 text-sm font-semibold transition-all <?= $isArabic ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50' ?>">
                    العربية
                </a>
                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in?lang=en') ?>"
                   class="v3-radius px-4 py-2 text-sm font-semibold transition-all <?= !$isArabic ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50' ?>">
                    English
                </a>
            </div>
            <?php if (!empty($pin_verified)): ?>
                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-rotate-left text-slate-400"></i>
                    <?= esc(lang('Kiosk.notYouReenter')) ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="overflow-hidden v3-corner border v3-border v3-surface">
            <div class="kiosk-header-banner v3-kiosk-command">
                <div class="v3-kiosk-command-content">
                    <div class="flex items-start gap-4">
                        <div class="v3-kiosk-brand">
                            <?php if ($logoSrc): ?>
                                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($settings['clinic_name'] ?? $clinic['name']) ?>" class="h-11 w-11 object-contain">
                            <?php else: ?>
                                <i class="fas fa-tooth text-2xl text-white"></i>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] v3-accent-text">
                                <span><?= esc(lang('Kiosk.title')) ?></span>
                                <span class="h-1 w-1 rounded-full v3-surface"></span>
                                <span><?= esc(!empty($pin_verified) ? $readyLabel : $secureAccessLabel) ?></span>
                            </div>
                            <h1 class="v3-kiosk-clinic-name" dir="auto" style="unicode-bidi: plaintext;">
                                <?= esc($settings['clinic_name'] ?? $clinic['name']) ?>
                            </h1>
                            <p class="max-w-2xl text-sm leading-6 text-slate-200" dir="auto" style="unicode-bidi: plaintext;">
                                <?= esc($heroLine) ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:max-w-sm lg:justify-end">
                        <span class="inline-flex items-center gap-2 rounded-full border v3-border v3-surface px-3 py-2 text-xs font-semibold text-white/85">
                            <i class="fas fa-clock text-[11px]"></i>
                            <?= esc($privacyNote) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="v3-kiosk-progress">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($workflowSteps as $step): ?>
                        <div class="kiosk-step-pill flex items-center gap-3 v3-corner border px-4 py-3 <?= $step['active'] ? 'active border-[var(--denta-emerald-pine)] bg-[var(--denta-emerald-pine)] text-white' : 'border-[var(--denta-taupe-border)] bg-[var(--denta-parchment)] text-[var(--denta-ink)]' ?>">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold <?= $step['active'] ? 'bg-[var(--denta-terracotta)] text-white' : 'bg-[var(--denta-sandstone)] text-[var(--denta-muted)]' ?>"><?= esc($step['number']) ?></span>
                            <span class="text-sm font-semibold"><?= esc($step['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="v3-kiosk-body">
                <?php if (!($pin_verified ?? false)): ?>
                    <div class="v3-kiosk-pin">
                        <div class="v3-kiosk-security-icon">
                            <i class="fas fa-shield-halved text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900"><?= esc(lang('Kiosk.enterClinicPin')) ?></h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500"><?= esc(lang('Kiosk.kioskProtected')) ?></p>
                        <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/unlock') ?>" method="POST" class="mt-6 space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.clinicPin')) ?></label>
                                <input type="password" name="kiosk_pin" inputmode="numeric" class="w-full v3-corner border-2 v3-border v3-surface px-4 py-4 text-lg tracking-[0.25em] text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="<?= esc(lang('Kiosk.enterPin')) ?>" required>
                            </div>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 v3-corner v3-soft-surface px-6 py-4 text-base font-semibold text-white transition hover:bg-indigo-700">
                                <i class="fas fa-lock-open"></i>
                                <?= esc(lang('Kiosk.unlockKiosk')) ?>
                            </button>
                        </form>
                    </div>
                <?php elseif (!empty($completionState)): ?>
                    <div class="mx-auto max-w-3xl space-y-6">
                        <section class="v3-command-section v3-corner border border-emerald-200 p-8">
                            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center v3-corner bg-emerald-600 text-white">
                                        <i class="fas fa-circle-check text-2xl"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                            <i class="fas fa-check"></i>
                                            <?= esc(lang('Kiosk.completionBadge')) ?>
                                        </div>
                                        <h2 class="text-2xl font-bold tracking-tight text-slate-900"><?= esc(lang('Kiosk.completionTitle')) ?></h2>
                                        <p class="text-sm leading-6 text-slate-600"><?= esc($completionState['message'] ?? lang('Kiosk.msgAppointmentConfirmed')) ?></p>
                                        <div class="flex flex-wrap gap-3 pt-1 text-sm text-slate-700">
                                            <?php if (!empty($completionState['patient_name'])): ?>
                                                <span class="inline-flex items-center gap-2 rounded-full border v3-border v3-surface px-3 py-2"><i class="fas fa-user text-slate-400"></i><?= esc($completionState['patient_name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($completionState['appointment_date']) || !empty($completionState['appointment_time'])): ?>
                                                <span class="inline-flex items-center gap-2 rounded-full border v3-border v3-surface px-3 py-2"><i class="fas fa-calendar-day text-slate-400"></i><?= esc(trim(($completionState['appointment_date'] ?? '') . ' ' . ($completionState['appointment_time'] ?? ''))) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($completionState['appointment_type'])): ?>
                                                <span class="inline-flex items-center gap-2 rounded-full border v3-border v3-surface px-3 py-2"><i class="fas fa-stethoscope text-slate-400"></i><?= esc($completionState['appointment_type']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="inline-flex items-center gap-2 self-start v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                    <i class="fas fa-rotate-left text-slate-400"></i>
                                    <?= esc(lang('Kiosk.notYouReenter')) ?>
                                </a>
                            </div>
                        </section>

                        <section class="grid gap-4 md:grid-cols-2">
                            <article class="v3-kiosk-station">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 items-center justify-center v3-corner v3-soft-surface v3-accent-text"><i class="fas fa-chair"></i></span>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900"><?= esc(lang('Kiosk.completionNextStepTitle')) ?></h3>
                                        <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.completionNextStepHelp')) ?></p>
                                    </div>
                                </div>
                            </article>
                            <article class="v3-corner border <?= !empty($completionBalance['has_due']) ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' ?> p-6 <?= !empty($completionBalance['has_due']) ? 'shadow-amber-100/60' : 'shadow-emerald-100/60' ?>">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 items-center justify-center v3-corner <?= !empty($completionBalance['has_due']) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                        <i class="fas <?= !empty($completionBalance['has_due']) ? 'fa-wallet' : 'fa-circle-check' ?>"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-semibold <?= !empty($completionBalance['has_due']) ? 'text-amber-900' : 'text-emerald-900' ?>"><?= esc(lang('Kiosk.balanceCardTitle')) ?></h3>
                                        <?php if (!empty($completionBalance['has_due'])): ?>
                                            <p class="text-lg font-bold text-amber-900"><?= esc($completionBalance['outstanding_formatted'] ?? formatCurrency(0)) ?></p>
                                            <p class="text-sm text-amber-800"><?= esc(lang('Kiosk.balancePayAtReception')) ?></p>
                                        <?php else: ?>
                                            <p class="text-lg font-bold text-emerald-900"><?= esc(lang('Kiosk.balanceClearTitle')) ?></p>
                                            <p class="text-sm text-emerald-800"><?= esc(lang('Kiosk.balanceClearHelp')) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </section>
                    </div>
                <?php else: ?>
                    <div class="v3-kiosk-stations">
                        <section class="v3-kiosk-station">
                            <div class="v3-kiosk-station-heading">
                                <div>
                                    <div class="v3-kiosk-station-label">
                                        <i class="fas fa-mobile-screen-button text-[11px]"></i>
                                        <?= esc($searchCardTitle) ?>
                                    </div>
                                    <h2 class="text-2xl font-bold tracking-tight text-slate-900"><?= esc(lang('Kiosk.checkIn')) ?></h2>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500"><?= esc(lang('Kiosk.checkInHint')) ?></p>
                                </div>
                            </div>

                            <form id="kioskLookupForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/lookup') ?>" method="POST" class="space-y-5" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                                <?= csrf_field() ?>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.mobileNumber')) ?></label>
                                    <input id="kioskPhoneInput" type="tel" name="phone" inputmode="numeric" class="w-full v3-corner border-2 v3-border v3-surface px-5 py-4 text-xl tracking-[0.08em] text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" placeholder="<?= esc(lang('Kiosk.mobilePlaceholder')) ?>" required>
                                </div>

                                <div class="v3-kiosk-keypad-panel" dir="ltr">
                                    <div class="v3-kiosk-keypad" dir="ltr">
                                        <?php foreach (['1','2','3','4','5','6','7','8','9','clear','0','back'] as $key): ?>
                                            <?php if ($key === 'clear'): ?>
                                                <button type="button" data-key="clear" class="kiosk-key v3-kiosk-key v3-kiosk-key-secondary"><?= esc(lang('Kiosk.clear')) ?></button>
                                            <?php elseif ($key === 'back'): ?>
                                                <button type="button" data-key="back" class="kiosk-key v3-kiosk-key v3-kiosk-key-secondary"><?= esc(lang('Kiosk.back')) ?></button>
                                            <?php else: ?>
                                                <button type="button" data-key="<?= $key ?>" class="kiosk-key v3-kiosk-key"><?= $key ?></button>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <button type="submit" class="kiosk-btn-submit inline-flex w-full items-center justify-center gap-2 v3-corner bg-[var(--denta-emerald-pine)] px-6 py-4 text-base font-semibold text-white transition hover:bg-[var(--denta-emerald-hover)]">
                                    <i class="fas fa-user-check"></i>
                                    <?= esc(lang('Kiosk.findRecord')) ?>
                                </button>
                            </form>
                        </section>

                        <aside class="space-y-6">
                            <section class="v3-kiosk-instructions">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-10 w-10 items-center justify-center v3-corner v3-soft-surface text-white">
                                        <i class="fas fa-bell-concierge"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-semibold v3-accent-text"><?= esc($priorityTaskTitle) ?></h3>
                                        <p class="mt-2 text-sm leading-6 v3-accent-text"><?= esc($priorityTaskHelp) ?></p>
                                    </div>
                                </div>
                            </section>

                            <section class="v3-kiosk-station">
                                <div class="mb-4 flex items-center gap-2 text-slate-900">
                                    <span class="flex h-10 w-10 items-center justify-center v3-corner v3-soft-surface v3-accent-text"><i class="fas fa-list-check"></i></span>
                                    <div>
                                        <h3 class="text-lg font-semibold"><?= esc($taskQueueLabel) ?></h3>
                                        <p class="text-sm text-slate-500"><?= esc($helpNote) ?></p>
                                    </div>
                                </div>
                                <div id="kioskActionBar"
                                     class="space-y-3"
                                     data-has-appointments="<?= $hasAppointments ? '1' : '0' ?>"
                                     data-has-patients="<?= $hasPatients ? '1' : '0' ?>"
                                     data-has-contracts="<?= $hasContracts ? '1' : '0' ?>"
                                     data-has-confirmable="<?= $hasConfirmable ? '1' : '0' ?>"
                                     data-primary-action="<?= esc($primaryAction) ?>">
                                    <button type="button" data-kiosk-action-btn data-action="checkin" class="flex w-full items-center justify-between v3-corner border px-4 py-3 text-left text-sm font-semibold transition-all duration-150">
                                        <span class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center v3-radius bg-slate-100 text-slate-600"><i class="fas fa-calendar-check"></i></span><?= esc(lang('Kiosk.actionConfirmVisit')) ?></span>
                                        <i class="fas fa-chevron-right text-xs opacity-60"></i>
                                    </button>
                                    <button type="button" data-kiosk-action-btn data-action="details" class="flex w-full items-center justify-between v3-corner border px-4 py-3 text-left text-sm font-semibold transition-all duration-150">
                                        <span class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center v3-radius bg-slate-100 text-slate-600"><i class="fas fa-address-card"></i></span><?= esc(lang('Kiosk.actionUpdateDetails')) ?></span>
                                        <i class="fas fa-chevron-right text-xs opacity-60"></i>
                                    </button>
                                    <button type="button" data-kiosk-action-btn data-action="contracts" class="flex w-full items-center justify-between v3-corner border px-4 py-3 text-left text-sm font-semibold transition-all duration-150">
                                        <span class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center v3-radius bg-slate-100 text-slate-600"><i class="fas fa-file-signature"></i></span><?= esc(lang('Kiosk.actionReviewSign')) ?></span>
                                        <i class="fas fa-chevron-right text-xs opacity-60"></i>
                                    </button>
                                </div>
                            </section>

                            <section class="v3-kiosk-station">
                                <h3 class="text-lg font-semibold text-slate-900"><?= esc($resultsHeading) ?></h3>
                                <p class="mt-2 text-sm leading-6 text-slate-500"><?= esc($resultsEmptyHint) ?></p>
                                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                    <div class="v3-corner border v3-border v3-surface px-4 py-3">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400"><?= esc(lang('Kiosk.mobileNumber')) ?></div>
                                        <div id="kioskLookupPhonePreview" class="mt-1 text-sm font-semibold text-slate-700"><?= esc($lookup_phone ?: ($isArabic ? 'غير محدد' : 'Not entered')) ?></div>
                                    </div>
                                    <div class="v3-corner border v3-border v3-surface px-4 py-3">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400"><?= esc(lang('Kiosk.needHelp')) ?></div>
                                        <div class="mt-1 text-sm font-semibold text-slate-700"><?= esc($footerHelp) ?></div>
                                    </div>
                                </div>
                            </section>

                            <section id="kioskBalanceSummaryCard" class="v3-corner border <?= !empty($balanceSummary['has_due']) ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white' ?> p-6 <?= $balanceSummary ? '' : 'hidden' ?>">
                                <div class="flex items-start gap-3">
                                    <span id="kioskBalanceIcon" class="mt-0.5 flex h-10 w-10 items-center justify-center v3-corner <?= !empty($balanceSummary['has_due']) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                        <i class="fas <?= !empty($balanceSummary['has_due']) ? 'fa-wallet' : 'fa-circle-check' ?>"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-semibold <?= !empty($balanceSummary['has_due']) ? 'text-amber-900' : 'text-slate-900' ?>"><?= esc(lang('Kiosk.balanceCardTitle')) ?></h3>
                                        <p id="kioskBalanceAmount" class="mt-2 text-xl font-bold <?= !empty($balanceSummary['has_due']) ? 'text-amber-900' : 'text-emerald-700' ?>">
                                            <?= esc(($balanceSummary['has_due'] ?? false) ? ($balanceSummary['outstanding_formatted'] ?? formatCurrency(0)) : lang('Kiosk.balanceClearTitle')) ?>
                                        </p>
                                        <p id="kioskBalanceHelp" class="mt-1 text-sm <?= !empty($balanceSummary['has_due']) ? 'text-amber-800' : 'text-slate-500' ?>">
                                            <?= esc(($balanceSummary['has_due'] ?? false) ? lang('Kiosk.balancePayAtReception') : lang('Kiosk.balanceLookupHelp')) ?>
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>

                    <div id="kioskResults" class="mt-8 space-y-6 <?= $hasAppointments || $hasPatients || $hasContracts || !empty($info_message) ? '' : 'hidden' ?>">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"><?= esc($resultsHeading) ?></div>
                                <h3 id="kioskResultsTitle" class="mt-1 text-xl font-bold tracking-tight text-slate-900"><?= esc(lang('Kiosk.resultsFor')) ?> <?= esc($lookup_phone ?? '') ?></h3>
                            </div>
                            <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="inline-flex items-center gap-2 self-start v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                <i class="fas fa-rotate-left text-slate-400"></i>
                                <?= esc(lang('Kiosk.notYouReenter')) ?>
                            </a>
                        </div>

                        <section id="kioskResultsList" class="v3-kiosk-station">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900"><?= esc(lang('Kiosk.actionConfirmVisit')) ?></h4>
                                    <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.confirmAvailableOnDay')) ?></p>
                                </div>
                                <span class="rounded-full v3-soft-surface px-3 py-1 text-xs font-semibold v3-accent-text"><?= count($appointments ?? []) ?></span>
                            </div>
                            <div id="kioskAppointmentRows" class="space-y-4">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="flex flex-col gap-4 v3-corner border v3-border v3-surface p-5 md:flex-row md:items-center md:justify-between">
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-slate-900" dir="auto" style="unicode-bidi: plaintext;"><?= esc($appointment['patient_display'] ?? '') ?></p>
                                            <p class="text-sm text-slate-500">
                                                <?= esc($appointment['appointment_date'] ?? '') ?> - <?= esc($appointment['appointment_time'] ?? '') ?> - <?= esc($appointment['appointment_type'] ?? '') ?>
                                                <?php if (!empty($appointment['status'])): ?>
                                                    - <?= esc($appointment['status_label'] ?? $appointment['status']) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <?php if (!empty($appointment['can_confirm'])): ?>
                                            <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/confirm') ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="appointment_id" value="<?= (int) ($appointment['id'] ?? 0) ?>">
                                                <button type="submit" class="inline-flex items-center gap-2 v3-corner bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                    <i class="fas fa-check-circle"></i>
                                                    <?= esc(lang('Kiosk.confirmToday')) ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold text-slate-500"><?= esc(lang('Kiosk.confirmAvailableOnDay')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($appointments) && !empty($info_message)): ?>
                                    <div class="v3-corner border border-amber-200 bg-amber-50 p-5 text-sm text-amber-700">
                                        <?= esc($info_message) ?>
                                    </div>
                                <?php elseif (empty($appointments)): ?>
                                    <div class="v3-corner border v3-border v3-surface p-5 text-sm text-slate-500">
                                        <?= esc($resultsEmptyLabel) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section id="kioskPatientDetails" class="<?= !empty($patients) ? '' : 'hidden' ?> v3-kiosk-station">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900"><?= esc(lang('Kiosk.updateYourDetails')) ?></h4>
                                    <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.detailsHint')) ?></p>
                                </div>
                                <span class="rounded-full v3-soft-surface px-3 py-1 text-xs font-semibold v3-accent-text"><?= count($patients ?? []) ?></span>
                            </div>
                            <form id="kioskDetailsForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/update-details') ?>" method="POST" class="space-y-4" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                                <?= csrf_field() ?>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.selectPatient')) ?></label>
                                    <select name="patient_id" id="kioskPatientSelect" class="w-full v3-corner border-2 v3-border v3-surface px-4 py-3 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                                        <?php foreach ($patients ?? [] as $patient): ?>
                                            <option value="<?= (int) $patient['id'] ?>" data-phone="<?= esc($patient['phone'] ?? '') ?>" data-email="<?= esc($patient['email'] ?? '') ?>">
                                                <?= esc($patient['name'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.mobileNumber')) ?></label>
                                        <input type="tel" name="phone" id="kioskPhoneUpdate" inputmode="numeric" class="w-full v3-corner border-2 v3-border v3-surface px-4 py-3 text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.email')) ?></label>
                                        <input type="email" name="email" id="kioskEmailUpdate" class="w-full v3-corner border-2 v3-border v3-surface px-4 py-3 text-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 v3-corner v3-soft-surface px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                    <i class="fas fa-save"></i>
                                    <?= esc(lang('Kiosk.updateDetailsButton')) ?>
                                </button>
                            </form>
                        </section>

                        <section id="kioskContracts" class="<?= !empty($contracts) ? '' : 'hidden' ?> v3-kiosk-station">
                            <div class="mb-5 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900"><?= esc(lang('Kiosk.treatmentPlan')) ?></h4>
                                    <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.treatmentPlanHint')) ?></p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"><?= count($contracts ?? []) ?></span>
                            </div>
                            <div id="kioskContractList" class="space-y-4">
                                <?php foreach ($contracts ?? [] as $contract): ?>
                                    <div class="flex flex-col gap-4 v3-corner border v3-border v3-surface p-5 md:flex-row md:items-center md:justify-between">
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-slate-900"><?= esc(lang('Kiosk.planPrefix')) ?><?= (int) $contract['id'] ?></p>
                                            <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.total')) ?>: <?= esc($contract['currency']) ?> <?= number_format((float) $contract['total_cost'], 2) ?></p>
                                        </div>
                                        <button type="button" class="kiosk-sign-btn inline-flex items-center gap-2 v3-corner bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                                data-contract='<?= esc(json_encode($contract), 'attr') ?>'>
                                            <i class="fas fa-pen-fancy"></i>
                                            <?= esc(lang('Kiosk.reviewAndSign')) ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 text-center text-xs text-slate-500" dir="auto" style="unicode-bidi: plaintext;">
            <?= esc($footerHelp) ?>
        </div>
    </div>
</div>

<div id="kioskContractModal" class="fixed inset-0 z-50 hidden bg-slate-950/60">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden v3-corner v3-surface">
            <div class="flex items-center justify-between bg-slate-950 px-6 py-5 text-white">
                <div>
                    <h3 class="text-xl font-semibold"><?= esc(lang('Kiosk.treatmentPlan')) ?></h3>
                    <p class="mt-1 text-sm text-slate-300" id="kioskContractMeta"></p>
                </div>
                <button type="button" id="kioskCloseContract" class="flex h-10 w-10 items-center justify-center v3-corner border v3-border v3-surface transition hover:bg-white/20">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="space-y-6 overflow-y-auto p-6">
                <div id="kioskContractSnapshot" class="v3-kiosk-keypad-panel"></div>
                <div>
                    <h4 class="mb-3 text-lg font-semibold text-slate-800"><?= esc(lang('Kiosk.planItems')) ?></h4>
                    <div class="overflow-x-auto v3-corner border v3-border">
                        <table class="min-w-full text-sm">
                            <thead class="v3-surface text-xs uppercase tracking-[0.14em] text-slate-500">
                                <tr>
                                    <th class="px-3 py-3 text-left"><?= esc(lang('Kiosk.tooth')) ?></th>
                                    <th class="px-3 py-3 text-left"><?= esc(lang('Kiosk.description')) ?></th>
                                    <th class="px-3 py-3 text-left"><?= esc(lang('Kiosk.qty')) ?></th>
                                    <th class="px-3 py-3 text-left"><?= esc(lang('Kiosk.price')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="kioskContractItems" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
                <form id="kioskContractForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/sign-contract') ?>" method="POST" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="contract_id" id="kioskContractId">
                    <input type="hidden" name="patient_id" id="kioskContractPatientId">
                    <input type="hidden" name="signature_data" id="kioskSignatureData">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.typedName')) ?></label>
                            <input type="text" name="signer_name" id="kioskSignerName" class="w-full v3-corner border-2 v3-border v3-surface px-4 py-3 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" required>
                        </div>
                        <div class="v3-corner border v3-border v3-surface px-4 py-4 text-sm leading-6 text-slate-500">
                            <?= esc($contractModalHint) ?>
                            <div class="mt-2 text-slate-400"><?= esc(lang('Kiosk.signHint')) ?></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Kiosk.drawSignature')) ?></label>
                        <canvas id="kioskSignatureCanvas" class="w-full v3-radius border v3-border v3-surface" height="160" style="touch-action: none; -ms-touch-action: none;"></canvas>
                        <button type="button" id="kioskClearSignature" class="mt-3 inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                            <i class="fas fa-eraser text-slate-500"></i>
                            <?= esc(lang('Kiosk.clearSignature')) ?>
                        </button>
                    </div>
                    <button type="submit" class="mt-5 inline-flex w-full items-center justify-center gap-2 v3-corner bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fas fa-signature"></i>
                        <?= esc(lang('Kiosk.signPlan')) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
const kioskI18n = <?= json_encode([
    'resultsFor' => lang('Kiosk.resultsFor'),
    'confirmToday' => lang('Kiosk.confirmToday'),
    'confirmAvailableOnDay' => lang('Kiosk.confirmAvailableOnDay'),
    'total' => lang('Kiosk.total'),
    'reviewAndSign' => lang('Kiosk.reviewAndSign'),
    'planItems' => lang('Kiosk.planItems'),
    'noSnapshot' => lang('Kiosk.noSnapshot'),
    'noItems' => lang('Kiosk.noItems'),
    'lookupFailedTryAgain' => lang('Kiosk.lookupFailedTryAgain'),
    'lookupFailed' => lang('Kiosk.lookupFailed'),
    'noUpcomingForMobile' => lang('Kiosk.noUpcomingForMobile'),
    'pleaseEnterMobile' => lang('Kiosk.pleaseEnterMobile'),
    'updated' => lang('Kiosk.updated'),
    'unableUpdateDetails' => lang('Kiosk.unableUpdateDetails'),
    'unableUpdateDetailsNow' => lang('Kiosk.unableUpdateDetailsNow'),
    'signed' => lang('Kiosk.signed'),
    'unableSignPlan' => lang('Kiosk.unableSignPlan'),
    'unableSignPlanNow' => lang('Kiosk.unableSignPlanNow'),
    'error' => lang('Kiosk.error'),
    'planPrefix' => lang('Kiosk.planPrefix'),
    'balanceCardTitle' => lang('Kiosk.balanceCardTitle'),
    'balanceClearTitle' => lang('Kiosk.balanceClearTitle'),
    'balanceLookupHelp' => lang('Kiosk.balanceLookupHelp'),
    'balancePayAtReception' => lang('Kiosk.balancePayAtReception'),
], JSON_UNESCAPED_UNICODE) ?>;
const kioskActionBar = document.getElementById('kioskActionBar');
const kioskActionButtons = Array.from(document.querySelectorAll('[data-kiosk-action-btn]'));
const kioskActionTargets = {
    checkin: 'kioskResultsList',
    details: 'kioskPatientDetails',
    contracts: 'kioskContracts',
};
const setActionButtonState = (button, active, enabled) => {
    if (!button) return;
    button.disabled = !enabled;
    button.classList.remove(
        'border-indigo-200', 'bg-indigo-50', 'text-indigo-900', 'shadow-sm',
        'border-slate-200', 'bg-white', 'text-slate-700', 'hover:bg-slate-50',
        'opacity-50', 'cursor-not-allowed'
    );

    if (!enabled) {
        button.classList.add('border-slate-200', 'bg-white', 'text-slate-400', 'opacity-50', 'cursor-not-allowed');
        return;
    }

    if (active) {
        button.classList.add('border-indigo-200', 'bg-indigo-50', 'text-indigo-900', 'shadow-sm');
        return;
    }

    button.classList.add('border-slate-200', 'bg-white', 'text-slate-700', 'hover:bg-slate-50');
};

const choosePrimaryAction = (state) => {
    if (state.hasConfirmable) return 'checkin';
    if (state.hasPatients) return 'details';
    if (state.hasContracts) return 'contracts';
    return 'checkin';
};

const refreshKioskActionBar = (state) => {
    if (!kioskActionBar || !kioskActionButtons.length) return;

    const availability = {
        checkin: !!state.hasAppointments,
        details: !!state.hasPatients,
        contracts: !!state.hasContracts,
    };
    const primary = choosePrimaryAction(state);

    kioskActionBar.dataset.primaryAction = primary;
    kioskActionButtons.forEach((button) => {
        const action = button.dataset.action || '';
        const enabled = !!availability[action];
        const active = enabled && action === primary;
        setActionButtonState(button, active, enabled);
    });
};

kioskActionButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (button.disabled) return;
        const action = button.dataset.action || '';
        const targetId = kioskActionTargets[action];
        const target = targetId ? document.getElementById(targetId) : null;
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        kioskActionButtons.forEach((btn) => {
            const btnAction = btn.dataset.action || '';
            setActionButtonState(btn, btnAction === action, !btn.disabled);
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    if (kioskActionBar) {
        refreshKioskActionBar({
            hasAppointments: kioskActionBar.dataset.hasAppointments === '1',
            hasPatients: kioskActionBar.dataset.hasPatients === '1',
            hasContracts: kioskActionBar.dataset.hasContracts === '1',
            hasConfirmable: kioskActionBar.dataset.hasConfirmable === '1',
        });
    }

    const input = document.getElementById('kioskPhoneInput');
    const keys = document.querySelectorAll('.kiosk-key');
    const form = document.getElementById('kioskLookupForm');
    const results = document.getElementById('kioskResults');
    const resultsTitle = document.getElementById('kioskResultsTitle');
    const resultsList = document.getElementById('kioskAppointmentRows');
    const phonePreview = document.getElementById('kioskLookupPhonePreview');
    if (!input || !keys.length) return;

    const clearNode = (node) => {
        if (!node) return;
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    };

    const appendText = (node, text) => {
        if (!node) return;
        node.appendChild(document.createTextNode(text || ''));
    };

    const createElement = (tag, className, text) => {
        const el = document.createElement(tag);
        if (className) {
            el.className = className;
        }
        if (text !== undefined && text !== null) {
            el.textContent = text;
        }
        return el;
    };

    const createAppointmentRow = (appointment, lookupForm) => {
        const row = document.createElement('div');
        row.className = 'flex flex-col gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-5 md:flex-row md:items-center md:justify-between';

        const info = document.createElement('div');
        info.className = 'space-y-1';

        const name = document.createElement('p');
        name.className = 'text-sm font-semibold text-slate-900';
        name.setAttribute('dir', 'auto');
        name.style.unicodeBidi = 'plaintext';
        name.textContent = appointment.patient_display || '';

        const meta = document.createElement('p');
        meta.className = 'text-sm text-slate-500';
        const dateLabel = appointment.appointment_date ? `${appointment.appointment_date} - ` : '';
        const statusLabel = appointment.status_label ? ` - ${appointment.status_label}` : '';
        meta.textContent = `${dateLabel}${appointment.appointment_time || ''} - ${appointment.appointment_type || ''}${statusLabel}`;

        info.appendChild(name);
        info.appendChild(meta);
        row.appendChild(info);

        if (appointment.can_confirm) {
            const confirmForm = document.createElement('form');
            confirmForm.method = 'POST';
            confirmForm.action = '<?= base_url('kiosk/' . $clinic['uuid'] . '/confirm') ?>';

            const confirmCsrf = document.createElement('input');
            confirmCsrf.type = 'hidden';
            const confirmCsrfName = lookupForm.dataset.csrfName || '<?= csrf_token() ?>';
            const confirmCsrfToken = lookupForm.dataset.csrfToken || '<?= csrf_hash() ?>';
            confirmCsrf.name = confirmCsrfName;
            confirmCsrf.value = confirmCsrfToken;

            const appointmentId = document.createElement('input');
            appointmentId.type = 'hidden';
            appointmentId.name = 'appointment_id';
            appointmentId.value = appointment.id || '';

            const confirmBtn = document.createElement('button');
            confirmBtn.type = 'submit';
            confirmBtn.className = 'inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700';

            const icon = document.createElement('i');
            icon.className = 'fas fa-check-circle';
            confirmBtn.appendChild(icon);
            confirmBtn.appendChild(document.createTextNode(kioskI18n.confirmToday));

            confirmForm.appendChild(confirmCsrf);
            confirmForm.appendChild(appointmentId);
            confirmForm.appendChild(confirmBtn);
            row.appendChild(confirmForm);
        } else {
            const note = document.createElement('span');
            note.className = 'text-xs font-semibold text-slate-500';
            note.textContent = kioskI18n.confirmAvailableOnDay;
            row.appendChild(note);
        }

        return row;
    };

    const applyKey = (value) => {
        if (value === 'clear') {
            input.value = '';
            return;
        }
        if (value === 'back') {
            input.value = input.value.slice(0, -1);
            return;
        }
        input.value += value;
    };

    keys.forEach((btn) => {
        btn.addEventListener('click', () => {
            applyKey(btn.dataset.key || '');
            input.focus();
        });
    });

    if (form && results && resultsTitle && resultsList) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const phone = input.value.trim();
            if (!phone) {
                if (window.showToast) {
                    window.showToast('error', kioskI18n.error, kioskI18n.pleaseEnterMobile);
                }
                return;
            }

            const csrfName = form.dataset.csrfName || '';
            const csrfToken = form.dataset.csrfToken || '';
            const formData = new FormData();
            formData.append('phone', phone);
            if (csrfName && csrfToken) {
                formData.append(csrfName, csrfToken);
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const data = await response.json();
                if (!data || !data.success) {
                    if (window.showToast) {
                        window.showToast('error', kioskI18n.error, data?.message || kioskI18n.lookupFailed);
                    }
                    return;
                }

                if (data.csrf_name && data.csrf_token) {
                    form.dataset.csrfName = data.csrf_name;
                    form.dataset.csrfToken = data.csrf_token;
                }

                resultsTitle.textContent = `${kioskI18n.resultsFor} ${data.lookup_phone || ''}`;
                if (phonePreview) {
                    phonePreview.textContent = data.lookup_phone || '';
                }
                clearNode(resultsList);

                if (data.appointments && data.appointments.length) {
                    data.appointments.forEach((appointment) => {
                        resultsList.appendChild(createAppointmentRow(appointment, form));
                    });
                } else {
                    const empty = document.createElement('div');
                    empty.className = 'rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-700';
                    empty.textContent = data.info_message || kioskI18n.noUpcomingForMobile;
                    resultsList.appendChild(empty);
                }

                const patientDetails = document.getElementById('kioskPatientDetails');
                const patientSelect = document.getElementById('kioskPatientSelect');
                const phoneUpdate = document.getElementById('kioskPhoneUpdate');
                const emailUpdate = document.getElementById('kioskEmailUpdate');
                let hasPatients = false;
                if (patientDetails && patientSelect) {
                    clearNode(patientSelect);
                    (data.patients || []).forEach((patient) => {
                        const option = document.createElement('option');
                        option.value = patient.id;
                        option.dataset.phone = patient.phone || '';
                        option.dataset.email = patient.email || '';
                        option.textContent = patient.name || '';
                        patientSelect.appendChild(option);
                    });
                    if ((data.patients || []).length) {
                        hasPatients = true;
                        patientDetails.classList.remove('hidden');
                        const selected = patientSelect.options[0];
                        if (selected) {
                            phoneUpdate.value = selected.dataset.phone || '';
                            emailUpdate.value = selected.dataset.email || '';
                        }
                    } else {
                        patientDetails.classList.add('hidden');
                    }
                }

                const contractsWrap = document.getElementById('kioskContracts');
                const contractsList = document.getElementById('kioskContractList');
                const balanceCard = document.getElementById('kioskBalanceSummaryCard');
                const balanceAmount = document.getElementById('kioskBalanceAmount');
                const balanceHelp = document.getElementById('kioskBalanceHelp');
                const balanceIcon = document.getElementById('kioskBalanceIcon');
                let hasContracts = false;
                if (contractsWrap && contractsList) {
                    clearNode(contractsList);
                    (data.contracts || []).forEach((contract) => {
                        const card = document.createElement('div');
                        card.className = 'flex flex-col gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-5 md:flex-row md:items-center md:justify-between';

                        const info = createElement('div', 'space-y-1');
                        const title = createElement('p', 'text-sm font-semibold text-slate-900', `${kioskI18n.planPrefix}${contract.id}`);
                        const totalValue = Number(contract.total_cost || 0).toFixed(2);
                        const total = createElement('p', 'text-sm text-slate-500', `${kioskI18n.total}: ${contract.currency} ${totalValue}`);
                        info.appendChild(title);
                        info.appendChild(total);

                        const button = createElement('button', 'kiosk-sign-btn inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700');
                        button.type = 'button';
                        const icon = createElement('i', 'fas fa-pen-fancy');
                        button.appendChild(icon);
                        appendText(button, kioskI18n.reviewAndSign);

                        card.appendChild(info);
                        card.appendChild(button);

                        button.addEventListener('click', () => {
                            openContractModal(contract);
                        });
                        contractsList.appendChild(card);
                    });
                    if ((data.contracts || []).length) {
                        hasContracts = true;
                        contractsWrap.classList.remove('hidden');
                    } else {
                        contractsWrap.classList.add('hidden');
                    }
                }

                refreshKioskActionBar({
                    hasAppointments: !!(data.appointments && data.appointments.length),
                    hasPatients,
                    hasContracts,
                    hasConfirmable: !!(data.appointments || []).some((item) => !!item.can_confirm),
                });

                if (balanceCard && balanceAmount && balanceHelp && balanceIcon) {
                    const summary = data.balance_summary || null;
                    if (!summary) {
                        balanceCard.classList.add('hidden');
                    } else {
                        balanceCard.classList.remove('hidden');
                        const hasDue = !!summary.has_due;
                        balanceCard.classList.remove('border-amber-200', 'bg-amber-50', 'border-slate-200', 'bg-white');
                        balanceIcon.classList.remove('bg-amber-100', 'text-amber-700', 'bg-emerald-100', 'text-emerald-700');
                        balanceAmount.classList.remove('text-amber-900', 'text-emerald-700');
                        balanceHelp.classList.remove('text-amber-800', 'text-slate-500');
                        const iconNode = balanceIcon.querySelector('i');
                        if (iconNode) {
                            iconNode.className = `fas ${hasDue ? 'fa-wallet' : 'fa-circle-check'}`;
                        }
                        if (hasDue) {
                            balanceCard.classList.add('border-amber-200', 'bg-amber-50');
                            balanceIcon.classList.add('bg-amber-100', 'text-amber-700');
                            balanceAmount.classList.add('text-amber-900');
                            balanceHelp.classList.add('text-amber-800');
                            balanceAmount.textContent = summary.outstanding_formatted || '';
                            balanceHelp.textContent = kioskI18n.balancePayAtReception;
                        } else {
                            balanceCard.classList.add('border-slate-200', 'bg-white');
                            balanceIcon.classList.add('bg-emerald-100', 'text-emerald-700');
                            balanceAmount.classList.add('text-emerald-700');
                            balanceHelp.classList.add('text-slate-500');
                            balanceAmount.textContent = kioskI18n.balanceClearTitle;
                            balanceHelp.textContent = kioskI18n.balanceLookupHelp;
                        }
                    }
                }

                results.classList.remove('hidden');
            } catch (error) {
                if (window.showToast) {
                    window.showToast('error', kioskI18n.error, kioskI18n.lookupFailedTryAgain);
                }
            }
        });
    }
});

const kioskSessionRoot = document.getElementById('kioskSessionRoot');
if (kioskSessionRoot?.dataset.pinVerified === '1') {
    const resetUrl = kioskSessionRoot.dataset.resetUrl || '';
    const timeoutMs = Number(kioskSessionRoot.dataset.timeoutMs || '90000');
    let inactivityTimer = null;

    const restartInactivityTimer = () => {
        if (!resetUrl || !timeoutMs) return;
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        inactivityTimer = setTimeout(() => {
            window.location.href = resetUrl;
        }, timeoutMs);
    };

    ['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, restartInactivityTimer, { passive: true });
    });
    restartInactivityTimer();
}

const patientSelect = document.getElementById('kioskPatientSelect');
if (patientSelect) {
    patientSelect.addEventListener('change', () => {
        const option = patientSelect.options[patientSelect.selectedIndex];
        document.getElementById('kioskPhoneUpdate').value = option?.dataset?.phone || '';
        document.getElementById('kioskEmailUpdate').value = option?.dataset?.email || '';
    });
}

const detailsForm = document.getElementById('kioskDetailsForm');
if (detailsForm) {
    detailsForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const formData = new FormData(detailsForm);
            const csrfName = detailsForm.dataset.csrfName || '';
            const csrfToken = detailsForm.dataset.csrfToken || '';
            if (csrfName && csrfToken) {
                formData.set(csrfName, csrfToken);
            }

            const response = await fetch(detailsForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await response.json().catch(() => null);
            if (data?.csrf_name && data?.csrf_token) {
                detailsForm.dataset.csrfName = data.csrf_name;
                detailsForm.dataset.csrfToken = data.csrf_token;
            }

            if (window.showToast) {
                if (response.ok && data?.success) {
                    window.showToast('success', kioskI18n.updated, data.message || kioskI18n.updated);
                } else {
                    window.showToast('error', kioskI18n.error, data?.message || kioskI18n.unableUpdateDetails);
                }
            }
        } catch (error) {
            if (window.showToast) {
                window.showToast('error', kioskI18n.error, kioskI18n.unableUpdateDetailsNow);
            }
        }
    });
}

const kioskModal = document.getElementById('kioskContractModal');
const kioskClose = document.getElementById('kioskCloseContract');
const kioskMeta = document.getElementById('kioskContractMeta');
const kioskSnapshot = document.getElementById('kioskContractSnapshot');
const kioskItems = document.getElementById('kioskContractItems');
const kioskForm = document.getElementById('kioskContractForm');
const kioskContractId = document.getElementById('kioskContractId');
const kioskPatientId = document.getElementById('kioskContractPatientId');
const kioskSignerName = document.getElementById('kioskSignerName');
const kioskSignatureCanvas = document.getElementById('kioskSignatureCanvas');
const kioskSignatureData = document.getElementById('kioskSignatureData');
const kioskClearSignature = document.getElementById('kioskClearSignature');

const openContractModal = (contract) => {
    if (!kioskModal) return;
    kioskContractId.value = contract.id;
    kioskPatientId.value = contract.patient_id;
    kioskMeta.textContent = `${kioskI18n.planPrefix}${contract.id} - ${contract.currency} ${Number(contract.total_cost).toFixed(2)}`;
    while (kioskSnapshot.firstChild) {
        kioskSnapshot.removeChild(kioskSnapshot.firstChild);
    }
    if (contract.snapshot_svg) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contract.snapshot_svg, 'image/svg+xml');
        const svg = doc.querySelector('svg');
        if (svg) {
            kioskSnapshot.appendChild(document.importNode(svg, true));
        }
    } else {
        const emptySnapshot = document.createElement('p');
        emptySnapshot.className = 'text-sm text-gray-500';
        emptySnapshot.textContent = kioskI18n.noSnapshot;
        kioskSnapshot.appendChild(emptySnapshot);
    }
    while (kioskItems.firstChild) {
        kioskItems.removeChild(kioskItems.firstChild);
    }
    (contract.items || []).forEach((item) => {
        const row = document.createElement('tr');
        const toothCell = document.createElement('td');
        toothCell.className = 'px-3 py-3';
        toothCell.textContent = item.tooth_no || '-';
        const descCell = document.createElement('td');
        descCell.className = 'px-3 py-3';
        descCell.textContent = item.description || '-';
        const qtyCell = document.createElement('td');
        qtyCell.className = 'px-3 py-3';
        qtyCell.textContent = item.qty || 1;
        const priceCell = document.createElement('td');
        priceCell.className = 'px-3 py-3';
        priceCell.textContent = Number(item.price || 0).toFixed(2);
        row.appendChild(toothCell);
        row.appendChild(descCell);
        row.appendChild(qtyCell);
        row.appendChild(priceCell);
        kioskItems.appendChild(row);
    });
    if (!kioskItems.children.length) {
        const empty = document.createElement('tr');
        const emptyCell = document.createElement('td');
        emptyCell.colSpan = 4;
        emptyCell.className = 'py-3 text-center text-gray-500';
        emptyCell.textContent = kioskI18n.noItems;
        empty.appendChild(emptyCell);
        kioskItems.appendChild(empty);
    }
    if (kioskSignatureCanvas) {
        const ctx = kioskSignatureCanvas.getContext('2d');
        ctx.clearRect(0, 0, kioskSignatureCanvas.width, kioskSignatureCanvas.height);
    }
    if (kioskSignerName) {
        kioskSignerName.value = contract.patient_name || '';
    }
    kioskSignatureData.value = '';
    kioskModal.classList.remove('hidden');
};

document.querySelectorAll('.kiosk-sign-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const payload = btn.getAttribute('data-contract');
        if (!payload) return;
        openContractModal(JSON.parse(payload));
    });
});

kioskClose?.addEventListener('click', () => {
    kioskModal?.classList.add('hidden');
});

kioskModal?.addEventListener('click', (event) => {
    if (event.target === kioskModal) {
        kioskModal.classList.add('hidden');
    }
});

if (kioskSignatureCanvas) {
    const ctx = kioskSignatureCanvas.getContext('2d');
    let drawing = false;
    kioskSignatureCanvas.style.touchAction = 'none';
    const getPos = (e) => {
        const rect = kioskSignatureCanvas.getBoundingClientRect();
        const point = e.touches ? e.touches[0] : e;
        const scaleX = kioskSignatureCanvas.width / rect.width;
        const scaleY = kioskSignatureCanvas.height / rect.height;
        return {
            x: (point.clientX - rect.left) * scaleX,
            y: (point.clientY - rect.top) * scaleY,
        };
    };
    const start = (e) => {
        if (e.cancelable) e.preventDefault();
        drawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    };
    const draw = (e) => {
        if (e.cancelable) e.preventDefault();
        if (!drawing) return;
        const pos = getPos(e);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#111827';
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    };
    const end = (e) => {
        if (e && e.cancelable) e.preventDefault();
        drawing = false;
    };

    kioskSignatureCanvas.addEventListener('mousedown', start);
    kioskSignatureCanvas.addEventListener('mousemove', draw);
    kioskSignatureCanvas.addEventListener('mouseup', end);
    kioskSignatureCanvas.addEventListener('mouseleave', end);
    kioskSignatureCanvas.addEventListener('touchstart', start, { passive: false });
    kioskSignatureCanvas.addEventListener('touchmove', draw, { passive: false });
    kioskSignatureCanvas.addEventListener('touchend', end, { passive: false });
    kioskSignatureCanvas.addEventListener('touchcancel', end, { passive: false });

    kioskClearSignature?.addEventListener('click', () => {
        ctx.clearRect(0, 0, kioskSignatureCanvas.width, kioskSignatureCanvas.height);
    });
}

if (kioskForm) {
    kioskForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            if (kioskSignatureCanvas) {
                kioskSignatureData.value = kioskSignatureCanvas.toDataURL('image/png');
            }
            const formData = new FormData(kioskForm);
            const csrfName = kioskForm.dataset.csrfName || '';
            const csrfToken = kioskForm.dataset.csrfToken || '';
            if (csrfName && csrfToken) {
                formData.set(csrfName, csrfToken);
            }
            const response = await fetch(kioskForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await response.json().catch(() => null);
            if (data?.csrf_name && data?.csrf_token) {
                kioskForm.dataset.csrfName = data.csrf_name;
                kioskForm.dataset.csrfToken = data.csrf_token;
            }
            if (window.showToast) {
                if (response.ok && data?.success) {
                    window.showToast('success', kioskI18n.signed, data.message || kioskI18n.signed);
                } else {
                    window.showToast('error', kioskI18n.error, data?.message || kioskI18n.unableSignPlan);
                }
            }
            if (response.ok && data?.success) {
                kioskModal?.classList.add('hidden');
            }
        } catch (error) {
            if (window.showToast) {
                window.showToast('error', kioskI18n.error, kioskI18n.unableSignPlanNow);
            }
        }
    });
}
</script>
<?= $this->endSection() ?>

