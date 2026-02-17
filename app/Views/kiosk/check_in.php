<?= $this->extend('layouts/kiosk') ?>

<?= $this->section('content') ?>
<div id="kioskSessionRoot"
     class="min-h-screen"
     data-pin-verified="<?= !empty($pin_verified) ? '1' : '0' ?>"
     data-reset-url="<?= esc(base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset')) ?>"
     data-timeout-ms="90000">
    <div class="container mx-auto px-6 py-10">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-end gap-2">
                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in?lang=ar') ?>"
                   class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition-all <?= ($locale ?? 'ar') === 'ar' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' ?>">
                    العربية
                </a>
                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in?lang=en') ?>"
                   class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition-all <?= ($locale ?? 'ar') === 'en' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' ?>">
                    English
                </a>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-8">
                    <?php
                    $logoPath = $settings['clinic_logo_path'] ?? '';
                    $logoSrc = '';
                    if (!empty($logoPath)) {
                        $logoSrc = (strpos($logoPath, 'http://') === 0 || strpos($logoPath, 'https://') === 0)
                            ? $logoPath
                            : base_url(ltrim($logoPath, '/'));
                    }
                    ?>
                    <div class="grid grid-cols-3 items-center gap-4" dir="ltr">
                        <div class="text-left">
                            <h1 class="text-xl font-bold text-white">
                                <?= esc(lang('Kiosk.headerEnglish')) ?>
                            </h1>
                        </div>
                        <div class="flex justify-center">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                <?php if ($logoSrc): ?>
                                    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($settings['clinic_name'] ?? $clinic['name']) ?>" class="w-10 h-10 object-contain">
                                <?php else: ?>
                                    <i class="fas fa-tooth text-white text-2xl"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <h1 class="text-xl font-bold text-white" dir="rtl">
                                <?= esc(lang('Kiosk.headerArabic')) ?>
                            </h1>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-white text-lg font-bold" dir="auto" style="unicode-bidi: plaintext;">
                            <?= esc($settings['clinic_name'] ?? $clinic['name']) ?>
                        </p>
                        <p class="text-blue-100 text-sm mt-1" dir="auto" style="unicode-bidi: plaintext;">
                            <?= esc($settings['kiosk_subheader'] ?? 'Confirm your appointment in seconds.') ?>
                        </p>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <?php if (!($pin_verified ?? false)): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6">
                            <h2 class="text-lg font-semibold text-blue-900 mb-2"><?= esc(lang('Kiosk.enterClinicPin')) ?></h2>
                            <p class="text-sm text-blue-700"><?= esc(lang('Kiosk.kioskProtected')) ?></p>
                        </div>
                        <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/unlock') ?>" method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.clinicPin')) ?></label>
                                <input type="password" name="kiosk_pin" inputmode="numeric" class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-lg" placeholder="<?= esc(lang('Kiosk.enterPin')) ?>" required>
                            </div>
                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                <i class="fas fa-lock-open mr-2"></i><?= esc(lang('Kiosk.unlockKiosk')) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="bg-white border border-slate-200 rounded-2xl p-6">
                            <h2 class="text-lg font-semibold text-slate-900 mb-2"><?= esc(lang('Kiosk.checkIn')) ?></h2>
                            <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.checkInHint')) ?></p>
                        </div>
                        <form id="kioskLookupForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/lookup') ?>" method="POST" class="space-y-4" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.mobileNumber')) ?></label>
                                <input id="kioskPhoneInput" type="tel" name="phone" inputmode="numeric" class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-lg tracking-wider" placeholder="<?= esc(lang('Kiosk.mobilePlaceholder')) ?>" required>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4" dir="ltr">
                                <div class="grid grid-cols-3 gap-3" dir="ltr">
                                    <?php foreach (['1','2','3','4','5','6','7','8','9','clear','0','back'] as $key): ?>
                                        <?php if ($key === 'clear'): ?>
                                            <button type="button" data-key="clear" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-all"><?= esc(lang('Kiosk.clear')) ?></button>
                                        <?php elseif ($key === 'back'): ?>
                                            <button type="button" data-key="back" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-all"><?= esc(lang('Kiosk.back')) ?></button>
                                        <?php else: ?>
                                            <button type="button" data-key="<?= $key ?>" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-lg font-bold text-slate-800 hover:bg-slate-100 transition-all"><?= $key ?></button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                <i class="fas fa-user-check mr-2"></i><?= esc(lang('Kiosk.findRecord')) ?>
                            </button>
                        </form>

                        <?php
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
                        ?>
                        <div id="kioskResults" class="border-t border-gray-100 pt-6 <?= $hasAppointments || $hasPatients || !empty($info_message) ? '' : 'hidden' ?>">
                            <div class="flex items-center justify-between mb-4">
                                <h3 id="kioskResultsTitle" class="text-lg font-semibold text-slate-900"><?= esc(lang('Kiosk.resultsFor')) ?> <?= esc($lookup_phone ?? '') ?></h3>
                                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                    <?= esc(lang('Kiosk.notYouReenter')) ?>
                                </a>
                            </div>
                            <div id="kioskActionBar"
                                 class="mb-5 grid grid-cols-1 sm:grid-cols-3 gap-2"
                                 data-has-appointments="<?= $hasAppointments ? '1' : '0' ?>"
                                 data-has-patients="<?= $hasPatients ? '1' : '0' ?>"
                                 data-has-contracts="<?= $hasContracts ? '1' : '0' ?>"
                                 data-has-confirmable="<?= $hasConfirmable ? '1' : '0' ?>"
                                 data-primary-action="<?= esc($primaryAction) ?>">
                                <button type="button"
                                        data-kiosk-action-btn
                                        data-action="checkin"
                                        class="px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all duration-150">
                                    <?= esc(lang('Kiosk.actionConfirmVisit')) ?>
                                </button>
                                <button type="button"
                                        data-kiosk-action-btn
                                        data-action="details"
                                        class="px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all duration-150">
                                    <?= esc(lang('Kiosk.actionUpdateDetails')) ?>
                                </button>
                                <button type="button"
                                        data-kiosk-action-btn
                                        data-action="contracts"
                                        class="px-4 py-2.5 rounded-xl border text-sm font-semibold transition-all duration-150">
                                    <?= esc(lang('Kiosk.actionReviewSign')) ?>
                                </button>
                            </div>
                            <div id="kioskResultsList" class="space-y-4">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                        <div>
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
                                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg">
                                                    <i class="fas fa-check-circle mr-2"></i><?= esc(lang('Kiosk.confirmToday')) ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold text-slate-500"><?= esc(lang('Kiosk.confirmAvailableOnDay')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($appointments) && !empty($info_message)): ?>
                                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm">
                                        <?= esc($info_message) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div id="kioskPatientDetails" class="mt-6 <?= !empty($patients) ? '' : 'hidden' ?>">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-2"><?= esc(lang('Kiosk.updateYourDetails')) ?></h3>
                                    <p class="text-sm text-slate-500 mb-4"><?= esc(lang('Kiosk.detailsHint')) ?></p>
                                    <form id="kioskDetailsForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/update-details') ?>" method="POST" class="space-y-4" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                                        <?= csrf_field() ?>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.selectPatient')) ?></label>
                                            <select name="patient_id" id="kioskPatientSelect" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                                <?php foreach ($patients ?? [] as $patient): ?>
                                                    <option value="<?= (int) $patient['id'] ?>" data-phone="<?= esc($patient['phone'] ?? '') ?>" data-email="<?= esc($patient['email'] ?? '') ?>">
                                                        <?= esc($patient['name'] ?? '') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.mobileNumber')) ?></label>
                                                <input type="tel" name="phone" id="kioskPhoneUpdate" inputmode="numeric" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.email')) ?></label>
                                                <input type="email" name="email" id="kioskEmailUpdate" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                            </div>
                                        </div>
                                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                            <i class="fas fa-save mr-2"></i><?= esc(lang('Kiosk.updateDetailsButton')) ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="kioskContracts" class="mt-6 <?= !empty($contracts) ? '' : 'hidden' ?>">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-2"><?= esc(lang('Kiosk.treatmentPlan')) ?></h3>
                                    <p class="text-sm text-slate-500 mb-4"><?= esc(lang('Kiosk.treatmentPlanHint')) ?></p>
                                    <div id="kioskContractList" class="space-y-4">
                                        <?php foreach ($contracts ?? [] as $contract): ?>
                                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-900"><?= esc(lang('Kiosk.planPrefix')) ?><?= (int) $contract['id'] ?></p>
                                                        <p class="text-sm text-slate-500"><?= esc(lang('Kiosk.total')) ?>: <?= esc($contract['currency']) ?> <?= number_format((float) $contract['total_cost'], 2) ?></p>
                                                    </div>
                                                    <button type="button" class="kiosk-sign-btn px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all"
                                                            data-contract='<?= esc(json_encode($contract), 'attr') ?>'>
                                                        <i class="fas fa-pen-fancy mr-2"></i><?= esc(lang('Kiosk.reviewAndSign')) ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center text-xs text-slate-500" dir="auto" style="unicode-bidi: plaintext;">
                <?= esc($settings['kiosk_footer_note'] ?? lang('Kiosk.needHelp')) ?>
            </div>
        </div>
    </div>
