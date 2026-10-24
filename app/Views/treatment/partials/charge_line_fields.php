<?php
$validation = $validation ?? null;
$chargeLine = $charge_line ?? null;
$idPrefix = preg_replace('/[^a-zA-Z0-9_\\-]/', '', (string) ($id_prefix ?? 'treatment_charge'));
$inputClass = $input_class ?? 'w-full v3-radius border-2 v3-border-default v3-bg-panel px-4 py-3 text-sm font-medium v3-text-ink v3-elevation-none transition v3-focus-control focus:outline-none focus:ring-4 v3-focus-control';
$chargeEnabled = old('charge_enabled') || (!empty($chargeLine) && ($chargeLine['status'] ?? '') !== 'voided');
$providerType = old('charge_provider_type', $chargeLine['provider_type'] ?? 'none');
$providerUserId = old('charge_provider_user_id', $chargeLine['provider_user_id'] ?? '');
$baseCurrency = settings()->get('currency', 'ILS') ?: 'ILS';
$currency = old('charge_currency', $chargeLine['currency'] ?? $baseCurrency);
$currencyOptions = ['ILS', 'USD', 'JOD', 'EUR', 'GBP'];
?>
<div class="mt-5 v3-radius border v3-border-default v3-bg-scheduled p-4" data-treatment-charge-section>
    <div class="space-y-4">
        <label class="app-toggle-row">
            <span class="app-toggle-row__text">
                <span class="block font-bold v3-text-ink"><?= esc(lang('Treatment.capture_patient_charge')) ?></span>
                <span class="block text-xs font-medium v3-text-muted"><?= esc(lang('Treatment.capture_patient_charge_hint')) ?></span>
            </span>
            <span class="app-toggle">
                <input type="hidden" name="charge_enabled" value="0">
                <input type="checkbox" name="charge_enabled" value="1" data-charge-toggle <?= $chargeEnabled ? 'checked' : '' ?> class="v3-checkbox">
                <span class="app-toggle__track" aria-hidden="true">
                    <span class="app-toggle__dot"></span>
                </span>
            </span>
        </label>

        <div data-charge-body class="<?= $chargeEnabled ? '' : 'hidden' ?> space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="<?= esc($idPrefix) ?>_procedure_name" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_procedure_name')) ?></label>
                    <input id="<?= esc($idPrefix) ?>_procedure_name" type="text" name="charge_procedure_name" value="<?= esc(old('charge_procedure_name', $chargeLine['procedure_name'] ?? '')) ?>" class="<?= esc($inputClass) ?> v3-input" placeholder="<?= esc(lang('Treatment.charge_procedure_name_placeholder')) ?>">
                    <?php if ($validation && $validation->hasError('charge_procedure_name')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_procedure_name')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_status" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_status')) ?></label>
                    <select id="<?= esc($idPrefix) ?>_status" name="charge_status" class="<?= esc($inputClass) ?> v3-select" data-force-select2 data-minimum-results-for-search="Infinity">
                        <?php foreach (['planned', 'accepted', 'completed'] as $status): ?>
                            <option value="<?= esc($status) ?>" <?= old('charge_status', $chargeLine['status'] ?? 'planned') === $status ? 'selected' : '' ?>><?= esc(lang('Treatment.charge_status_' . $status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation && $validation->hasError('charge_status')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_status')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_quantity" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_quantity')) ?></label>
                    <input id="<?= esc($idPrefix) ?>_quantity" type="number" step="0.01" min="0.01" name="charge_quantity" value="<?= esc(old('charge_quantity', $chargeLine['quantity'] ?? '1.00')) ?>" class="<?= esc($inputClass) ?> v3-input">
                    <?php if ($validation && $validation->hasError('charge_quantity')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_quantity')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_unit_price" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_unit_price')) ?></label>
                    <input id="<?= esc($idPrefix) ?>_unit_price" type="number" step="0.01" min="0" name="charge_unit_price" value="<?= esc(old('charge_unit_price', $chargeLine['unit_price'] ?? '0.00')) ?>" class="<?= esc($inputClass) ?> v3-input">
                    <?php if ($validation && $validation->hasError('charge_unit_price')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_unit_price')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_discount" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_discount_amount')) ?></label>
                    <input id="<?= esc($idPrefix) ?>_discount" type="number" step="0.01" min="0" name="charge_discount_amount" value="<?= esc(old('charge_discount_amount', $chargeLine['discount_amount'] ?? '0.00')) ?>" class="<?= esc($inputClass) ?> v3-input">
                    <?php if ($validation && $validation->hasError('charge_discount_amount')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_discount_amount')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_tax" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_tax_amount')) ?></label>
                    <input id="<?= esc($idPrefix) ?>_tax" type="number" step="0.01" min="0" name="charge_tax_amount" value="<?= esc(old('charge_tax_amount', $chargeLine['tax_amount'] ?? '0.00')) ?>" class="<?= esc($inputClass) ?> v3-input">
                    <?php if ($validation && $validation->hasError('charge_tax_amount')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_tax_amount')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_currency" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_currency')) ?></label>
                    <select id="<?= esc($idPrefix) ?>_currency" name="charge_currency" class="<?= esc($inputClass) ?> v3-select" data-force-select2 data-minimum-results-for-search="Infinity">
                        <?php foreach ($currencyOptions as $option): ?>
                            <option value="<?= esc($option) ?>" <?= $currency === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation && $validation->hasError('charge_currency')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_currency')) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="<?= esc($idPrefix) ?>_provider_type" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_provider_type')) ?></label>
                    <select id="<?= esc($idPrefix) ?>_provider_type" name="charge_provider_type" class="<?= esc($inputClass) ?> v3-select" data-charge-provider-type data-force-select2 data-minimum-results-for-search="Infinity">
                        <option value="none" <?= $providerType === 'none' ? 'selected' : '' ?>><?= esc(lang('Treatment.provider_none')) ?></option>
                        <option value="user" <?= $providerType === 'user' ? 'selected' : '' ?>><?= esc(lang('Treatment.provider_clinic_user')) ?></option>
                        <option value="guest" <?= $providerType === 'guest' ? 'selected' : '' ?>><?= esc(lang('Treatment.provider_outside')) ?></option>
                    </select>
                    <?php if ($validation && $validation->hasError('charge_provider_type')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_provider_type')) ?></p><?php endif; ?>
                </div>
            </div>

            <div data-provider-user-wrap class="<?= $providerType === 'user' ? '' : 'hidden' ?>">
                <label for="<?= esc($idPrefix) ?>_provider_user" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_provider_user')) ?></label>
                <select id="<?= esc($idPrefix) ?>_provider_user" name="charge_provider_user_id" class="<?= esc($inputClass) ?> v3-select" data-force-select2 data-allow-clear="true">
                    <option value=""><?= esc(lang('Treatment.select_provider')) ?></option>
                    <?php foreach (($clinic_providers ?? []) as $provider): ?>
                        <?php $name = trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '')); ?>
                        <option value="<?= (int) $provider['id'] ?>" <?= (string) $providerUserId === (string) $provider['id'] ? 'selected' : '' ?>><?= esc($name ?: ($provider['email'] ?? 'Provider')) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($validation && $validation->hasError('charge_provider_user_id')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_provider_user_id')) ?></p><?php endif; ?>
            </div>

            <div data-provider-guest-wrap class="<?= $providerType === 'guest' ? '' : 'hidden' ?>">
                <label for="<?= esc($idPrefix) ?>_provider_guest" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_provider_name')) ?></label>
                <input id="<?= esc($idPrefix) ?>_provider_guest" type="text" name="charge_provider_name_snapshot" value="<?= esc(old('charge_provider_name_snapshot', $chargeLine['provider_name_snapshot'] ?? '')) ?>" class="<?= esc($inputClass) ?> v3-input" placeholder="<?= esc(lang('Treatment.charge_provider_name_placeholder')) ?>">
                <?php if ($validation && $validation->hasError('charge_provider_name_snapshot')): ?><p class="mt-2 text-sm v3-text-danger"><?= esc($validation->getError('charge_provider_name_snapshot')) ?></p><?php endif; ?>
            </div>

            <div>
                <label for="<?= esc($idPrefix) ?>_notes" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.charge_notes')) ?></label>
                <textarea id="<?= esc($idPrefix) ?>_notes" name="charge_notes" rows="2" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc(lang('Treatment.charge_notes_placeholder')) ?>"><?= esc(old('charge_notes', $chargeLine['notes'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>
</div>
