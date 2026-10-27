<div id="treatments-content" class="tab-content hidden">
<?= view('patient/partials/history_navigation',['history_page'=>$workspaceHistoryPages['treatments']??null,'history_navigation'=>$historyNavigation['treatments']??null]) ?>
<section class="patient-tab-ambient patient-treatment-workspace"><header class="patient-section-header"><div><h3><?= esc(lang('UI.patientTreatments')) ?></h3><p><?= esc(lang('UI.patientBilling')) ?> / <?= esc(lang('UI.patientStatus')) ?> / <?= esc(lang('Finance.provider')) ?></p></div><?php if(has_permission('treatments','create')): ?><button type="button" data-app-drawer-open="patientTreatmentDrawer"><?= esc(lang('UI.patientCreateTreatmentPlan')) ?></button><?php endif; ?></header>
<dl class="patient-treatment-summary"><div><dt><?= esc(lang('UI.patientUnbilledWork')) ?></dt><dd><?= formatCurrency($openClinicalChargeTotal) ?></dd><small><?= esc($openClinicalChargeCount) ?> <?= esc(lang('UI.patientTreatments')) ?></small></div><div><dt><?= esc(lang('UI.patientBilledPaid')) ?></dt><dd><?= esc($paidBilledTreatmentCount) ?></dd></div><div><dt><?= esc(lang('UI.patientBilledWithBalance')) ?></dt><dd><?= esc($unpaidBilledTreatmentCount) ?></dd></div></dl>
<?php if (!empty($treatments)): ?>
                                    <table class="patient-record-table studio-grid" data-record-type="treatments" data-grid-widths="12,25,15,15,21,12" aria-label="<?= esc(lang('UI.patientTreatments'),'attr') ?>"><thead><tr><?php foreach(['UI.patientDate','UI.patientTreatments','Finance.provider','UI.patientBilling','Finance.invoice','UI.patientActions'] as $key): ?><th scope="col"><?= esc(lang($key)) ?></th><?php endforeach; ?></tr></thead><tbody>
                                        <?php foreach ($treatments as $trt): ?>
                                            <?php
                                            $rowBilling = $treatmentBillingMeta($trt);
                                            $treatmentId = (int) ($trt['id'] ?? 0);
                                            $treatmentUrl = workflow_context_url('treatment/' . $treatmentId, $patientContextReturnTo);
                                            $treatmentStatus = strtolower((string) ($trt['status'] ?? ''));
                                            $treatmentStatusLabel = match ($treatmentStatus) {
                                                'active' => lang('UI.patientStatusActive'),
                                                'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                'pending' => lang('UI.patientTreatmentStatusPending'),
                                                'in_progress' => lang('UI.patientTreatmentStatusInProgress'),
                                                'needs_attention' => lang('UI.patientTreatmentStatusNeedsAttention'),
                                                'on_hold' => lang('UI.patientStatusPending'),
                                                'cancelled' => lang('UI.patientStatusInactive'),
                                                default => $treatmentStatus !== '' ? ucfirst(str_replace('_', ' ', $treatmentStatus)) : lang('UI.patientNa'),
                                            };
                                            $statusClass = match ($treatmentStatus) {
                                                'active', 'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200/70',
                                                'completed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                'on_hold', 'pending', 'needs_attention' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                default => 'bg-slate-100 text-slate-700 ring-slate-200/70',
                                            };
                                            $doctorName = trim((string) (($trt['doctor_first_name'] ?? '') . ' ' . ($trt['doctor_last_name'] ?? '')));
                                            if ($doctorName === '') {
                                                $doctorName = lang('UI.patientUnknownDoctor');
                                            }
                                            $treatmentType = trim((string) ($trt['treatment_type'] ?? ''));
                                            $treatmentTypeLabel = $treatmentType !== '' ? ucfirst(str_replace('_', ' ', $treatmentType)) : lang('UI.patientGeneral');
                                            $billTreatmentFromPatientUrl = '';
                                            if (
                                                has_permission('finance', 'create')
                                                && !$rowBilling['is_billed']
                                                && (int) ($rowBilling['charge_line_id'] ?? 0) > 0
                                                && (float) ($rowBilling['amount_raw'] ?? 0) > 0
                                            ) {
                                                $billTreatmentFromPatientUrl = workflow_context_url(
                                                    'finance/create?' . http_build_query([
                                                        'patient_id' => (int) $patient['id'],
                                                        'transaction_type' => 'invoice',
                                                        'clinical_charge_line_ids' => [(int) $rowBilling['charge_line_id']],
                                                    ], '', '&', PHP_QUERY_RFC3986),
                                                    'patient/' . (int) $patient['id'] . '?tab=treatments'
                                                );
                                            }
                                            ?>
                                            <tr class="patient-record-row" data-href="<?= esc($treatmentUrl) ?>" data-date="<?= esc(!empty($trt['start_date'])?substr($trt['start_date'],0,10):'','attr') ?>"><td><?= !empty($trt['start_date'])?esc(formatDate($trt['start_date'])):esc(lang('UI.patientNa')) ?></td><td><strong><?= esc($trt['treatment_name']??lang('UI.patientNa')) ?></strong><small><?= esc($treatmentTypeLabel) ?> · <?= esc(lang('UI.patientTooth')) ?> <?= esc($trt['tooth_number']??lang('UI.patientNa')) ?></small><?php if(!empty($trt['treatment_description'])): ?><small><?= esc($trt['treatment_description']) ?></small><?php endif; ?></td><td><?= esc($doctorName) ?></td><td><strong><?= esc($rowBilling['amount_text']) ?></strong><small><?= esc($rowBilling['status_text']) ?></small><span class="patient-record-status"><?= esc($treatmentStatusLabel) ?></span></td><td><?php if(!empty($rowBilling['finance_id'])): ?><a href="<?= esc(workflow_context_url('finance/'.(int)$rowBilling['finance_id'],$patientContextReturnTo)) ?>"><?= esc($rowBilling['invoice_number']!==''?$rowBilling['invoice_number']:lang('Finance.invoice')) ?></a><?php if($rowBilling['invoice_status_text']!==''): ?><small><?= esc($rowBilling['invoice_status_text']) ?></small><?php endif; ?><small><?= esc(lang('UI.patientPaidShort')) ?> <?= esc($rowBilling['paid_text']) ?></small><small><?= esc(lang('UI.patientBalanceShort')) ?> <?= esc($rowBilling['balance_text']) ?></small><?php else: ?><?= esc(lang('UI.patientNa')) ?><?php endif; ?></td><td><a class="patient-table-action" href="<?= esc($treatmentUrl) ?>"><?= esc(lang('UI.patientView')) ?></a><?php if($billTreatmentFromPatientUrl!==''): ?><button type="button" data-patient-charge-line-open data-charge-line-id="<?= (int)$rowBilling['charge_line_id'] ?>"><?= esc(lang('UI.patientCreateInvoiceFromWork')) ?></button><?php endif; ?></td></tr>
                                        <?php endforeach; ?>
</tbody></table>
                                <?php else: ?>
                                    <div >
                                        <div class="patient-empty">

                                            <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoTreatments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
