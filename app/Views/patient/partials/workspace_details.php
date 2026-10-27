<div id="details-content" class="tab-content" role="tabpanel" aria-labelledby="details-tab">
                            <div class="patient-details-layout">
                                <div class="patient-details-main">
                                    <section class="patient-compact-card patient-compact-card-clinical">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientClinicalVisualsTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientClinicalVisualsHelp')) ?></p>
                                            </div>
                                        </div>
                                        <div class="patient-clinical-visuals-grid">
                                            <article class="patient-preview-card">
                                                <div class="patient-preview-card-header">
                                                    <div>
                                                        <h4><?= esc(lang('UI.patientCurrentOdontogramTitle')) ?></h4>
                                                        <p><?= esc(lang('UI.patientCurrentOdontogramHelp')) ?></p>
                                                    </div>
                                                    <a href="<?= workflow_context_url('odontogram/' . $patient['id'], $patientContextReturnTo) ?>" class="patient-preview-action">

                                                        <span><?= esc(lang('UI.patientOpenFullChart')) ?></span>
                                                    </a>
                                                </div>
                                                <div class="patient-preview-frame">
                                                    <?php if (!empty($odontogram) && $miniOdontogramInlineSvg !== ''): ?>
                                                        <div class="patient-odontogram-mini-svg">
                                                            <?= $miniOdontogramInlineSvg ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="patient-preview-empty">

                                                            <p><?= esc(lang('UI.patientNoOdontogramPreview')) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-metrics">
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['healthy_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientHealthy')) ?></span>
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['treated_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientTreated')) ?></span>
                                                    <span class="patient-preview-metric"><strong><?= (int) ($odontogram_stats['needs_treatment_count'] ?? 0) ?></strong> <?= esc(lang('UI.patientNeedsTreatment')) ?></span>
                                                </div>
                                            </article>
                                            <article class="patient-preview-card">
                                                <div class="patient-preview-card-header">
                                                    <div>
                                                        <h4><?= esc(lang('UI.patientLatestImagingTitle')) ?></h4>
                                                        <p><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                                    </div>
                                                    <?php if (!empty($attachments_enabled)): ?>
                                                        <button type="button" data-patient-tab="attachments" class="patient-preview-action">

                                                            <span><?= esc(lang('UI.patientOpenImagingWorkspace')) ?></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-frame">
                                                    <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                        <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="patient-imaging-preview-image">
                                                    <?php elseif (!empty($latestImagingAttachment)): ?>
                                                        <div class="patient-preview-empty">

                                                            <p><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="patient-preview-empty">

                                                            <p><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="patient-preview-meta">
                                                    <?php if (!empty($latestImagingAttachment)): ?>
                                                        <p class="patient-preview-meta-title"><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></p>
                                                        <p class="patient-preview-meta-copy"><?= esc($latestImagingAttachment['original_name'] ?? '') ?></p>
                                                        <p class="patient-preview-meta-copy"><?= !empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--' ?></p>
                                                    <?php else: ?>
                                                        <p class="patient-preview-meta-copy"><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </article>
                                        </div>
                                    </section><section class="patient-compact-card patient-compact-card-profile">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientSummaryTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientSummaryHelp')) ?></p>
                                            </div>
                                        </div>
                                        <div class="patient-facts-section patient-facts-section-primary">
                                            <div class="patient-facts-grid">
                                                <?php foreach ($patientOverviewFacts as $fact): ?>
                                                    <article class="patient-fact-card">
                                                        <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                        <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="patient-facts-split patient-facts-split-secondary">
                                            <section class="patient-facts-section">
                                                <div class="patient-facts-heading">
                                                    <h4><?= esc(lang('UI.patientContactSection')) ?></h4>
                                                    <p><?= esc(lang('UI.patientContactSectionHelp')) ?></p>
                                                </div>
                                                <div class="patient-facts-grid patient-facts-grid-compact">
                                                    <?php foreach ($patientContactFacts as $fact): ?>
                                                        <article class="patient-fact-card">
                                                            <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                            <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
                                            <section class="patient-facts-section">
                                                <div class="patient-facts-heading">
                                                    <h4><?= esc(lang('UI.patientChartSection')) ?></h4>
                                                    <p><?= esc(lang('UI.patientChartSectionHelp')) ?></p>
                                                </div>
                                                <div class="patient-facts-grid patient-facts-grid-compact">
                                                    <?php foreach ($patientChartFacts as $fact): ?>
                                                        <article class="patient-fact-card">
                                                            <span class="patient-fact-label"><?= esc($fact['label']) ?></span>
                                                            <p class="patient-fact-value"><?= esc($fact['value']) ?></p>
                                                        </article>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
                                        </div>
                                    </section>

                                </div>
                                <aside class="patient-details-sidebar">
                                    <?php if ($hasStructuredMedicalAlerts || $patient['medical_history'] || $patient['allergies'] || $patient['notes']): ?>
                                        <section class="patient-compact-card patient-compact-card-alerts">
                                            <div class="patient-compact-header">
                                                <div>
                                                    <h3 class="patient-compact-title"><?= esc(lang('UI.patientMedicalInformation')) ?></h3>
                                                    <p class="patient-compact-copy"><?= esc(lang('UI.patientMedicalSectionHelp')) ?></p>
                                                </div>
                                                <?php if ($medicalAlertCount > 0): ?>
                                                    <span class="patient-alert-count"><?= (int) $medicalAlertCount ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="patient-medical-body">
                                                <?php if ($hasStructuredMedicalAlerts): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h4>
                                                        </div>
                                                        <div class="patient-alert-grid">
                                                            <?php foreach ($medicalAlerts as $medicalAlertRow): ?>
                                                                <?php if (empty($medicalAlertRow['enabled'])) continue; ?>
                                                                <article class="patient-alert-tile">
                                                                    <div class="patient-alert-topline">
                                                                        <span class="patient-alert-title">

                                                                            <span><?= esc($medicalAlertRow['label']) ?></span>
                                                                        </span>
                                                                        <span class="patient-alert-flag"><?= esc(lang('UI.patientYes')) ?></span>
                                                                    </div>
                                                                    <?php if (!empty($medicalAlertRow['details'])): ?>
                                                                        <p class="patient-alert-copy"><?= nl2br(esc($medicalAlertRow['details'])) ?></p>
                                                                    <?php endif; ?>
                                                                </article>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['medical_history']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyMedicalHistory') : lang('UI.patientMedicalHistory')) ?></h4>
                                                        </div>
                                                        <div class="patient-note-panel">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['allergies']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc($hasStructuredMedicalAlerts ? lang('UI.patientLegacyAllergies') : lang('UI.patientAllergies')) ?></h4>
                                                        </div>
                                                        <div class="patient-note-panel">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                                <?php if ($patient['notes']): ?>
                                                    <section class="patient-medical-section">
                                                        <div class="patient-facts-heading">
                                                            <h4><?= esc(lang('UI.patientNotesTitle')) ?></h4>
                                                            <p><?= esc(lang('UI.patientNotesSectionHelp')) ?></p>
                                                        </div>
                                                        <div class="patient-note-panel patient-note-panel-soft">
                                                            <p class="patient-note-copy"><?= nl2br(htmlspecialchars($patient['notes'])) ?></p>
                                                        </div>
                                                    </section>
                                                <?php endif; ?>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                    <section class="patient-compact-card patient-compact-card-history">
                                        <div class="patient-compact-header">
                                            <div>
                                                <h3 class="patient-compact-title"><?= esc(lang('UI.patientChartHistoryTitle')) ?></h3>
                                                <p class="patient-compact-copy"><?= esc(lang('UI.patientChartHistoryHelp')) ?></p>
                                            </div>
                                            <?php if ($latestSnapshot): ?>
                                                <div class="patient-history-summary">
                                                    <span class="patient-history-summary-count"><?= count($snapshotPreviewRows) ?></span>
                                                    <span class="patient-history-summary-copy"><?= esc(lang('UI.patientSnapshotCapturedOn', ['date' => !empty($latestSnapshot['created_at']) ? formatDateTime($latestSnapshot['created_at']) : '--'])) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-snapshot-list">
                                            <?php if (!empty($snapshotPreviewRows)): ?>
                                                <div >
                                                    <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions', $patientContextReturnTo) ?>" >

                                                        <span><?= esc(lang('UI.patientChartHistoryOpenAll')) ?></span>
                                                    </a>
                                                </div>
                                                <?php foreach ($snapshotPreviewRows as $snapshotItem): ?>
                                                    <article class="patient-snapshot-item">
                                                        <div class="patient-snapshot-item-topline">
                                                            <span class="patient-snapshot-badge"><?= esc($snapshotItem['label']) ?></span>
                                                            <span class="patient-snapshot-date"><?= esc(lang('UI.patientSnapshotCapturedOn', ['date' => !empty($snapshotItem['created_at']) ? formatDateTime($snapshotItem['created_at']) : '--'])) ?></span>
                                                        </div>
                                                        <?php if (!empty($snapshotItem['raw_label'])): ?>
                                                            <p class="patient-snapshot-title"><?= esc($snapshotItem['raw_label']) ?></p>
                                                        <?php endif; ?>
                                                        <p class="patient-snapshot-copy"><?= esc(lang('UI.patientSnapshotTeethAffected', ['count' => (int) $snapshotItem['count']])) ?></p>
                                                        <div >
                                                            <?php if (!empty($snapshotItem['id'])): ?>
                                                                <a href="<?= workflow_context_url('patient/' . $patient['id'] . '/odontogram-versions/' . $snapshotItem['id'], $patientContextReturnTo) ?>" >

                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSelected')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($snapshotItem['source_url'])): ?>
                                                                <a href="<?= esc($snapshotItem['source_url']) ?>" >

                                                                    <span><?= esc(lang('UI.patientChartHistoryOpenSource')) ?></span>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </article>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="patient-preview-empty patient-preview-empty-compact">

                                                    <p><?= esc(lang('UI.patientNoChartSnapshots')) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                </aside>
                            </div>
                        </div>
