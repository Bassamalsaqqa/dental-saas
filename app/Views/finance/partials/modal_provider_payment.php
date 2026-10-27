<div id="<?= esc($providerPayoutModalId) ?>" class="fr-modal hidden" data-finance-modal-root="<?= esc($providerPayoutModalId) ?>" role="dialog" aria-modal="true" aria-labelledby="<?= esc($providerPayoutModalId) ?>Title">
            <div class="finance-inline-modal-backdrop" data-finance-close="<?= esc($providerPayoutModalId) ?>"></div>
            <div  data-finance-close-self="<?= esc($providerPayoutModalId) ?>">
                <div class="finance-inline-modal-panel" data-finance-panel>
                    <div class="finance-inline-modal-header">
                        <div>
                            <span class="finance-inline-modal-kicker">

                                <span><?= esc($providerPayoutUi['kicker']) ?></span>
                            </span>
                            <h4 id="<?= esc($providerPayoutModalId) ?>Title" ><?= esc($providerPayoutUi['action']) ?></h4>
                            <p ><?= esc($providerPayoutUi['notice']) ?></p>
                        </div>
                        <button type="button"  data-finance-close="<?= esc($providerPayoutModalId) ?>">
                            <?= esc(lang('Studio.close')) ?>
                        </button>
                    </div>
                    <form class="finance-inline-modal-form provider-payout-payment-form" data-url="<?= esc(base_url('finance/payouts/' . (int) $providerPayout['id'] . '/payments')) ?>" data-finance-modal-close="<?= esc($providerPayoutModalId) ?>">
                        <?= csrf_field() ?>
                        <div >
                            <div >
                                <div >
                                    <p ><?= esc($providerPayoutUi['kicker']) ?></p>
                                    <h5 ><?= esc($providerDisplayName) ?></h5>
                                    <p >
                                        <?= esc(lang('Finance.financeTransaction')) ?>:
                                        <span ><?= esc($finance['transaction_id'] ?? ('#' . ($finance['id'] ?? ''))) ?></span>
                                    </p>
                                </div>
                                <div class="fr-field-grid">
                                    <div >
                                        <p ><?= esc($providerPayoutUi['doctorShare']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_share_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div >
                                        <p ><?= esc($providerPayoutUi['paid']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerPayout['provider_paid_amount'] ?? 0, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                    <div >
                                        <p ><?= esc($providerPayoutUi['left']) ?></p>
                                        <p  dir="ltr"><?= esc(\App\Services\ProviderCurrencySummary::native($providerBalanceDue, $providerPayout['currency'] ?? null)) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid finance-inline-modal-grid-3">
                            <div class="fr-field">
                                <label >

                                    <span><?= esc($ui['amountRequired']) ?></span>
                                </label>
                                <input type="number" name="amount" step="0.01" min="0.01" max="<?= esc($providerPayoutAmountValue) ?>" value="<?= esc($providerPayoutAmountValue) ?>" class="finance-show-input" placeholder="0.00" required>
                            </div>
                            <div class="fr-field">
                                <label >

                                    <span><?= esc($ui['methodRequired']) ?></span>
                                </label>
                                <div >
                                    <select name="payment_method" class="provider-payout-method-input finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paymentMethod')) ?>">
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
                                <label >

                                    <span><?= lang('Finance.dateRequired') ?></span>
                                </label>
                                <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="finance-show-input">
                            </div>
                        </div>
                        <div class="finance-inline-modal-grid">
                            <div class="provider-payout-liquidity-account-field hidden fr-field">
                                <label >

                                    <span><?= lang('Finance.paidFromAccount') ?></span>
                                </label>
                                <div >
                                    <select name="liquidity_account" class="finance-show-select finance-modal-select" data-searchable-select data-minimum-input-length="0" data-allow-clear="false" data-placeholder="<?= esc(lang('Finance.paidFromAccount')) ?>">
                                        <option value="bank"><?= esc(($check_outgoing_liquidity_options ?? [])['bank'] ?? 'Bank') ?></option>
                                        <option value="undeposited_checks"><?= esc(($check_outgoing_liquidity_options ?? [])['undeposited_checks'] ?? 'Checks on Hand') ?></option>
                                    </select>
                                    <div >

                                    </div>
                                </div>
                                <p ><?= lang('Finance.checkOutgoingAccountHelp') ?></p>
                            </div>
                        </div>
                        <div class="fr-field">
                            <label >

                                <span><?= esc($providerPayoutUi['historyNotes']) ?></span>
                            </label>
                            <textarea name="notes" rows="2" class="finance-show-input" placeholder="<?= esc($ui['optionalNote']) ?>"></textarea>
                        </div>
                        <div class="finance-inline-modal-actions">
                            <button type="button" data-finance-close="<?= esc($providerPayoutModalId) ?>" >
                                <span><?= esc(lang('Finance.cancel')) ?></span>
                            </button>
                            <button type="submit" class="button secondary">
                                <?= esc($providerPayoutUi['save']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
