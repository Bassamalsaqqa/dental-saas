<dialog id="refundModal" class="fr-modal hidden" data-finance-modal-root="refundModal" role="dialog" aria-modal="true" aria-labelledby="refundModalTitle" aria-describedby="refundModalDescription">
        <div class="finance-inline-modal-backdrop" data-finance-close="refundModal"></div>
        <div  data-finance-close-self="refundModal">
            <div class="finance-inline-modal-panel" data-finance-panel>
                <div class="finance-inline-modal-header">
                    <div>
                        <span class="finance-inline-modal-kicker finance-inline-modal-kicker-danger">

                            <span><?= esc($ui['refundKicker']) ?></span>
                        </span>
                        <h4 id="refundModalTitle" ><?= esc($ui['recordRefund']) ?></h4>
                        <p id="refundModalDescription"><?= esc($ui['refundNotice']) ?> <span ><?= esc($baseCurrency) ?></span>.</p>
                    </div>
                    <button type="button"  data-finance-close="refundModal">
                        <?= esc(lang('Studio.close')) ?>
                    </button>
                </div>
                <form id="invoiceRefundForm" class="finance-inline-modal-form">
                    <?= csrf_field() ?>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label for="refundModalField1" >

                                <span><?= esc($ui['amountRequired']) ?></span>
                            </label>
                            <div >
                                <input id="refundModalField1" type="number" name="<?= $supportsPaymentExchangeFields ? 'amount_original' : 'amount' ?>" step="0.01" min="0.01"
                                       class="finance-show-input"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label for="refundModalField2" >

                                <span><?= lang('Finance.currency') ?></span>
                            </label>
                            <div >
                                <select id="refundModalField2" name="currency" data-settlement-currency class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.currency')) ?>">
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
                            <label for="refundModalField3" >

                                <span><?= lang('Finance.exchangeRate') ?></span>
                            </label>
                            <div >
                                <input id="refundModalField3" type="number" name="exchange_rate" data-settlement-rate step="0.0001" min="0.0001" value="1"
                                       class="finance-show-input">
                            </div>
                        </div>
                        <div class="fr-field">
                            <label for="refundModalBaseAmount">

                                <span><?= lang('Finance.amountBase') ?> (<?= esc($baseCurrency) ?>)</span>
                            </label>
                            <div >
                                <input id="refundModalBaseAmount" type="text" data-settlement-base readonly
                                       class="finance-show-input"
                                       value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="finance-inline-modal-grid">
                        <div class="fr-field">
                            <label for="refundModalField4" >

                                <span><?= esc($ui['methodRequired']) ?></span>
                            </label>
                            <div >
                                <select id="refundModalField4" name="payment_method" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
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
                        <div class="fr-field">
                            <label for="refundModalField5" >

                                <span><?= lang('Finance.dateRequired') ?></span>
                            </label>
                            <div >
                                <input id="refundModalField5" type="text" name="payment_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>"
                                       class="finance-show-input">
                            </div>
                        </div>
                    </div>
                    <div class="fr-field">
                        <label for="refundModalField6" >

                            <span><?= esc($ui['refundNote']) ?></span>
                        </label>
                        <div >
                            <textarea id="refundModalField6" name="notes" rows="2"

                                      placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                    </div>
                    <div class="finance-inline-modal-actions">
                        <button type="button" data-finance-close="refundModal" >
                            <span><?= esc(lang('Finance.cancel')) ?></span>
                        </button>
                        <button type="submit" class="button secondary">
                            <?= esc($ui['saveRefund']) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
