<?= $this->extend('layouts/kiosk') ?>
<?= $this->section('content') ?>
<?php $localeCode=$locale??'ar';$isArabic=str_starts_with($localeCode,'ar');$kk=static fn($key)=>esc(lang('Studio.kk'.$key));$hasAppointments=!empty($appointments);$hasPatients=!empty($patients);$hasContracts=!empty($contracts);$hasConfirmable=false;foreach($appointments??[] as $appointment){if(!empty($appointment['can_confirm'])){$hasConfirmable=true;break;}}$primaryAction=$hasConfirmable?'checkin':($hasPatients?'details':($hasContracts?'contracts':'checkin'));$balanceSummary=$balance_summary??null;$completionState=$completion??null;$completionBalance=$completionState['balance_summary']??null;$logoPath=$settings['clinic_logo_path']??'';$logoSrc=$logoPath!==''?((str_starts_with($logoPath,'http://')||str_starts_with($logoPath,'https://'))?$logoPath:base_url(ltrim($logoPath,'/'))):''; ?>
<div id="kioskSessionRoot" data-pin-verified="<?= !empty($pin_verified)?'1':'0' ?>" data-reset-url="<?= esc(base_url('kiosk/'.$clinic['uuid'].'/check-in/reset')) ?>" data-timeout-ms="90000">
<header class="kiosk-header">
<div class="kiosk-clinic-brand">
<?php if($logoSrc): ?>
<img src="<?= esc($logoSrc,'attr') ?>" alt="<?= esc($settings['clinic_name']??$clinic['name'],'attr') ?>">
<?php else: ?>
<span class="kiosk-brand-mark">denta+</span>
<?php endif; ?>
<span>
<?= esc($settings['clinic_name']??$clinic['name']) ?>
</span>
</div>
<?php if(!empty($pin_verified)): ?><a class="kiosk-button secondary" href="<?= base_url('kiosk/'.$clinic['uuid'].'/check-in/reset') ?>"><?= esc(lang('Kiosk.notYouReenter')) ?></a><?php endif; ?><a class="kiosk-button secondary" href="<?= base_url('kiosk/'.$clinic['uuid'].'/check-in?lang='.($isArabic?'en':'ar')) ?>" lang="<?= $isArabic?'en':'ar' ?>">
<?= $isArabic?'English':'العربية' ?>
</a>
</header>
<main class="kiosk-main" id="main">
<div class="kiosk-welcome">
<h1>
<?= esc(lang('Kiosk.title')) ?>
</h1>
<p>
<?= $kk('Welcome') ?>
</p>
</div>
<?php foreach(['success','error'] as $kind):if(session($kind)): ?>
<p class="kiosk-note <?= $kind==='error'?'is-error':'' ?>" role="status">
<?= esc(session($kind)) ?>
</p>
<?php endif;endforeach; ?>
<?php if(!($pin_verified??false)): ?>
<section class="kiosk-unlock kiosk-panel">
<div>
<h2>
<?= esc(lang('Kiosk.enterClinicPin')) ?>
</h2>
<p>
<?= esc(lang('Kiosk.kioskProtected')) ?>
</p>
<p class="kiosk-help">
<?= $kk('ReceptionHelp') ?>
</p>
</div>
<form class="kiosk-form" action="<?= base_url('kiosk/'.$clinic['uuid'].'/unlock') ?>" method="POST">
<?= csrf_field() ?>
<label for="kiosk-pin">
<?= esc(lang('Kiosk.clinicPin')) ?>
</label>
<input id="kiosk-pin" type="password" name="kiosk_pin" inputmode="numeric" placeholder="<?= esc(lang('Kiosk.enterPin'),'attr') ?>" required autocomplete="off">
<button class="kiosk-button" type="submit">
<?= esc(lang('Kiosk.unlockKiosk')) ?>
</button>
</form>
</section>
<?php elseif(!empty($completionState)): ?>
<section class="kiosk-panel kiosk-completed">
<h2>
<?= esc(lang('Kiosk.completionTitle')) ?>
</h2>
<p>
<?= esc($completionState['message']??lang('Kiosk.msgAppointmentConfirmed')) ?>
</p>
<div class="kiosk-completion-facts">
<?php foreach(['patient_name','appointment_date','appointment_time','appointment_type'] as $field):if(!empty($completionState[$field])): ?>
<span>
<?= esc($completionState[$field]) ?>
</span>
<?php endif;endforeach; ?>
</div>
<p>
<?= esc(lang('Kiosk.completionNextStepHelp')) ?>
</p>
<div class="kiosk-balance <?= !empty($completionBalance['has_due'])?'has-due':'' ?>">
<h3>
<?= esc(lang('Kiosk.balanceCardTitle')) ?>
</h3>
<strong>
<?= !empty($completionBalance['has_due'])?esc($completionBalance['outstanding_formatted']??formatCurrency(0)):esc(lang('Kiosk.balanceClearTitle')) ?>
</strong>
<p>
<?= esc(lang(!empty($completionBalance['has_due'])?'Kiosk.balancePayAtReception':'Kiosk.balanceClearHelp')) ?>
</p>
</div>
<a class="kiosk-button" href="<?= base_url('kiosk/'.$clinic['uuid'].'/check-in/reset') ?>">
<?= esc(lang('Kiosk.notYouReenter')) ?>
</a>
</section>
<?php else: ?>
<div class="kiosk-find-layout">
<section class="kiosk-panel">
<h2>
<?= esc(lang('Kiosk.checkIn')) ?>
</h2>
<p>
<?= esc(lang('Kiosk.checkInHint')) ?>
</p>
<form class="kiosk-form" id="kioskLookupForm" action="<?= base_url('kiosk/'.$clinic['uuid'].'/lookup') ?>" method="POST" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
<?= csrf_field() ?>
<label for="kioskPhoneInput">
<?= esc(lang('Kiosk.mobileNumber')) ?>
</label>
<input id="kioskPhoneInput" type="tel" name="phone" inputmode="numeric" placeholder="<?= esc(lang('Kiosk.mobilePlaceholder'),'attr') ?>" required autocomplete="off">
<div class="kiosk-keypad" aria-label="<?= $kk('Keypad') ?>">
<?php foreach(['1','2','3','4','5','6','7','8','9','clear','0','back'] as $key): ?>
<button class="kiosk-key" type="button" data-key="<?= $key ?>">
<?= esc($key==='clear'?lang('Kiosk.clear'):($key==='back'?lang('Kiosk.back'):$key)) ?>
</button>
<?php endforeach; ?>
</div>
<button class="kiosk-button" type="submit">
<?= esc(lang('Kiosk.findRecord')) ?>
</button>
</form>
</section>
<aside class="kiosk-panel kiosk-task-panel">
<h2>
<?= $kk('AvailableTasks') ?>
</h2>
<nav id="kioskActionBar" data-has-appointments="<?= $hasAppointments?'1':'0' ?>" data-has-patients="<?= $hasPatients?'1':'0' ?>" data-has-contracts="<?= $hasContracts?'1':'0' ?>" data-has-confirmable="<?= $hasConfirmable?'1':'0' ?>" data-primary-action="<?= esc($primaryAction) ?>">
<?php foreach(['checkin'=>'actionConfirmVisit','details'=>'actionUpdateDetails','contracts'=>'actionReviewSign'] as $action=>$key): ?>
<button type="button" class="kiosk-task" data-kiosk-action-btn data-action="<?= $action ?>">
<?= esc(lang('Kiosk.'.$key)) ?>
</button>
<?php endforeach; ?>
</nav>
<p class="kiosk-help">
<?= $kk('TasksHelp') ?>
</p>
<div class="kiosk-current-record">
<span>
<?= esc(lang('Kiosk.mobileNumber')) ?>
</span>
<strong id="kioskLookupPhonePreview">
<?= esc($lookup_phone??'—') ?>
</strong>
</div>
<div id="kioskBalanceSummaryCard" class="kiosk-balance <?= !$balanceSummary?'hidden':'' ?> <?= !empty($balanceSummary['has_due'])?'has-due':'' ?>">
<span id="kioskBalanceIcon" aria-hidden="true">
</span>
<h3>
<?= esc(lang('Kiosk.balanceCardTitle')) ?>
</h3>
<strong id="kioskBalanceAmount">
<?= esc(!empty($balanceSummary['has_due'])?($balanceSummary['outstanding_formatted']??formatCurrency(0)):lang('Kiosk.balanceClearTitle')) ?>
</strong>
<p id="kioskBalanceHelp">
<?= esc(lang(!empty($balanceSummary['has_due'])?'Kiosk.balancePayAtReception':'Kiosk.balanceLookupHelp')) ?>
</p>
</div>
</aside>
</div>
<div id="kioskResults" class="kiosk-results <?= $hasAppointments||$hasPatients||$hasContracts||!empty($info_message)?'':'hidden' ?>">
<header>
<h2 id="kioskResultsTitle">
<?= esc(lang('Kiosk.resultsFor')) ?> <?= esc($lookup_phone??'') ?>
</h2>
<a class="kiosk-button secondary" href="<?= base_url('kiosk/'.$clinic['uuid'].'/check-in/reset') ?>">
<?= esc(lang('Kiosk.notYouReenter')) ?>
</a>
</header>
<section id="kioskResultsList" class="kiosk-panel">
<h3>
<?= esc(lang('Kiosk.actionConfirmVisit')) ?>
</h3>
<p>
<?= esc(lang('Kiosk.confirmAvailableOnDay')) ?>
</p>
<div id="kioskAppointmentRows">
<?php foreach($appointments??[] as $appointment): ?>
<article class="kiosk-record">
<div class="kiosk-record-copy">
<strong dir="auto" style="unicode-bidi:plaintext">
<?= esc($appointment['patient_display']??'') ?>
</strong>
<p>
<?= esc($appointment['appointment_date']??'') ?> · <?= esc($appointment['appointment_time']??'') ?> · <?= esc($appointment['appointment_type']??'') ?>
<?php if(!empty($appointment['status'])): ?> · <?= esc($appointment['status_label']??$appointment['status']) ?>
<?php endif; ?>
</p>
</div>
<?php if(!empty($appointment['can_confirm'])): ?>
<form action="<?= base_url('kiosk/'.$clinic['uuid'].'/confirm') ?>" method="POST">
<?= csrf_field() ?>
<input type="hidden" name="appointment_id" value="<?= (int)($appointment['id']??0) ?>">
<button class="kiosk-button" type="submit">
<?= esc(lang('Kiosk.confirmToday')) ?>
</button>
</form>
<?php else: ?>
<span class="kiosk-muted">
<?= esc(lang('Kiosk.confirmAvailableOnDay')) ?>
</span>
<?php endif; ?>
</article>
<?php endforeach; ?>
<?php if(empty($appointments)): ?>
<p class="kiosk-note">
<?= esc($info_message??lang('Kiosk.noUpcomingForMobile')) ?>
</p>
<?php endif; ?>
</div>
</section>
<section id="kioskPatientDetails" class="kiosk-panel <?= !$hasPatients?'hidden':'' ?>">
<h3>
<?= esc(lang('Kiosk.updateYourDetails')) ?>
</h3>
<p>
<?= esc(lang('Kiosk.detailsHint')) ?>
</p>
<form class="kiosk-form" id="kioskDetailsForm" action="<?= base_url('kiosk/'.$clinic['uuid'].'/update-details') ?>" method="POST" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
<?= csrf_field() ?>
<div class="kiosk-detail-fields">
<div>
<label for="kioskPatientSelect">
<?= esc(lang('Kiosk.selectPatient')) ?>
</label>
<select name="patient_id" id="kioskPatientSelect">
<?php foreach($patients??[] as $patient): ?>
<option value="<?= (int)$patient['id'] ?>" data-phone="<?= esc($patient['phone']??'','attr') ?>" data-email="<?= esc($patient['email']??'','attr') ?>">
<?= esc($patient['name']??'') ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div>
<label for="kioskPhoneUpdate">
<?= esc(lang('Kiosk.mobileNumber')) ?>
</label>
<input type="tel" name="phone" id="kioskPhoneUpdate" inputmode="numeric">
</div>
<div>
<label for="kioskEmailUpdate">
<?= esc(lang('Kiosk.email')) ?>
</label>
<input type="email" name="email" id="kioskEmailUpdate">
</div>
</div>
<button class="kiosk-button" type="submit">
<?= esc(lang('Kiosk.updateDetailsButton')) ?>
</button>
</form>
</section>
<section id="kioskContracts" class="kiosk-panel <?= !$hasContracts?'hidden':'' ?>">
<h3>
<?= esc(lang('Kiosk.treatmentPlan')) ?>
</h3>
<p>
<?= esc(lang('Kiosk.treatmentPlanHint')) ?>
</p>
<div id="kioskContractList">
<?php foreach($contracts??[] as $contract): ?>
<article class="kiosk-record">
<div>
<strong>
<?= esc(lang('Kiosk.planPrefix')) ?>
<?= (int)$contract['id'] ?>
</strong>
<p>
<?= esc(lang('Kiosk.total')) ?>: <?= esc($contract['currency']) ?> <?= number_format((float)$contract['total_cost'],2) ?>
</p>
</div>
<button type="button" class="kiosk-sign-btn kiosk-button" data-contract='<?= esc(json_encode($contract),'attr') ?>'>
<?= esc(lang('Kiosk.reviewAndSign')) ?>
</button>
</article>
<?php endforeach; ?>
</div>
</section>
</div>
<?php endif; ?>
<footer class="kiosk-footer">
<p>
<?= $kk('Privacy') ?>
</p>
<p>
<?= $kk('ReceptionHelp') ?>
</p>
</footer>
</main>
<div id="kioskContractModal" class="kiosk-modal hidden" role="dialog" aria-modal="true" aria-labelledby="kioskContractTitle">
<div class="kiosk-modal-panel">
<header>
<div>
<h2 id="kioskContractTitle">
<?= esc(lang('Kiosk.treatmentPlan')) ?>
</h2>
<p id="kioskContractMeta">
</p>
</div>
<button type="button" class="kiosk-button secondary" id="kioskCloseContract">
<?= $kk('Close') ?>
</button>
</header>
<div id="kioskContractSnapshot" class="kiosk-snapshot">
</div>
<h3>
<?= esc(lang('Kiosk.planItems')) ?>
</h3>
<div class="kiosk-contract-table" tabindex="0" role="region" aria-label="<?= esc(lang('Kiosk.planItems'),'attr') ?>">
<table>
<thead>
<tr>
<?php foreach(['tooth','description','qty','price'] as $key): ?>
<th scope="col">
<?= esc(lang('Kiosk.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody id="kioskContractItems">
</tbody>
</table>
</div>
<form class="kiosk-form" id="kioskContractForm" action="<?= base_url('kiosk/'.$clinic['uuid'].'/sign-contract') ?>" method="POST" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
<?= csrf_field() ?>
<input type="hidden" name="contract_id" id="kioskContractId">
<input type="hidden" name="patient_id" id="kioskContractPatientId">
<input type="hidden" name="signature_data" id="kioskSignatureData">
<label for="kioskSignerName">
<?= esc(lang('Kiosk.typedName')) ?>
</label>
<input type="text" name="signer_name" id="kioskSignerName" required>
<p>
<?= esc(lang('Kiosk.signHint')) ?>
</p>
<label for="kioskSignatureCanvas">
<?= esc(lang('Kiosk.drawSignature')) ?>
</label>
<canvas id="kioskSignatureCanvas" height="160" style="touch-action:none;-ms-touch-action:none">
</canvas>
<div class="kiosk-modal-actions">
<button class="kiosk-button secondary" type="button" id="kioskClearSignature">
<?= esc(lang('Kiosk.clearSignature')) ?>
</button>
<button class="kiosk-button" type="submit">
<?= esc(lang('Kiosk.signPlan')) ?>
</button>
</div>
</form>
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
], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
const kioskActionBar = document.getElementById('kioskActionBar');
const kioskActionButtons = Array.from(document.querySelectorAll('[data-kiosk-action-btn]'));
const kioskActionTargets = {
    checkin: 'kioskResultsList',
    details: 'kioskPatientDetails',
    contracts: 'kioskContracts',
};
const setActionButtonState = (button, active, enabled) => {if(!button)return;button.disabled=!enabled;button.classList.toggle('is-current',active&&enabled);button.setAttribute('aria-pressed',String(active&&enabled));};
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
        row.className = 'kiosk-record';

        const info = document.createElement('div');
        info.className = 'kiosk-record-copy';

        const name = document.createElement('p');
        name.className = 'kiosk-record-name';
        name.setAttribute('dir', 'auto');
        name.style.unicodeBidi = 'plaintext';
        name.textContent = appointment.patient_display || '';

        const meta = document.createElement('p');
        meta.className = 'kiosk-muted';
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
            confirmBtn.className = 'kiosk-button';

            const icon = document.createElement('i');
            icon.className = 'kiosk-icon-check';
            confirmBtn.appendChild(icon);
            confirmBtn.appendChild(document.createTextNode(kioskI18n.confirmToday));

            confirmForm.appendChild(confirmCsrf);
            confirmForm.appendChild(appointmentId);
            confirmForm.appendChild(confirmBtn);
            row.appendChild(confirmForm);
        } else {
            const note = document.createElement('span');
            note.className = 'kiosk-muted';
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
                    empty.className = 'kiosk-note';
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
                        card.className = 'kiosk-record';

                        const info = createElement('div', 'kiosk-record-copy');
                        const title = createElement('p', 'kiosk-record-name', `${kioskI18n.planPrefix}${contract.id}`);
                        const totalValue = Number(contract.total_cost || 0).toFixed(2);
                        const total = createElement('p', 'kiosk-muted', `${kioskI18n.total}: ${contract.currency} ${totalValue}`);
                        info.appendChild(title);
                        info.appendChild(total);

                        const button = createElement('button', 'kiosk-sign-btn kiosk-button');
                        button.type = 'button';
                        const icon = createElement('i', 'kiosk-icon-sign');
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

                    } else {

                        const hasDue = !!summary.has_due;
                        balanceCard.classList.toggle('has-due',hasDue);
                        if (hasDue) {




                            balanceAmount.textContent = summary.outstanding_formatted || '';
                            balanceHelp.textContent = kioskI18n.balancePayAtReception;
                        } else {




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
<script src="<?= base_url('assets/js/studio-kiosk-access.js') ?>" defer>
</script>
<?= $this->endSection() ?>