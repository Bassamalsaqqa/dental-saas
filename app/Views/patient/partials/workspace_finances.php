<div id="finances-content" class="tab-content hidden" role="tabpanel" aria-labelledby="finances-tab" hidden>
                            <section class="patient-finance-workspace"><div ><div ></div><div ></div></div><div class="patient-form-stack">
                                <div >
                                    <?php if (has_permission('finance', 'create')): ?>
                                        <button type="button" data-app-drawer-open="patientPaymentDrawer" >
                                            <?= esc(lang('UI.recordPayment')) ?>
                                        </button>
                                        <button type="button" data-app-drawer-open="patientChargeDrawer" >
                                            <?= esc(lang('UI.patientCreateInvoice')) ?>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <dl class="patient-statement <?= $outstandingBalance>0.009?'patient-statement--due':'' ?>"><?php foreach([[lang('UI.patientOutstandingBalance'),$outstandingBalance>0.009?formatCurrency($outstandingBalance):lang('UI.patientNoBalanceDue')],[lang('UI.patientTotalBilled'),formatCurrency($totalCharges)],[lang('UI.patientTotalPaid'),formatCurrency($totalCollected)],[lang('Finance.refunds'),formatCurrency($totalRefunded)]] as [$label,$value]): ?><div><dt><?= esc($label) ?></dt><dd><?= esc($value) ?></dd></div><?php endforeach; ?></dl>
<?php if (!empty($financialLedgerRows)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="finances" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                        <th ><?= esc(lang('UI.patientType')) ?></th>
                                                        <th ><?= esc(lang('Finance.detailsReference')) ?></th>
                                                        <th ><?= esc(lang('UI.patientClinicalSource')) ?></th>
                                                        <th ><?= esc(lang('Finance.amount')) ?></th>
                                                        <th ><?= esc(lang('Finance.method')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($financialLedgerRows as $ledgerRow): ?>
                                                        <?php
                                                        $statusColors = [
                                                            'paid' => 'bg-green-100 text-green-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'partial' => 'bg-blue-100 text-blue-800',
                                                            'overdue' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                                            'recorded' => 'bg-slate-100 text-slate-700',
                                                        ];
                                                        $rowAmount = (float) ($ledgerRow['amount'] ?? 0);
                                                        $amountClass = $rowAmount < 0 ? 'text-rose-700' : ($rowAmount > 0 ? 'text-emerald-700' : 'text-slate-700');
                                                        $colorClass = $statusColors[$ledgerRow['status_key']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <tr class="patient-record-row" data-href="<?= esc($ledgerRow['action_url']) ?>" data-date="<?= esc($ledgerRow['document_date_iso']) ?>">
                                                            <td >
                                                                <?= !empty($ledgerRow['document_date']) ? formatDate($ledgerRow['document_date']) : esc(lang('UI.patientNa')) ?>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($ledgerRow['record_label']) ?></div>
                                                                    <div class="patient-record-subtitle"><?= esc($ledgerRow['record_note'] !== '' ? $ledgerRow['record_note'] : lang('UI.patientNa')) ?></div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($ledgerRow['reference']) ?></div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($ledgerRow['clinical_sources'])): ?>
                                                                    <div >
                                                                        <?php foreach ($ledgerRow['clinical_sources'] as $clinicalSource): ?>
                                                                            <?php $clinicalSourceLabel = trim((string) ($clinicalSource['label'] ?? '')); ?>
                                                                            <?php if ($clinicalSourceLabel === '') { continue; } ?>
                                                                            <?php if (!empty($clinicalSource['url'])): ?>
                                                                                <a href="<?= esc($clinicalSource['url']) ?>" >

                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span >

                                                                                    <span><?= esc($clinicalSourceLabel) ?></span>
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span ><?= esc(lang('UI.patientNoClinicalSource')) ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($ledgerRow['provider_payouts'])): ?>
                                                                    <div >
                                                                        <?php foreach ($ledgerRow['provider_payouts'] as $providerPayout): ?>
                                                                            <a href="<?= esc($providerPayout['url']) ?>" >

                                                                                <span><?= esc(lang('UI.patientProviderCompensation')) ?>: <?= esc($providerPayout['provider_name']) ?></span>
                                                                                <span dir="ltr"><?= esc($providerPayout['share_text']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td >
                                                                <?= ($rowAmount < 0 ? '- ' : '') . formatCurrency(abs($rowAmount)) ?>
                                                            </td>
                                                            <td><?= esc($ledgerRow['method_label']) ?></td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($ledgerRow['status_label']) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <div class="patient-table-actions">
                                                                    <a href="<?= esc($ledgerRow['action_url']) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <div class="patient-empty">

                                    <p ><?= esc(lang('UI.patientNoFinancialRecords')) ?></p>
                                </div>
                                <?php endif; ?>
                                <details class="patient-finance-trace">
                                    <summary >
                                        <span ><?= esc(lang('UI.moneyTrailTitle')) ?></span>
                                        <span >
                                            <?= esc(lang('UI.moneyTrailLinkedRecords')) ?>

                                        </span>
                                    </summary>
                                    <div >
                                        <?= view('components/money_trail_panel', [
                                            'money_trail' => $money_trail ?? [],
                                            'is_rtl' => $isRtl,
                                            'return_to' => $patientContextReturnTo,
                                        ]) ?>
                                    </div>
                                </details>
                            </div></section>
                        </div>
