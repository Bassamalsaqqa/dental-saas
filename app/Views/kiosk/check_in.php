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

                        <div id="kioskResults" class="border-t border-gray-100 pt-6 <?= !empty($appointments) || !empty($info_message) ? '' : 'hidden' ?>">
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
                                                <?= esc($appointment['appointment_time'] ?? '') ?> - <?= esc($appointment['appointment_type'] ?? '') ?>
                                            </p>
                                        </div>
                                        <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/confirm') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="appointment_id" value="<?= (int) ($appointment['id'] ?? 0) ?>">
                                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg">
                                                <i class="fas fa-check-circle mr-2"></i>Confirm
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($appointments) && !empty($info_message)): ?>
                                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm">
                                        <?= esc($info_message) ?>
                                    </div>
                                <?php endif; ?>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('kioskPhoneInput');
    const keys = document.querySelectorAll('.kiosk-key');
    const form = document.getElementById('kioskLookupForm');
    const results = document.getElementById('kioskResults');
    const resultsTitle = document.getElementById('kioskResultsTitle');
    const resultsList = document.getElementById('kioskResultsList');
    if (!input || !keys.length) return;

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
                resultsList.textContent = '';

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
                        meta.textContent = `${appointment.appointment_time || ''} - ${appointment.appointment_type || ''}`;

                        info.appendChild(name);
                        info.appendChild(meta);

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
                        confirmBtn.appendChild(document.createTextNode('Confirm'));

                        confirmForm.appendChild(confirmCsrf);
                        confirmForm.appendChild(appointmentId);
                        confirmForm.appendChild(confirmBtn);

                        row.appendChild(info);
                        row.appendChild(confirmForm);
                        resultsList.appendChild(row);
                    });
                } else {
                    const empty = document.createElement('div');
                    empty.className = 'bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm';
                    empty.textContent = data.info_message || 'No scheduled appointments found for today.';
                    resultsList.appendChild(empty);
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
</script>
<?= $this->endSection() ?>
