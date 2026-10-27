<div id="examinations-content" class="tab-content hidden">
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['examinations'] ?? null, 'history_navigation' => $historyNavigation['examinations'] ?? null]) ?>
                            <section class="patient-tab-ambient patient-exam-workspace">
                                <div class="patient-exam-header">
                                    <div class="patient-exam-heading">
                                        <span class="patient-exam-heading-icon"></span>
                                        <div >
                                            <h3><?= esc(lang('UI.patientExaminations')) ?></h3>
                                            <p><?= esc(lang('UI.patientExaminationsSubtitle')) ?></p>
                                        </div>
                                    </div>
                                    <div class="patient-exam-header-actions">
                                        <div class="patient-exam-count">
                                            <strong><?= (int) ($historyCounts['examinations'] ?? count($examinations)) ?></strong>
                                            <span><?= esc(lang('UI.patientTabExams')) ?></span>
                                        </div>
                                        <?php if (has_permission('examinations', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientExamDrawer" class="patient-exam-create-action">

                                                <span><?= esc(lang('UI.patientCreateExamination')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($examinations)): ?>
                                    <table class="patient-record-table studio-grid" data-record-type="examinations" data-grid-widths="14,15,14,28,17,12" aria-label="<?= esc(lang('UI.patientExaminations'),'attr') ?>"><thead><tr><?php foreach(['UI.patientDate','UI.patientType','UI.patientStatus','Examination.chief_complaint','Finance.provider','UI.patientActions'] as $key): ?><th scope="col"><?= esc(lang($key)) ?></th><?php endforeach; ?></tr></thead><tbody>
                                        <?php foreach ($examinations as $examination): ?>
                                            <?php
                                            $examStatusKey = strtolower((string) ($examination['status'] ?? ''));
                                            $examStatusLabel = match ($examStatusKey) {
                                                'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                'in_progress' => lang('UI.patientTreatmentStatusInProgress'),
                                                'pending' => lang('UI.patientStatusPending'),
                                                'active' => lang('UI.patientStatusActive'),
                                                'cancelled' => lang('UI.patientStatusInactive'),
                                                default => $examStatusKey !== '' ? ucfirst(str_replace('_', ' ', $examStatusKey)) : lang('UI.patientUnknown'),
                                            };
                                            $examStatusClass = $examStatusKey === 'completed' ? 'patient-exam-status-complete' : 'patient-exam-status-open';
                                            $examTypeRaw = trim((string) ($examination['examination_type'] ?? ''));
                                            $examTypeLabel = $examTypeRaw !== '' ? ucfirst(str_replace('_', ' ', $examTypeRaw)) : lang('UI.patientGeneral');
                                            $examComplaint = trim((string) ($examination['chief_complaint'] ?? ''));
                                            $examComplaintPreview = $examComplaint !== ''
                                                ? (function_exists('mb_strimwidth') ? mb_strimwidth($examComplaint, 0, 130, '...') : (strlen($examComplaint) > 127 ? substr($examComplaint, 0, 127) . '...' : $examComplaint))
                                                : lang('UI.patientNoComplaint');
                                            $examDoctorName = trim(((string) ($examination['doctor_first_name'] ?? '')) . ' ' . ((string) ($examination['doctor_last_name'] ?? '')));
                                            $examDateIso = !empty($examination['examination_date']) ? substr((string) $examination['examination_date'], 0, 10) : '';
                                            $examUrl = workflow_context_url('examination/' . $examination['id'], $patientContextReturnTo);
                                            ?>
                                            <tr class="patient-record-row" data-href="<?= esc($examUrl) ?>" data-date="<?= esc($examDateIso,'attr') ?>"><td><?= esc(formatDate($examination['examination_date'])) ?></td><td><strong><?= esc($examTypeLabel) ?></strong><small><?= esc(lang('UI.patientExam')) ?></small></td><td><span class="patient-record-status"><?= esc($examStatusLabel) ?></span></td><td><?= esc($examComplaintPreview) ?></td><td><?= esc($examDoctorName) ?></td><td><a class="patient-table-action" href="<?= esc($examUrl) ?>"><?= esc(lang('UI.patientView')) ?></a></td></tr>
                                        <?php endforeach; ?>
</tbody></table>
                                    <div class="patient-filter-empty hidden">

                                        <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="patient-exam-empty">

                                        <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoExaminations')) ?></p>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
