<div id="paymentModal" class="fr-modal hidden" data-finance-modal-root="paymentModal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
        <div class="finance-inline-modal-backdrop" data-finance-close="paymentModal"></div>
        <div  data-finance-close-self="paymentModal">
            <div class="finance-inline-modal-panel" data-finance-panel>
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker">

                            <span><?= esc($ui['paymentKicker']) ?></span>
                        </span>
                        <h4 id="paymentModalTitle" ><?= esc($ui['recordPayment']) ?></h4>
                        <p ><?= esc($ui['paymentNotice']) ?> <span ><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button"  data-finance-close="paymentModal">
                        <?= esc(lang('Studio.close')) ?>
                    </button>
                </div>
                <form id="invoicePaymentForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div >
                                <input type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div >
                                <select name="currency" data-settlement-currency class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
                                    <?php foreach (($currency_options ?? [$baseCurrency]) as $currencyOption): ?>
                                        <option value="<?= esc($currencyOption) ?>" <?= $currencyOption === $baseCurrency ? 'selected' : '' ?>><?= esc($currencyOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div >
                                <input type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div >
                                <input type="text" data-settlement-base readonly
                                       class="finance-show-input"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label >

                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div >
                                <select name="payment_method" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
                                    <option value="cash"><?= lang('Finance.cash') ?></option>
                                    <option value="card"><?= lang('Finance.card') ?></option>
                                    <option value="bank_transfer"><?= lang('Finance.bankTransferShort') ?></option>
                                    <option value="check"><?= lang('Finance.check') ?></option>
                                    <option value="other"><?= lang('Finance.other') ?></option>
                                </select>
                                <div >

                                </div>
                            </div>
                        </div>
                        <?php if ($isExpense): ?>
                        <div class="hidden fr-field" id="settlementLiquidityAccountField">
                            <label >

                                <span><?= lang('Finance.paidFromAccount') ?></span>
                            </label>
                            <div >
                                <select id="settlementLiquidityAccount" name="liquidity_account" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?>
                                        <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div >

                                </div>
                            </div>
                            <p ><?= lang('Finance.paidFromAccountHelp') ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="fr-field">
                            <label >

                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div >
                                <input type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="fr-field">
                        <label >

                            <span><?= lang('Finance.notes') ?></span>
                        </label>
                        <div >
                            <textarea name="notes" rows="2"

                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" data-finance-close="paymentModal" >
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="button secondary">
                            <?= esc($ui['savePayment']) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
