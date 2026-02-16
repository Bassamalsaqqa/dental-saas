<?= $this->extend('layouts/kiosk') ?>

<?= $this->section('content') ?>
<div class="min-h-screen">
    <div class="container mx-auto px-6 py-10">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                            <?php
                            $logoPath = $settings['clinic_logo_path'] ?? '';
                            $logoSrc = '';
                            if (!empty($logoPath)) {
                                $logoSrc = (strpos($logoPath, 'http://') === 0 || strpos($logoPath, 'https://') === 0)
                                    ? $logoPath
                                    : base_url(ltrim($logoPath, '/'));
                            }
                            ?>
                            <?php if ($logoSrc): ?>
                                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($settings['clinic_name'] ?? $clinic['name']) ?>" class="w-10 h-10 object-contain">
                            <?php else: ?>
                                <i class="fas fa-tooth text-white text-2xl"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white" dir="auto" style="unicode-bidi: plaintext;">
                                <?= esc($settings['kiosk_header'] ?? 'Self Check-In') ?>
                            </h1>
                            <p class="text-blue-100 text-sm" dir="auto" style="unicode-bidi: plaintext;">
                                <?= esc($settings['kiosk_subheader'] ?? 'Confirm your appointment in seconds.') ?>
                            </p>
                            <p class="text-white text-lg font-bold mt-2" dir="auto" style="unicode-bidi: plaintext;">
                                <?= esc($settings['clinic_name'] ?? $clinic['name']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <?php if (!($pin_verified ?? false)): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6">
                            <h2 class="text-lg font-semibold text-blue-900 mb-2">Enter Clinic PIN</h2>
                            <p class="text-sm text-blue-700">This kiosk is protected. Please enter the clinic PIN to begin.</p>
                        </div>
                        <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/unlock') ?>" method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic PIN</label>
                                <input type="password" name="kiosk_pin" inputmode="numeric" class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-lg" placeholder="Enter PIN" required>
                            </div>
                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                <i class="fas fa-lock-open mr-2"></i>Unlock Kiosk
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="bg-white border border-slate-200 rounded-2xl p-6">
                            <h2 class="text-lg font-semibold text-slate-900 mb-2">Check In</h2>
                            <p class="text-sm text-slate-500">Enter your mobile number to confirm today's appointment.</p>
                        </div>
                        <form id="kioskLookupForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/lookup') ?>" method="POST" class="space-y-4" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number</label>
                                <input id="kioskPhoneInput" type="tel" name="phone" inputmode="numeric" class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-lg tracking-wider" placeholder="e.g. 0599 000 000" required>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <?php foreach (['1','2','3','4','5','6','7','8','9','clear','0','back'] as $key): ?>
                                        <?php if ($key === 'clear'): ?>
                                            <button type="button" data-key="clear" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-all">Clear</button>
                                        <?php elseif ($key === 'back'): ?>
                                            <button type="button" data-key="back" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-all">Back</button>
                                        <?php else: ?>
                                            <button type="button" data-key="<?= $key ?>" class="kiosk-key bg-white border border-slate-200 rounded-xl py-3 text-lg font-bold text-slate-800 hover:bg-slate-100 transition-all"><?= $key ?></button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                <i class="fas fa-search mr-2"></i>Find My Appointment
                            </button>
                        </form>

                        <div id="kioskResults" class="border-t border-gray-100 pt-6 <?= !empty($appointments) || !empty($patients) || !empty($info_message) ? '' : 'hidden' ?>">
                            <div class="flex items-center justify-between mb-4">
                                <h3 id="kioskResultsTitle" class="text-lg font-semibold text-slate-900">Appointments for <?= esc($lookup_phone ?? '') ?></h3>
                                <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                    Not you? Re-enter phone
                                </a>
                            </div>
                            <div id="kioskResultsList" class="space-y-4">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900" dir="auto" style="unicode-bidi: plaintext;"><?= esc($appointment['patient_display'] ?? '') ?></p>
                                            <p class="text-sm text-slate-500">
                                                <?= esc($appointment['appointment_date'] ?? '') ?> · <?= esc($appointment['appointment_time'] ?? '') ?> - <?= esc($appointment['appointment_type'] ?? '') ?>
                                                <?php if (!empty($appointment['status'])): ?>
                                                    · <?= esc(ucfirst($appointment['status'])) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <?php if (!empty($appointment['can_confirm'])): ?>
                                            <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/confirm') ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="appointment_id" value="<?= (int) ($appointment['id'] ?? 0) ?>">
                                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg">
                                                    <i class="fas fa-check-circle mr-2"></i>Confirm Today
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold text-slate-500">Confirmation available on appointment day</span>
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
                                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Update Your Details</h3>
                                    <p class="text-sm text-slate-500 mb-4">Keep your contact details up to date.</p>
                                    <form id="kioskDetailsForm" action="<?= base_url('kiosk/' . $clinic['uuid'] . '/update-details') ?>" method="POST" class="space-y-4" data-csrf-name="<?= csrf_token() ?>" data-csrf-token="<?= csrf_hash() ?>">
                                        <?= csrf_field() ?>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Patient</label>
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
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number</label>
                                                <input type="tel" name="phone" id="kioskPhoneUpdate" inputmode="numeric" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                                <input type="email" name="email" id="kioskEmailUpdate" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                            </div>
                                        </div>
                                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                            <i class="fas fa-save mr-2"></i>Update Details
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="kioskContracts" class="mt-6 <?= !empty($contracts) ? '' : 'hidden' ?>">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Treatment Plan</h3>
                                    <p class="text-sm text-slate-500 mb-4">Review and sign your treatment plan.</p>
                                    <div id="kioskContractList" class="space-y-4">
                                        <?php foreach ($contracts ?? [] as $contract): ?>
                                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-900">Plan #<?= (int) $contract['id'] ?></p>
                                                        <p class="text-sm text-slate-500">Total: <?= esc($contract['currency']) ?> <?= number_format((float) $contract['total_cost'], 2) ?></p>
                                                    </div>
                                                    <button type="button" class="kiosk-sign-btn px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all"
                                                            data-contract='<?= esc(json_encode($contract), 'attr') ?>'>
                                                        <i class="fas fa-pen-fancy mr-2"></i>Review &amp; Sign
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
                <?= esc($settings['kiosk_footer_note'] ?? 'Need help? Please ask the reception desk.') ?>
            </div>
        </div>
    </div>
</div>

<div id="kioskContractModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-6 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Treatment Plan</h3>
                    <p class="text-sm text-blue-100" id="kioskContractMeta"></p>
                </div>
                <button type="button" id="kioskCloseContract" class="w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="p-6 space-y-6 overflow-y-auto">
                <div id="kioskContractSnapshot" class="border border-gray-200 rounded-2xl p-4 bg-gray-50"></div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Plan Items</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-gray-500 uppercase text-xs">
                                    <th class="py-2 px-3 text-left">Tooth</th>
                                    <th class="py-2 px-3 text-left">Description</th>
                                    <th class="py-2 px-3 text-left">Qty</th>
                                    <th class="py-2 px-3 text-left">Price</th>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Typed Name</label>
                            <input type="text" name="signer_name" id="kioskSignerName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required>
                        </div>
                        <div class="flex items-end text-sm text-gray-500">
                            Please sign below to confirm your treatment plan.
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Draw Signature</label>
                        <canvas id="kioskSignatureCanvas" class="border border-gray-300 rounded-xl w-full" height="160" style="touch-action: none; -ms-touch-action: none;"></canvas>
                        <button type="button" id="kioskClearSignature"
                                class="mt-2 inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-eraser mr-1.5 text-gray-500"></i>Clear Signature
                        </button>
                    </div>
                    <button type="submit" class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all">
                        <i class="fas fa-signature mr-2"></i>Sign Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
                    window.showToast('error', 'Error', 'Please enter a mobile number.');
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
                        window.showToast('error', 'Error', data?.message || 'Lookup failed.');
                    }
                    return;
                }

                if (data.csrf_name && data.csrf_token) {
                    form.dataset.csrfName = data.csrf_name;
                    form.dataset.csrfToken = data.csrf_token;
                }

                resultsTitle.textContent = `Appointments for ${data.lookup_phone || ''}`;
                clearNode(resultsList);

                if (data.appointments && data.appointments.length) {
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
                        const dateLabel = appointment.appointment_date ? `${appointment.appointment_date} · ` : '';
                        const statusLabel = appointment.status ? ` · ${appointment.status.charAt(0).toUpperCase()}${appointment.status.slice(1)}` : '';
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
                            confirmBtn.appendChild(document.createTextNode('Confirm Today'));

                            confirmForm.appendChild(confirmCsrf);
                            confirmForm.appendChild(appointmentId);
                            confirmForm.appendChild(confirmBtn);

                            row.appendChild(confirmForm);
                        } else {
                            const note = document.createElement('span');
                            note.className = 'text-xs font-semibold text-slate-500';
                            note.textContent = 'Confirmation available on appointment day';
                            row.appendChild(note);
                        }
                        resultsList.appendChild(row);
                    });
                } else {
                    const empty = document.createElement('div');
                    empty.className = 'bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm';
                    empty.textContent = data.info_message || 'No scheduled appointments found for today.';
                    resultsList.appendChild(empty);
                }

                const patientDetails = document.getElementById('kioskPatientDetails');
                const patientSelect = document.getElementById('kioskPatientSelect');
                const phoneUpdate = document.getElementById('kioskPhoneUpdate');
                const emailUpdate = document.getElementById('kioskEmailUpdate');
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
                if (contractsWrap && contractsList) {
                    clearNode(contractsList);
                    (data.contracts || []).forEach((contract) => {
                        const card = document.createElement('div');
                        card.className = 'bg-slate-50 border border-slate-200 rounded-2xl p-5';
                        const row = createElement('div', 'flex flex-col md:flex-row md:items-center md:justify-between gap-4');
                        const info = createElement('div');
                        const title = createElement('p', 'text-sm font-semibold text-slate-900', `Plan #${contract.id}`);
                        const totalValue = Number(contract.total_cost || 0).toFixed(2);
                        const total = createElement('p', 'text-sm text-slate-500', `Total: ${contract.currency} ${totalValue}`);
                        info.appendChild(title);
                        info.appendChild(total);

                        const button = createElement('button', 'kiosk-sign-btn px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all');
                        button.type = 'button';
                        const icon = createElement('i', 'fas fa-pen-fancy mr-2');
                        button.appendChild(icon);
                        appendText(button, 'Review & Sign');

                        row.appendChild(info);
                        row.appendChild(button);
                        card.appendChild(row);

                        button.addEventListener('click', () => {
                            openContractModal(contract);
                        });
                        contractsList.appendChild(card);
                    });
                    if ((data.contracts || []).length) {
                        contractsWrap.classList.remove('hidden');
                    } else {
                        contractsWrap.classList.add('hidden');
                    }
                }

                results.classList.remove('hidden');
            } catch (error) {
                if (window.showToast) {
                    window.showToast('error', 'Error', 'Lookup failed. Please try again.');
                }
            }
        });
    }
});

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
        const data = await response.json();
        if (data.csrf_name && data.csrf_token) {
            detailsForm.dataset.csrfName = data.csrf_name;
            detailsForm.dataset.csrfToken = data.csrf_token;
        }
        if (window.showToast) {
            if (data.success) {
                window.showToast('success', 'Updated', data.message || 'Details updated.');
            } else {
                window.showToast('error', 'Error', data.message || 'Unable to update details.');
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
    kioskMeta.textContent = `Plan #${contract.id} · ${contract.currency} ${Number(contract.total_cost).toFixed(2)}`;
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
        emptySnapshot.textContent = 'No snapshot available.';
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
        emptyCell.textContent = 'No items listed.';
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
        const data = await response.json();
        if (data.csrf_name && data.csrf_token) {
            kioskForm.dataset.csrfName = data.csrf_name;
            kioskForm.dataset.csrfToken = data.csrf_token;
        }
        if (window.showToast) {
            if (data.success) {
                window.showToast('success', 'Signed', data.message || 'Plan signed.');
            } else {
                window.showToast('error', 'Error', data.message || 'Unable to sign plan.');
            }
        }
        if (data.success) {
            kioskModal?.classList.add('hidden');
        }
    });
}
</script>
<?= $this->endSection() ?>
