<?php
$chargeLines = $clinical_charge_lines ?? [];
$selectedIds = (array) old('clinical_charge_line_ids', $selected_clinical_charge_line_ids ?? []);
$selectedIds = array_map('strval', $selectedIds);
$isRtl = !empty($is_rtl);
?>
<?php if (!empty($chargeLines)): ?>
    <div class="context-charge-picker" data-clinical-charge-picker>
        <div >
            <div>
                <p ><?= esc(lang('Finance.billClinicalWork')) ?></p>
            </div>
            <span  data-clinical-charge-total>0.00</span>
        </div>
        <div class="context-field-stack">
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
                <label >
                    <input type="checkbox"
                           name="clinical_charge_line_ids[]"
                           value="<?= $lineId ?>"

                           data-clinical-charge-line
                           data-amount="<?= esc(number_format($amount, 2, '.', '')) ?>"
                           data-currency="<?= esc($currency) ?>"
                           data-label="<?= esc($label, 'attr') ?>"
                           <?= in_array((string) $lineId, $selectedIds, true) ? 'checked' : '' ?>>
                    <span >
                        <span ><?= esc($label) ?></span>
                        <?php if ($meta): ?>
                            <span ><?= esc(implode(' · ', $meta)) ?></span>
                        <?php endif; ?>
                    </span>
                    <span  dir="ltr"><?= esc($currency) ?> <?= number_format($amount, 2) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <p ><?= esc(lang('Finance.billClinicalWorkServerOwned')) ?></p>
    </div>
<?php endif; ?>
