<div id="prescriptions-content" class="tab-content hidden">
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
                                                    <h3 ><?= esc(lang('UI.patientPrescriptions')) ?></h3>
                                                    <span ><?= count($prescriptions) ?> <?= esc(lang('UI.patientTabRx')) ?></span>
                                                </div>
                                                <p ><?= esc(lang('UI.patientPrescriptionsSubtitle')) ?></p>
                                            </div>
                                        </div>
                                        <?php if (has_permission('prescriptions', 'create')): ?>
                                            <a href="<?= workflow_context_url('prescription/create?patient_id=' . $patient['id'], $patientContextReturnTo) ?>"
                                               >

                                                <span><?= esc(lang('UI.patientCreatePrescription')) ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($prescriptions)): ?>
                                    <div >
                                        <div >
                                            <table class="patient-record-table studio-grid" data-record-type="prescriptions" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                                <thead >
                                                    <tr>
                                                        <th ><?= esc(lang('UI.patientMedication')) ?></th>
                                                        <th ><?= esc(lang('UI.patientDosage')) ?></th>
                                                        <th ><?= esc(lang('UI.patientFrequency')) ?></th>
                                                        <th ><?= esc(lang('UI.patientPrescribedDate')) ?></th>
                                                        <th ><?= esc(lang('UI.patientStatus')) ?></th>
                                                        <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody >
                                                    <?php foreach ($prescriptions as $prescription): ?>
                                                        <?php
                                                        $medicationName = lang('UI.patientNa');
                                                        $dosage = lang('UI.patientNa');
                                                        $frequency = lang('UI.patientNa');

                                                        try {
                                                            $medicinesData = json_decode((string) ($prescription['medication_name'] ?? ''), true);
                                                            if (is_array($medicinesData) && !empty($medicinesData)) {
                                                                $medicineNames = [];
                                                                $dosages = [];
                                                                $frequencies = [];

                                                                foreach ($medicinesData as $medicine) {
                                                                    if (!empty($medicine['name'])) {
                                                                        $medicineNames[] = (string) $medicine['name'];
                                                                    }
                                                                    if (!empty($medicine['dosage'])) {
                                                                        $dosages[] = (string) $medicine['dosage'];
                                                                    }
                                                                    if (!empty($medicine['frequency'])) {
                                                                        $frequencies[] = (string) $medicine['frequency'];
                                                                    }
                                                                }

                                                                $medicationName = !empty($medicineNames) ? implode(', ', $medicineNames) : lang('UI.patientNa');
                                                                $dosage = !empty($dosages) ? implode(', ', $dosages) : lang('UI.patientNa');
                                                                $frequency = !empty($frequencies) ? implode(', ', $frequencies) : lang('UI.patientNa');
                                                            } else {
                                                                $medicationName = $prescription['medication_name'] ?? lang('UI.patientNa');
                                                                $dosage = $prescription['dosage'] ?? lang('UI.patientNa');
                                                                $frequency = $prescription['frequency'] ?? lang('UI.patientNa');
                                                            }
                                                        } catch (Exception $e) {
                                                            $medicationName = $prescription['medication_name'] ?? lang('UI.patientNa');
                                                            $dosage = $prescription['dosage'] ?? lang('UI.patientNa');
                                                            $frequency = $prescription['frequency'] ?? lang('UI.patientNa');
                                                        }

                                                        $prescriptionStatusKey = strtolower((string) ($prescription['status'] ?? ''));
                                                        $prescriptionStatusLabel = match ($prescriptionStatusKey) {
                                                            'active' => lang('UI.patientStatusActive'),
                                                            'pending' => lang('UI.patientStatusPending'),
                                                            'cancelled' => lang('UI.patientStatusInactive'),
                                                            default => $prescriptionStatusKey !== '' ? ucfirst(str_replace('_', ' ', $prescriptionStatusKey)) : lang('UI.patientUnknown'),
                                                        };
                                                        $prescriptionStatusClass = match ($prescriptionStatusKey) {
                                                            'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/70',
                                                            'pending' => 'bg-amber-100 text-amber-800 ring-amber-200/70',
                                                            'expired', 'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200/70',
                                                            default => 'bg-slate-100 text-slate-700 ring-slate-200/70',
                                                        };
                                                        $prescriptionUrl = workflow_context_url('prescription/' . $prescription['id'], $patientContextReturnTo);
                                                        $prescribedDateIso = !empty($prescription['prescribed_date']) ? substr((string) $prescription['prescribed_date'], 0, 10) : '';
                                                        ?>
                                                        <tr class="patient-record-row" data-href="<?= esc($prescriptionUrl) ?>" data-date="<?= esc($prescribedDateIso, 'attr') ?>">
                                                            <td >
                                                                <div >
                                                                    <span >

                                                                    </span>
                                                                    <div >
                                                                        <div class="patient-record-title"><?= esc($medicationName) ?></div>
                                                                        <div ><?= esc(lang('UI.patientMedicationDetails')) ?></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td ><?= esc($dosage) ?></td>
                                                            <td ><?= esc($frequency) ?></td>
                                                            <td >
                                                                <?= !empty($prescription['prescribed_date']) ? esc(formatDate($prescription['prescribed_date'])) : esc(lang('UI.patientNa')) ?>
                                                            </td>
                                                            <td >
                                                                <span >
                                                                    <?= esc($prescriptionStatusLabel) ?>
                                                                </span>
                                                            </td>
                                                            <td >
                                                                <a href="<?= esc($prescriptionUrl) ?>" class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
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

                                            <p ><?= esc(lang('UI.patientNoPrescriptions')) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
