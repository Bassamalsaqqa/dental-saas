                <div id="kiosk-tab" class="settings-tab hidden">
                    <?php $kioskFeatureEnabled = !empty($kiosk_feature_enabled); ?>
                    <?php $kioskLink = !empty($clinic_uuid) ? base_url('kiosk/' . $clinic_uuid . '/check-in') : ''; ?>
                    <?php $kioskEnabled = !empty($settings['kiosk_enabled']); ?>
                    <?php $kioskReady = $kioskFeatureEnabled && $kioskEnabled && !empty($kiosk_pin_set); ?>
                    <?php $kioskLanguage = lang('TenantMisc.arabicDefault'); ?>

                    <div class="v3-surface border v3-border v3-radius overflow-hidden">
                        <div class="border-b v3-border v3-surface px-8 py-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="w-11 h-11 v3-radius v3-soft-surface v3-accent-text flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tablet-alt text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold v3-text-ink"><?= lang('UI.settingsKiosk') ?></h3>
                                        <p class="mt-1 text-sm v3-text-muted max-w-2xl"><?= lang('UI.settingsKioskLaunchShareHelp') ?></p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $kioskFeatureEnabled ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                                        <?= $kioskFeatureEnabled ? lang('UI.settingsFeatureAvailable') : lang('UI.settingsKioskDisabledByPlanShort') ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $kioskEnabled ? 'v3-bg-scheduled v3-text-primary' : 'v3-bg-soft v3-text-muted' ?>">
                                        <?= $kioskEnabled ? lang('UI.settingsKioskEnabledState') : lang('UI.settingsKioskDisabledState') ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($kiosk_pin_set ?? false) ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                                        <?= ($kiosk_pin_set ?? false) ? lang('UI.settingsPinSet') : lang('UI.settingsPinNotSet') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 space-y-6">
                            <?php if (!$kioskFeatureEnabled): ?>
                                <div class="v3-radius border v3-border-default v3-bg-waiting px-5 py-4 text-sm v3-text-warning">
                                    <?= lang('UI.settingsKioskDisabledByPlan') ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= base_url('settings/updateKiosk') ?>" method="POST" class="space-y-6">
                                <?= csrf_field() ?>

                                <section class="v3-radius border v3-border v3-surface p-6 space-y-5 <?= $kioskFeatureEnabled ? '' : 'opacity-60' ?>">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="text-lg font-semibold v3-text-ink"><?= lang('UI.settingsKioskAccessSecurity') ?></h4>
                                            <p class="mt-1 text-sm v3-text-muted"><?= lang('UI.settingsKioskAccessSecurityHelp') ?></p>
                                        </div>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="kiosk_enabled" value="1" class="ui-toggle kiosk-toggle v3-switch" <?= $kioskEnabled ? 'checked' : '' ?> <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.newPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border v3-border v3-radius v3-surface v3-focus-control focus:ring-4 v3-focus-control transition-all duration-200 v3-input" placeholder="<?= lang('TenantMisc.pinDigitsPlaceholder') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.confirmPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin_confirm" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border v3-border v3-radius v3-surface v3-focus-control focus:ring-4 v3-focus-control transition-all duration-200 v3-input" placeholder="<?= lang('TenantMisc.reenterPin') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                    </div>

                                    <div class="v3-radius border border-dashed v3-border v3-surface px-4 py-3 text-xs v3-text-muted">
                                        <?= lang('TenantMisc.kioskPinHelp') ?> <?= lang('TenantMisc.defaultLanguage') ?>: <?= lang('TenantMisc.arabicDefault') ?>.
                                    </div>
                                </section>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-8 py-3 v3-text-on-primary font-semibold v3-radius transition-all duration-200 v3-form-section-header v3-btn v3-btn--primary" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveKioskSettings') ?>
                                    </button>
                                </div>
                            </form>

                            <section class="v3-radius border v3-border v3-surface p-6 space-y-5">
                                <div>
                                    <h4 class="text-lg font-semibold v3-text-ink"><?= lang('UI.settingsKioskLaunchShare') ?></h4>
                                    <p class="mt-1 text-sm v3-text-muted"><?= lang('UI.settingsKioskLaunchShareHelp') ?></p>
                                </div>

                                <div class="v3-radius border v3-border v3-surface px-4 py-3">
                                    <div class="text-xs font-medium v3-text-muted mb-1"><?= lang('UI.settingsKioskLink') ?></div>
                                    <input id="kioskLinkInput" type="text" readonly value="<?= esc($kioskLink) ?>" class="w-full bg-transparent text-sm v3-text-ink border-0 p-0 focus:ring-0 v3-input" />
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button type="button" onclick="copyKioskLink()" class="px-4 py-2.5 v3-surface border v3-border v3-accent-text font-semibold v3-radius v3-hover-primary transition-all v3-btn v3-btn--secondary" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-copy mr-2"></i><?= lang('UI.settingsCopyLink') ?>
                                    </button>
                                    <button type="button" onclick="openKioskLink()" class="px-4 py-2.5 v3-surface border v3-border v3-text-ink font-semibold v3-radius v3-hover-soft transition-all v3-btn v3-btn--secondary" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas mr-2"></i><?= lang('UI.settingsOpenLink') ?>
                                    </button>
                                    <button type="button" onclick="downloadKioskQr()" class="px-4 py-2.5 v3-surface border v3-border-default v3-text-primary font-semibold v3-radius v3-hover-primary transition-all v3-btn v3-btn--secondary" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-download mr-2"></i><?= lang('UI.settingsDownloadQr') ?>
                                    </button>
                                </div>

                                <div class="v3-radius border v3-border v3-surface p-4 flex flex-col items-center justify-center min-h-[260px]">
                                    <div id="kioskQrCode" class="v3-radius v3-surface p-3"></div>
                                    <p id="kioskQrStatus" class="mt-3 text-xs v3-text-muted text-center">
                                        <?= $kioskReady ? lang('UI.settingsKioskQrReady') : lang('UI.settingsKioskQrUnavailable') ?>
                                    </p>
                                </div>

                                <div class="v3-radius border border-dashed v3-border v3-surface px-4 py-3 text-xs v3-text-muted">
                                    <?= lang('UI.settingsKioskQrHelp') ?>
                                </div>
                            </section>

                            <section class="v3-radius border <?= $kioskReady ? 'v3-border-default v3-bg-paid' : 'v3-border-default v3-bg-waiting' ?> p-5">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 text-lg <?= $kioskReady ? 'v3-text-primary' : 'v3-text-warning' ?>">
                                        <i class="fas <?= $kioskReady ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold <?= $kioskReady ? 'v3-text-primary' : 'v3-text-warning' ?>">
                                            <?= $kioskReady ? lang('UI.settingsKioskReadyTitle') : lang('UI.settingsKioskNeedsAttentionTitle') ?>
                                        </h5>
                                        <p class="mt-1 text-sm <?= $kioskReady ? 'v3-text-primary' : 'v3-text-warning' ?>">
                                            <?= $kioskReady ? lang('UI.settingsKioskReadyHelp') : lang('UI.settingsKioskNeedsAttentionHelp') ?>
                                        </p>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-lg v3-surface px-3 py-2 v3-text-ink"><span class="font-semibold"><?= lang('UI.settingsEnableKioskCheckin') ?>:</span> <?= $kioskEnabled ? lang('UI.settingsKioskEnabledState') : lang('UI.settingsKioskDisabledState') ?></div>
                                            <div class="rounded-lg v3-surface px-3 py-2 v3-text-ink"><span class="font-semibold"><?= lang('TenantMisc.defaultLanguage') ?>:</span> <?= esc($kioskLanguage) ?></div>
                                            <div class="rounded-lg v3-surface px-3 py-2 v3-text-ink"><span class="font-semibold"><?= lang('UI.settingsKioskPinLabel') ?>:</span> <?= ($kiosk_pin_set ?? false) ? lang('UI.settingsPinSet') : lang('UI.settingsPinNotSet') ?></div>
                                            <div class="rounded-lg v3-surface px-3 py-2 v3-text-ink"><span class="font-semibold"><?= lang('UI.settingsKioskLink') ?>:</span> <?= $kioskFeatureEnabled ? lang('UI.settingsAvailable') : lang('UI.settingsUnavailable') ?></div>
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
                                qrContainer.replaceChildren();

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