</div>

<div id="kioskContractModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-6 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold"><?= esc(lang('Kiosk.treatmentPlan')) ?></h3>
                    <p class="text-sm text-blue-100" id="kioskContractMeta"></p>
                </div>
                <button type="button" id="kioskCloseContract" class="w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="p-6 space-y-6 overflow-y-auto">
                <div id="kioskContractSnapshot" class="border border-gray-200 rounded-2xl p-4 bg-gray-50"></div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-3"><?= esc(lang('Kiosk.planItems')) ?></h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-gray-500 uppercase text-xs">
                                    <th class="py-2 px-3 text-left"><?= esc(lang('Kiosk.tooth')) ?></th>
                                    <th class="py-2 px-3 text-left"><?= esc(lang('Kiosk.description')) ?></th>
                                    <th class="py-2 px-3 text-left"><?= esc(lang('Kiosk.qty')) ?></th>
                                    <th class="py-2 px-3 text-left"><?= esc(lang('Kiosk.price')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="kioskContractItems" class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                </div>
                <form id="kioskContractForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/sign-contract') ?>" method="POST" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="contract_id" id="kioskContractId">
                    <input type="hidden" name="patient_id" id="kioskContractPatientId">
                    <input type="hidden" name="signature_data" id="kioskSignatureData">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.typedName')) ?></label>
                            <input type="text" name="signer_name" id="kioskSignerName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required>
                        </div>
                        <div class="flex items-end text-sm text-gray-500">
                            <?= esc(lang('Kiosk.signHint')) ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Kiosk.drawSignature')) ?></label>
                        <canvas id="kioskSignatureCanvas" class="border border-gray-300 rounded-xl w-full" height="160" style="touch-action: none; -ms-touch-action: none;"></canvas>
                        <button type="button" id="kioskClearSignature"
                                class="mt-2 inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-eraser mr-1.5 text-gray-500"></i><?= esc(lang('Kiosk.clearSignature')) ?>
                        </button>
                    </div>
                    <button type="submit" class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all">
                        <i class="fas fa-signature mr-2"></i><?= esc(lang('Kiosk.signPlan')) ?>
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
        'bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-sm',
        'bg-white', 'text-slate-700', 'border-slate-200', 'hover:bg-slate-50',
        'opacity-50', 'cursor-not-allowed'
    );

    if (!enabled) {
        button.classList.add('bg-white', 'text-slate-400', 'border-slate-200', 'opacity-50', 'cursor-not-allowed');
        return;
    }

    if (active) {
        button.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-sm');
        return;
    }

    button.classList.add('bg-white', 'text-slate-700', 'border-slate-200', 'hover:bg-slate-50');
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
    const resultsList = document.getElementById('kioskResultsList');
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

    keys.forEach(btn => {
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
                clearNode(resultsList);

                if (data.appointments && data.appointments.length) {
                    let hasConfirmable = false;
                    data.appointments.forEach((appointment) => {
                        const row = document.createElement('div');
                        row.className = 'flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-5';

                        const info = document.createElement('div');
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
                            hasConfirmable = true;
                            const confirmForm = document.createElement('form');
                            confirmForm.method = 'POST';
                            confirmForm.action = '<?= base_url('kiosk/' . $clinic['uuid'] . '/confirm') ?>';

                            const confirmCsrf = document.createElement('input');
                            confirmCsrf.type = 'hidden';
                            const confirmCsrfName = form.dataset.csrfName || '<?= csrf_token() ?>';
                            const confirmCsrfToken = form.dataset.csrfToken || '<?= csrf_hash() ?>';
                            confirmCsrf.name = confirmCsrfName;
                            confirmCsrf.value = confirmCsrfToken;

                            const appointmentId = document.createElement('input');
                            appointmentId.type = 'hidden';
                            appointmentId.name = 'appointment_id';
                            appointmentId.value = appointment.id || '';

                            const confirmBtn = document.createElement('button');
                            confirmBtn.type = 'submit';
                            confirmBtn.className = 'px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg';

                            const icon = document.createElement('i');
                            icon.className = 'fas fa-check-circle mr-2';
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
                        resultsList.appendChild(row);
                    });
                } else {
                    const empty = document.createElement('div');
                    empty.className = 'bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm';
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
                let hasContracts = false;
                if (contractsWrap && contractsList) {
                    clearNode(contractsList);
                    (data.contracts || []).forEach((contract) => {
                        const card = document.createElement('div');
                        card.className = 'bg-slate-50 border border-slate-200 rounded-2xl p-5';
                        const row = createElement('div', 'flex flex-col md:flex-row md:items-center md:justify-between gap-4');
                        const info = createElement('div');
                        const title = createElement('p', 'text-sm font-semibold text-slate-900', `${kioskI18n.planPrefix}${contract.id}`);
                        const totalValue = Number(contract.total_cost || 0).toFixed(2);
                        const total = createElement('p', 'text-sm text-slate-500', `${kioskI18n.total}: ${contract.currency} ${totalValue}`);
                        info.appendChild(title);
                        info.appendChild(total);

                        const button = createElement('button', 'kiosk-sign-btn px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all');
                        button.type = 'button';
                        const icon = createElement('i', 'fas fa-pen-fancy mr-2');
                        button.appendChild(icon);
                        appendText(button, kioskI18n.reviewAndSign);

                        row.appendChild(info);
                        row.appendChild(button);
                        card.appendChild(row);

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
        toothCell.className = 'py-2 px-3';
        toothCell.textContent = item.tooth_no || '-';
        const descCell = document.createElement('td');
        descCell.className = 'py-2 px-3';
        descCell.textContent = item.description || '-';
        const qtyCell = document.createElement('td');
        qtyCell.className = 'py-2 px-3';
        qtyCell.textContent = item.qty || 1;
        const priceCell = document.createElement('td');
        priceCell.className = 'py-2 px-3';
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

document.querySelectorAll('.kiosk-sign-btn').forEach(btn => {
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
