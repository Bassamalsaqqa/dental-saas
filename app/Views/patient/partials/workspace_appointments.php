<div id="appointments-content" class="tab-content hidden">
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['appointments'] ?? null, 'history_navigation' => $historyNavigation['appointments'] ?? null]) ?>
                            <section >
                                <div >
                                    <div ></div>
                                    <div ></div>
                                </div>
                                <div >
                                    <div >
                                        <div >
                                            <span >

                                            </span>
                                            <div >
                                                <div >
                                                    <h3 ><?= esc(lang('UI.patientAppointments')) ?></h3>
                                                    <span ><?= (int) ($historyCounts['appointments'] ?? count($appointments)) ?> <?= esc(lang('UI.patientTabAppts')) ?></span>
                                                </div>
                                                <p ><?= esc(lang('UI.patientAppointmentsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('appointments', 'create')): ?>
                                            <button type="button" data-app-drawer-open="patientAppointmentDrawer" class="patient-appointment-create-action">

                                                <span><?= esc(lang('UI.patientScheduleAppointment')) ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($appointments)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="appointments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientDateTime')) ?></th>
                                                        <th ><?= esc(lang('UI.patientType')) ?></th>
                                                        <th ><?= esc(lang('UI.patientDuration')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody >
                                                    <?php foreach ($appointments as $apt): ?>
                                                        <?php
                                                        $appointmentDateKey = !empty($apt['appointment_date']) ? substr((string) $apt['appointment_date'], 0, 10) : '';
                                                        $appointmentClinical = $appointmentDateKey !== '' ? ($sameDayClinicalByDate[$appointmentDateKey] ?? null) : null;
                                                        $appointmentClinicalCount = !empty($appointmentClinical)
                                                            ? (int) ($appointmentClinical['examination_count'] ?? count($appointmentClinical['examinations'] ?? [])) + (int) ($appointmentClinical['treatment_count'] ?? count($appointmentClinical['treatments'] ?? []))
                                                            : 0;
                                                        $appointmentStatusKey = strtolower((string) ($apt['status'] ?? ''));
                                                        $appointmentStatusLabel = match ($appointmentStatusKey) {
                                                            'scheduled' => lang('UI.appointmentStatusScheduled'),
                                                            'confirmed' => lang('UI.appointmentStatusConfirmed'),
                                                            'cancelled' => lang('UI.appointmentStatusCancelled'),
                                                            'no_show' => lang('UI.appointmentStatusNoShow'),
                                                            'completed' => lang('UI.patientTreatmentStatusCompleted'),
                                                            default => $appointmentStatusKey !== '' ? ucfirst(str_replace('_', ' ', $appointmentStatusKey)) : lang('UI.patientUnknown'),
                                                        };
                                                        $appointmentStatusClass = [
                                                            'scheduled' => 'bg-blue-100 text-blue-800 ring-blue-200/70',
                                                            'confirmed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                            'completed' => 'bg-slate-100 text-slate-800 ring-slate-200/70',
                                                            'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                            'no_show' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                        ][$appointmentStatusKey] ?? 'bg-slate-100 text-slate-700 ring-slate-200/70';
                                                        $appointmentTypeRaw = trim((string) ($apt['appointment_type'] ?? ''));
                                                        $appointmentTypeLabel = $appointmentTypeRaw !== '' ? ucfirst(str_replace('_', ' ', $appointmentTypeRaw)) : lang('UI.patientGeneral');
                                                        $appointmentChairLabel = trim((string) ($apt['chair_name'] ?? '')) !== '' ? (string) $apt['chair_name'] : lang('UI.appointmentChairDefault');
                                                        $appointmentUrl = workflow_context_url('appointment/' . $apt['id'], $patientContextReturnTo);
                                                        ?>
                                                        <tr class="patient-record-row" data-href="<?= esc($appointmentUrl) ?>" data-date="<?= esc($appointmentDateKey, 'attr') ?>">
                                                            <td >
                                                                <div ><?= esc(formatDate($apt['appointment_date'])) ?></div>
                                                                <div ><?= esc(lang('UI.patientAt')) ?> <?= esc(formatTime($apt['appointment_time'])) ?></div>
                                                            </td>
                                                            <td >
                                                                <div class="patient-record-title"><?= esc($appointmentTypeLabel) ?></div>
                                                                <div >

                                                                    <?= esc($appointmentChairLabel) ?>
                                                                </div>
                                                                <?php if ($appointmentClinicalCount > 0): ?>
                                                                    <div >
                                                                        <span >

                                                                            <span><?= esc(lang('UI.patientSameDayClinicalWork')) ?>: <?= esc((string) $appointmentClinicalCount) ?></span>
                                                                        </span>
                                                                        <?php foreach (array_slice(($appointmentClinical['examinations'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" >

                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                        <?php foreach (array_slice(($appointmentClinical['treatments'] ?? []), 0, 1) as $clinicalItem): ?>
                                                                            <a href="<?= esc($clinicalItem['url']) ?>" >

                                                                                <span><?= esc($clinicalItem['label']) ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td >
                                                                <span >

                                                                    <?= esc($apt['duration'] ?? '30') ?> <?= esc(lang('UI.patientMinutes')) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($appointmentStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <a href="<?= esc($appointmentUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div >
                                        <div class="patient-empty">

                                            <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoAppointments')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
