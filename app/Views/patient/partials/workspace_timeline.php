<div id="timeline-content" class="tab-content hidden">
                            <?= view('patient/partials/history_navigation', ['history_page' => $workspaceHistoryPages['timeline'] ?? null, 'history_navigation' => $historyNavigation['timeline'] ?? null]) ?>
                            <div class="patient-tab-ambient patient-timeline-workspace">
                                <div class="patient-timeline-workspace-header">
                                    <div class="patient-timeline-heading">
                                        <span class="patient-timeline-heading-icon">

                                        </span>
                                        <div >
                                            <h3><?= esc(lang('UI.patientTabTimeline')) ?></h3>
                                            <p><?= esc(lang('UI.patientHistory')) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <ul class="patient-timeline-list">
                                    <?php 
                                    $timeline = $workspaceHistoryPages['timeline']['items'] ?? \App\Services\PatientWorkspaceTimelinePresenter::build(get_defined_vars());

                                    foreach ($timeline as $index => $item): 
                                    ?>
                                    <li class="patient-timeline-node">
                                        <div >
                                            <?php if ($index !== count($timeline) - 1): ?>
                                                <span class="patient-timeline-rail" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['url'])): ?>
                                                <a href="<?= esc($item['url']) ?>" class="patient-timeline-link">
                                            <?php else: ?>
                                                <div class="patient-timeline-link">
                                            <?php endif; ?>
                                                <div class="patient-timeline-shell">
                                                    <div>
                                                        <span class="patient-timeline-icon">

                                                        </span>
                                                    </div>
                                                    <div class="patient-timeline-main">
                                                        <div class="patient-timeline-title-block">
                                                            <div class="patient-timeline-title"><?= esc($item['title']) ?></div>
                                                            <div class="patient-timeline-date">

                                                                <span><?= esc(lang('UI.patientTimelineOn')) ?> <?= !empty($item['date']) ? formatDate($item['date']) : esc(lang('UI.patientNa')) ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="patient-timeline-summary">
                                                            <p class="patient-timeline-description"><?= esc($item['description']) ?></p>
                                                            <?php
                                                            $timelineMeta = $item['meta'] ?? [];
                                                            if (!empty($item['doctor'])) {
                                                                $timelineMeta[] = [
                                                                    'icon' => 'fas fa-user-md',
                                                                    'text' => 'Dr. ' . $item['doctor'],
                                                                ];
                                                            }
                                                            $timelineFacts = array_values(array_filter($timelineMeta, static fn($metaItem): bool => !empty($metaItem['text'])));
                                                            $timelineFactsPreview = array_slice($timelineFacts, 0, 3);
                                                            $timelineFactsMore = max(0, count($timelineFacts) - count($timelineFactsPreview));
                                                            ?>
                                                            <?php if (!empty($timelineFactsPreview)): ?>
                                                                <div class="patient-timeline-facts">
                                                                    <?php foreach ($timelineFactsPreview as $metaItem): ?>
                                                                        <span class="patient-timeline-fact">
                                                                            <?php if (!empty($metaItem['icon'])): ?><?php endif; ?>
                                                                            <span><?= esc($metaItem['text']) ?></span>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                    <?php if ($timelineFactsMore > 0): ?>
                                                                        <span class="patient-timeline-fact-more">+<?= (int) $timelineFactsMore ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="patient-timeline-status">
                                                            <span class="patient-timeline-status-label">
                                                                <?= esc($item['status_label'] ?? ucfirst((string) ($item['status'] ?? ''))) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php if (!empty($item['url'])): ?>
                                                </a>
                                            <?php else: ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>

                                    <?php if (empty($timeline)): ?>
                                        <li>
                                            <div class="patient-empty">

                                                <p ><?= esc(lang(isset($workspace_history) ? 'PatientHistory.none' : 'UI.patientNoHistoryRecords')) ?></p>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
