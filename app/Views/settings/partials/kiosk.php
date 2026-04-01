                <div id="kiosk-tab" class="settings-tab hidden">
                    <?php $kioskFeatureEnabled = !empty($kiosk_feature_enabled); ?>
                    <?php $kioskLink = !empty($clinic_uuid) ? base_url('kiosk/' . $clinic_uuid . '/check-in') : ''; ?>
                    <?php $kioskEnabled = !empty($settings['kiosk_enabled']); ?>
                    <?php $kioskReady = $kioskFeatureEnabled && $kioskEnabled && !empty($kiosk_pin_set); ?>
                    <?php $kioskLanguage = lang('TenantMisc.arabicDefault'); ?>

                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-8 py-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tablet-alt text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900"><?= lang('UI.settingsKiosk') ?></h3>
                                        <p class="mt-1 text-sm text-slate-500 max-w-2xl"><?= lang('UI.settingsKioskLaunchShareHelp') ?></p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $kioskFeatureEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= $kioskFeatureEnabled ? lang('UI.settingsFeatureAvailable') : lang('UI.settingsKioskDisabledByPlanShort') ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $kioskEnabled ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= $kioskEnabled ? lang('UI.settingsKioskEnabledState') : lang('UI.settingsKioskDisabledState') ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($kiosk_pin_set ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= ($kiosk_pin_set ?? false) ? lang('UI.settingsPinSet') : lang('UI.settingsPinNotSet') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 space-y-6">
                            <?php if (!$kioskFeatureEnabled): ?>
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                                    <?= lang('UI.settingsKioskDisabledByPlan') ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= base_url('settings/updateKiosk') ?>" method="POST" class="space-y-6">
                                <?= csrf_field() ?>

                                <section class="rounded-2xl border border-slate-200 bg-slate-50 p-6 space-y-5 <?= $kioskFeatureEnabled ? '' : 'opacity-60' ?>">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="text-lg font-semibold text-slate-900"><?= lang('UI.settingsKioskAccessSecurity') ?></h4>
                                            <p class="mt-1 text-sm text-slate-500"><?= lang('UI.settingsKioskAccessSecurityHelp') ?></p>
                                        </div>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="kiosk_enabled" value="1" class="ui-toggle kiosk-toggle" <?= $kioskEnabled ? 'checked' : '' ?> <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.newPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="<?= lang('TenantMisc.pinDigitsPlaceholder') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.confirmPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin_confirm" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="<?= lang('TenantMisc.reenterPin') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-xs text-slate-500">
                                        <?= lang('TenantMisc.kioskPinHelp') ?> <?= lang('TenantMisc.defaultLanguage') ?>: <?= lang('TenantMisc.arabicDefault') ?>.
                                    </div>
                                </section>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-200 shadow-lg" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveKioskSettings') ?>
                                    </button>
                                </div>
                            </form>

                            <section class="rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900"><?= lang('UI.settingsKioskLaunchShare') ?></h4>
                                    <p class="mt-1 text-sm text-slate-500"><?= lang('UI.settingsKioskLaunchShareHelp') ?></p>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="text-xs font-medium text-slate-500 mb-1"><?= lang('UI.settingsKioskLink') ?></div>
                                    <input id="kioskLinkInput" type="text" readonly value="<?= esc($kioskLink) ?>" class="w-full bg-transparent text-sm text-slate-700 border-0 p-0 focus:ring-0" />
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button type="button" onclick="copyKioskLink()" class="px-4 py-2.5 bg-white border border-blue-200 text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transition-all" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-copy mr-2"></i><?= lang('UI.settingsCopyLink') ?>
                                    </button>
                                    <button type="button" onclick="openKioskLink()" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-arrow-up-right-from-square mr-2"></i><?= lang('UI.settingsOpenLink') ?>
                                    </button>
                                    <button type="button" onclick="downloadKioskQr()" class="px-4 py-2.5 bg-white border border-emerald-200 text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-all" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-download mr-2"></i><?= lang('UI.settingsDownloadQr') ?>
                                    </button>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex flex-col items-center justify-center min-h-[260px]">
                                    <div id="kioskQrCode" class="rounded-xl bg-white p-3 shadow-sm"></div>
                                    <p id="kioskQrStatus" class="mt-3 text-xs text-slate-500 text-center">
                                        <?= $kioskReady ? lang('UI.settingsKioskQrReady') : lang('UI.settingsKioskQrUnavailable') ?>
                                    </p>
                                </div>

                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                                    <?= lang('UI.settingsKioskQrHelp') ?>
                                </div>
                            </section>

                            <section class="rounded-2xl border <?= $kioskReady ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' ?> p-5">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 text-lg <?= $kioskReady ? 'text-emerald-600' : 'text-amber-600' ?>">
                                        <i class="fas <?= $kioskReady ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold <?= $kioskReady ? 'text-emerald-900' : 'text-amber-900' ?>">
                                            <?= $kioskReady ? lang('UI.settingsKioskReadyTitle') : lang('UI.settingsKioskNeedsAttentionTitle') ?>
                                        </h5>
                                        <p class="mt-1 text-sm <?= $kioskReady ? 'text-emerald-800' : 'text-amber-800' ?>">
                                            <?= $kioskReady ? lang('UI.settingsKioskReadyHelp') : lang('UI.settingsKioskNeedsAttentionHelp') ?>
                                        </p>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-lg bg-white/70 px-3 py-2 text-slate-700"><span class="font-semibold"><?= lang('UI.settingsEnableKioskCheckin') ?>:</span> <?= $kioskEnabled ? lang('UI.settingsKioskEnabledState') : lang('UI.settingsKioskDisabledState') ?></div>
                                            <div class="rounded-lg bg-white/70 px-3 py-2 text-slate-700"><span class="font-semibold"><?= lang('TenantMisc.defaultLanguage') ?>:</span> <?= esc($kioskLanguage) ?></div>
                                            <div class="rounded-lg bg-white/70 px-3 py-2 text-slate-700"><span class="font-semibold"><?= lang('UI.settingsKioskPinLabel') ?>:</span> <?= ($kiosk_pin_set ?? false) ? lang('UI.settingsPinSet') : lang('UI.settingsPinNotSet') ?></div>
                                            <div class="rounded-lg bg-white/70 px-3 py-2 text-slate-700"><span class="font-semibold"><?= lang('UI.settingsKioskLink') ?>:</span> <?= $kioskFeatureEnabled ? lang('UI.settingsAvailable') : lang('UI.settingsUnavailable') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                    <script>
                        (function() {
                            const kioskLink = <?= json_encode($kioskLink) ?>;
                            const kioskReady = <?= $kioskReady ? 'true' : 'false' ?>;
                            const qrContainer = document.getElementById('kioskQrCode');
                            const qrStatus = document.getElementById('kioskQrStatus');

                            function renderKioskQr() {
                                if (!qrContainer) return;
                                qrContainer.innerHTML = '';

                                if (!kioskReady || !kioskLink || typeof QRCode === 'undefined') {
                                    if (qrStatus) {
                                        qrStatus.textContent = kioskReady ? <?= json_encode(lang('UI.settingsKioskQrFailed')) ?> : <?= json_encode(lang('UI.settingsKioskQrUnavailable')) ?>;
                                    }
                                    return;
                                }

                                new QRCode(qrContainer, {
                                    text: kioskLink,
                                    width: 172,
                                    height: 172,
                                    colorDark: '#0f172a',
                                    colorLight: '#ffffff',
                                    correctLevel: QRCode.CorrectLevel.M
                                });

                                if (qrStatus) {
                                    qrStatus.textContent = <?= json_encode(lang('UI.settingsKioskQrReady')) ?>;
                                }
                            }

                            window.openKioskLink = function() {
                                const input = document.getElementById('kioskLinkInput');
                                if (!input || !input.value) {
                                    window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastKioskLinkNotAvailable);
                                    return;
                                }
                                window.open(input.value, '_blank', 'noopener');
                            };

                            window.downloadKioskQr = function() {
                                const canvas = qrContainer?.querySelector('canvas');
                                const image = qrContainer?.querySelector('img');
                                let dataUrl = '';

                                if (canvas) {
                                    dataUrl = canvas.toDataURL('image/png');
                                } else if (image) {
                                    dataUrl = image.src;
                                }

                                if (!dataUrl) {
                                    window.showToast?.('error', settingsI18n.toastError, <?= json_encode(lang('UI.settingsKioskQrFailed')) ?>);
                                    return;
                                }

                                const link = document.createElement('a');
                                link.href = dataUrl;
                                link.download = 'kiosk-qr.png';
                                document.body.appendChild(link);
                                link.click();
                                link.remove();
                            };

                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', renderKioskQr);
                            } else {
                                renderKioskQr();
                            }
                        })();
                    </script>
                </div>
