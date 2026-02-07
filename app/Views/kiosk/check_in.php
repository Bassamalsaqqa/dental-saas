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
                            <h1 class="text-2xl font-bold text-white" dir="auto">
                                <?= esc($settings['kiosk_header'] ?? 'Self Check-In') ?>
                            </h1>
                            <p class="text-blue-100 text-sm" dir="auto">
                                <?= esc($settings['kiosk_subheader'] ?? 'Confirm your appointment in seconds.') ?>
                            </p>
                            <p class="text-blue-200 text-xs mt-1" dir="auto">
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
                        <form action="<?= base_url('kiosk/' . $clinic['uuid'] . '/lookup') ?>" method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number</label>
                                <input type="tel" name="phone" inputmode="tel" class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-lg" placeholder="e.g. 0599 000 000" required>
                            </div>
                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                <i class="fas fa-search mr-2"></i>Find My Appointment
                            </button>
                        </form>

                        <?php if (!empty($appointments)): ?>
                            <div class="border-t border-gray-100 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-slate-900">Appointments for <?= esc($lookup_phone ?? '') ?></h3>
                                    <a href="<?= base_url('kiosk/' . $clinic['uuid'] . '/check-in/reset') ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        Not you? Re-enter phone
                                    </a>
                                </div>
                                <div class="space-y-4">
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
                                </div>
                            </div>
                        <?php elseif (!empty($info_message)): ?>
                            <div class="border-t border-gray-100 pt-6">
                                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-amber-700 text-sm">
                                    <?= esc($info_message) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center text-xs text-slate-500" dir="auto">
                <?= esc($settings['kiosk_footer_note'] ?? 'Need help? Please ask the reception desk.') ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
