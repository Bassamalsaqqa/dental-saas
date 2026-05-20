<?php
$chargeLines = $clinical_charge_lines ?? [];
$selectedIds = array_map('strval', (array) old('clinical_charge_line_ids', []));
$isRtl = !empty($is_rtl);
?>
<?php if (!empty($chargeLines)): ?>
    <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50/70 p-4" data-clinical-charge-picker>
        <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold text-slate-900"><?= esc(lang('Finance.billClinicalWork')) ?></p>
                <p class="text-xs font-medium text-slate-500"><?= esc(lang('Finance.billClinicalWorkHelp')) ?></p>
            </div>
            <span class="text-xs font-bold text-sky-700" data-clinical-charge-total>0.00</span>
        </div>
        <div class="space-y-2">
            <?php foreach ($chargeLines as $line): ?>
                <?php
                $lineId = (int) ($line['id'] ?? 0);
                $amount = (float) ($line['net_production_amount'] ?? 0);
                $currency = (string) ($line['currency'] ?? 'ILS');
                $label = (string) ($line['procedure_name'] ?? lang('Finance.clinicalChargeLine'));
                $meta = array_filter([
                    !empty($line['treatment_number']) ? $line['treatment_number'] : null,
                    !empty($line['tooth_numbers']) ? (lang('Treatment.tooth_number_short') . ': ' . $line['tooth_numbers']) : null,
                    !empty($line['service_date']) ? formatDate($line['service_date']) : null,
                ]);
                ?>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-sky-200 hover:bg-sky-50">
                    <input type="checkbox"
                           name="clinical_charge_line_ids[]"
                           value="<?= $lineId ?>"
                           class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                           data-clinical-charge-line
                           data-amount="<?= esc(number_format($amount, 2, '.', '')) ?>"
                           data-currency="<?= esc($currency) ?>"
                           data-label="<?= esc($label, 'attr') ?>"
                           <?= in_array((string) $lineId, $selectedIds, true) ? 'checked' : '' ?>>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-slate-900"><?= esc($label) ?></span>
                        <?php if ($meta): ?>
                            <span class="mt-0.5 block text-xs text-slate-500"><?= esc(implode(' · ', $meta)) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="whitespace-nowrap text-sm font-black text-sky-700" dir="ltr"><?= esc($currency) ?> <?= number_format($amount, 2) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <p class="mt-3 text-xs font-medium text-slate-500"><?= esc(lang('Finance.billClinicalWorkServerOwned')) ?></p>
    </div>
<?php endif; ?>
