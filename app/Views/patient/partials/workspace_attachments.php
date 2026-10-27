<div id="attachments-content" class="tab-content hidden">
                                <?php
                                    $usageBytes = (int) ($attachments_storage_usage_bytes ?? 0);
                                    $limitGb = (int) ($attachments_storage_limit_gb ?? 0);
                                    $limitBytes = $limitGb > 0 ? $limitGb * 1024 * 1024 * 1024 : 0;
                                    $usagePercent = $limitBytes > 0 ? round(($usageBytes / $limitBytes) * 100) : 0;
                                    $maxFileMb = $attachments_max_file_mb ?? 5;
                                ?>

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

                                <?php if ($limitBytes > 0 && $usagePercent >= 80): ?>
                                    <div >
                                        <strong><?= esc(lang('UI.patientStorageWarning')) ?></strong> <?= esc(lang('UI.patientStorageWarningText', ['percent' => $usagePercent])) ?>
                                    </div>
                                <?php endif; ?>

                                <section ><div ><div ></div><div ></div></div><div class="patient-fields">
                                    <section >
                                        <div >
                                            <h3 ><?= esc(lang('UI.patientImagingWorkspaceLatestTitle')) ?></h3>
                                            <p ><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                                        </div>
                                        <div >
                                            <?php if (!empty($latestImagingAttachment)): ?>
                                                <div >
                                                    <div >
                                                        <?php if (!empty($latestImagingAttachment) && $latestImagingIsPreviewable && !empty($latestImagingPreviewSrc)): ?>
                                                            <img loading="lazy" decoding="async" src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" >
                                                        <?php else: ?>
                                                            <div class="patient-empty"><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div >
                                                    <div>
                                                        <h4 ><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></h4>
                                                        <p ><?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></p>
                                                        <p ><?= esc(!empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--') ?></p>
                                                    </div>
                                                    <div >
                                                        <?php if (!empty($latestImagingViewUrl) && $latestImagingIsPreviewable): ?>
                                                            <a href="<?= esc($latestImagingViewUrl) ?>" target="_blank" rel="noopener" >

                                                                <span><?= esc(lang('UI.patientOpenImaging')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($latestImagingOpenUrl)): ?>
                                                            <a href="<?= esc($latestImagingOpenUrl) ?>" >

                                                                <span><?= esc(lang('UI.patientImagingWorkspaceOpenFile')) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="patient-empty">

                                                    <p ><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>

                                    <aside>
                                        <section >
                                            <div >
                                                <h3 ><?= esc(lang('UI.patientUploadFiles')) ?></h3>
                                                <p ><?= esc(lang('UI.patientAttachmentsSubtitle', ['max' => ($maxFileMb === null ? lang('UI.patientUnlimited') : ((int) $maxFileMb . 'MB'))])) ?></p>
                                            </div>
                                            <div >
                                                <?php $selectedAttachmentCategory = old('category'); ?>
                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" >
                                                    <?= csrf_field() ?>
                                                    <div>
                                                        <label ><?= esc(lang('UI.patientUploadFiles')) ?></label>
                                                        <input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip" >
                                                    </div>
                                                    <div>
                                                        <label ><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
                                                        <select name="category" >
                                                            <option value=""><?= esc(lang('UI.patientAttachmentCategoryPlaceholder')) ?></option>
                                                            <?php foreach (($attachment_category_options ?? []) as $groupLabel => $groupOptions): ?>
                                                                <optgroup label="<?= esc($groupLabel) ?>">
                                                                    <?php foreach ($groupOptions as $optionLabel): ?>
                                                                        <option value="<?= esc($optionLabel) ?>" <?= $selectedAttachmentCategory === $optionLabel ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                                                                    <?php endforeach; ?>
                                                                </optgroup>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label ><?= esc(lang('UI.patientNotes')) ?></label>
                                                        <textarea name="notes" rows="4" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>" ></textarea>
                                                    </div>
                                                    <div >
                                                        <button type="submit" >

                                                            <span><?= esc(lang('UI.patientUpload')) ?></span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </section>
                                    </aside>
                                </div>

                                <div >
                                    <div >
                                        <h4 ><?= esc(lang('UI.patientAttachmentHistory')) ?></h4>
                                    </div>
                                    <div >
                                        <table class="patient-record-table studio-grid" data-record-type="attachments" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                                            <thead>
                                                <tr>
                                                    <th ><?= esc(lang('UI.patientDate')) ?></th>
                                                    <th ><?= esc(lang('UI.patientFile')) ?></th>
                                                    <th ><?= esc(lang('UI.patientCategory')) ?></th>
                                                    <th ><?= esc(lang('UI.patientNotes')) ?></th>
                                                    <th ><?= esc(lang('UI.patientActions')) ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($attachments)): ?>
                                                    <?php foreach ($attachments as $attachment): ?>
                                                        <?php $attachmentIsPreviewable = strpos((string) ($attachment['mime_type'] ?? ''), 'image/') === 0; ?>
                                                        <tr class="patient-record-row" data-date="<?= !empty($attachment['created_at']) ? esc(date('Y-m-d', strtotime($attachment['created_at']))) : '' ?>" data-href="<?= esc($attachmentIsPreviewable ? base_url('file/inline/' . $attachment['id']) : base_url('file/download/' . $attachment['id'])) ?>">
                                                            <td >
                                                                <?= !empty($attachment['created_at']) ? formatDateTime($attachment['created_at']) : '--' ?>
                                                            </td>
                                                            <td>
                                                                <div class="patient-record-main">
                                                                    <div class="patient-record-title"><?= esc($attachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></div>
                                                                    <div class="patient-record-subtitle"><?= number_format(($attachment['file_size'] ?? 0) / 1024, 1) ?> KB</div>
                                                                </div>
                                                            </td>
                                                            <td >
                                                                <?= esc($attachment['category'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td >
                                                                <?= esc($attachment['notes'] ?? lang('UI.patientDash')) ?>
                                                            </td>
                                                            <td >
                                                                <div class="patient-table-actions">
                                                                <?php if ($attachmentIsPreviewable): ?>
                                                                <a href="<?= base_url('file/inline/' . $attachment['id']) ?>" target="_blank" rel="noopener"
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientView')) ?>"><?= esc(lang('UI.patientView')) ?></a>
                                                                <?php endif; ?>
                                                                <a href="<?= base_url('file/download/' . $attachment['id']) ?>" 
                                                                   class="patient-table-action" title="<?= esc(lang('UI.patientDownload')) ?>"><?= esc(lang('UI.patientDownload')) ?></a>
                                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/' . $attachment['id'] . '/delete') ?>" method="post" >
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="patient-table-action patient-table-action-danger" title="<?= esc(lang('UI.patientDelete')) ?>" data-confirm-message="<?= esc(lang('UI.patientDeleteAttachmentConfirm'),'attr') ?>" onclick="return window.StudioDialogs ? window.StudioDialogs.submitConfirm(event) : false;">

                                                                    </button>
                                                                </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="patient-empty"><?= esc(lang('UI.patientNoAttachmentsYet')) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                </div>
