<div id="contracts-content" class="tab-content hidden">
                                <section ><div ><div ></div><div ></div></div><div class="patient-form-stack">
                                <?php if (session('success')): ?>
                                    <div >
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div >
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <div >
                                    <?php if (has_permission('patients', 'edit')): ?>
                                        <a href="<?= workflow_context_url('contracts/create/' . $patient['id'], $patientContextReturnTo) ?>"
                                           >
                                            <?= esc(lang('UI.patientCreateTreatmentPlan')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div >
                                    <div >
                                        <h4 ><?= esc(lang('UI.patientContractHistory')) ?></h4>
                                    </div>
                                    <div >
                                        <table class="patient-record-table studio-grid" data-record-type="contracts" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                    <th ><?= esc(lang('UI.patientExam')) ?></th>
                                                    <th ><?= esc(lang('UI.patientTotal')) ?></th>
                                                    <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($contracts)): ?>
                                                    <?php foreach ($contracts as $contract): ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($contract['created_at']) ? esc(date('Y-m-d', strtotime($contract['created_at']))) : '' ?>" data-href="<?= esc(workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo)) ?>">
                                                            <td >
                                                                <?= !empty($contract['created_at']) ? formatDateTime($contract['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <span >
                                                                    <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <?= !empty($contract['examination_id']) ? esc($contract['examination_id']) : '--' ?>
                                                            </td>
                                                            <td >
                                                                <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                                                            </td>
                                                    <td >
                                                        <div class="patient-table-actions">
                                                        <a href="<?= workflow_context_url('contracts/' . $contract['id'], $patientContextReturnTo) ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                        <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                                                            <a href="<?= workflow_context_url('contracts/' . $contract['id'] . '/edit', $patientContextReturnTo) ?>" 
                                                               class="patient-table-action" title="<?= esc(lang('UI.patientEdit')) ?>"><?= esc(lang('UI.patientEdit')) ?></a>
                                                            <form action="<?= base_url('contracts/' . $contract['id'] . '/delete') ?>" method="post" >
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" data-confirm-message="<?= esc(lang('UI.patientDeleteContractConfirm'),'attr') ?>" onclick="return window.StudioDialogs ? window.StudioDialogs.submitConfirm(event) : false;">

                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                                                           class="patient-table-action" title="<?= esc(lang('UI.patientPrint')) ?>"><?= esc(lang('UI.patientPrint')) ?></a>
                                                        </div>
                                                    </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="patient-empty"><?= esc(lang('UI.patientNoContractsYet')) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                </div></section>
                            </div>
