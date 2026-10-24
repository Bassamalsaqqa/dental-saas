<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $return_to = $return_to ?? null; ?>
<?php
$snapshotRows = [];
if (!empty($snapshot['data_snapshot_json'])) {
    $decodedSnapshotRows = json_decode($snapshot['data_snapshot_json'], true);
    if (is_array($decodedSnapshotRows)) {
        $snapshotRows = array_values(array_filter($decodedSnapshotRows, static function ($row) {
            return !empty($row['condition_type']) && ($row['condition_type'] !== 'healthy');
        }));
    }
}
?>
<div class="denta-contracts-workspace v3-module-workspace">
    <div class="space-y-6">
        <div class="v3-command-layout">
            <div>
                <h1 class="v3-title-scale font-bold v3-text-ink"><?= esc(lang('Contracts.contractTitle')) ?></h1>
                <p class="v3-text-muted mt-1"><?= esc(lang('Contracts.patientLabel')) ?> <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            </div>
            <div class="v3-command-actions">
                <a href="<?= esc(workflow_back('patient/' . $patient['id'], $return_to ?? null)) ?>"
                   class="inline-flex items-center px-4 py-2 v3-soft-surface v3-text-on-primary text-sm font-medium rounded-md v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc(lang('Contracts.back')) ?>
                </a>
                <a href="<?= workflow_context_url('patient/' . $patient['id'], $return_to ?? null) ?>"
                   class="inline-flex items-center px-4 py-2 v3-bg-soft v3-text-ink text-sm font-medium rounded-md v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-user mr-2"></i><?= esc(lang('Contracts.patientProfile')) ?>
                </a>
                <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                    <a href="<?= workflow_context_url('contracts/' . $contract['id'] . '/edit', $return_to ?? null) ?>"
                       class="inline-flex items-center px-4 py-2 v3-soft-surface v3-text-on-primary text-sm font-medium rounded-md v3-hover-primary v3-btn v3-btn--secondary">
                        <i class="fas fa-pen mr-2"></i><?= esc(lang('Contracts.editDraft')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>"
                   class="inline-flex items-center px-4 py-2 v3-surface v3-text-ink text-sm font-medium rounded-md border v3-border v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-print mr-2"></i><?= esc(lang('Contracts.print')) ?>
                </a>
                <?php if (!empty($contract['signed_attachment_id'])): ?>
                    <a href="<?= base_url('file/download/' . $contract['signed_attachment_id']) ?>"
                       class="inline-flex items-center px-4 py-2 v3-soft-surface v3-text-on-primary text-sm font-medium rounded-md v3-hover-primary v3-btn v3-btn--secondary">
                        <i class="fas fa-download mr-2"></i><?= esc(lang('Contracts.download')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="rounded-lg border v3-border-default v3-bg-paid p-4 v3-text-primary text-sm">
                <?= esc(session('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="rounded-lg border v3-border-default v3-bg-alert p-4 v3-text-danger text-sm">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <div class="contract-review-layout">
            <?php $creatorName = trim(($contract['creator_first_name'] ?? '') . ' ' . ($contract['creator_last_name'] ?? '')); ?>
            <div class="space-y-6">
                <div class="contract-card v3-surface rounded-lg border v3-border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold v3-text-ink"><?= esc(lang('Contracts.contractOdontogram')) ?></h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                            <?= esc(($contract['status'] ?? 'draft') === 'signed' ? lang('Contracts.signed') : lang('Contracts.draft')) ?>
                        </span>
                    </div>
                    <div class="border v3-border rounded-lg p-4 v3-surface">
                        <?php if (!empty($snapshot['svg_snapshot'])): ?>
                            <?= $snapshot['svg_snapshot'] ?>
                        <?php elseif (!empty($snapshotRows)): ?>
                            <div class="space-y-3">
                                <p class="text-sm font-medium v3-text-ink"><?= esc(lang('Contracts.recordedConditions')) ?></p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y v3-border-default text-sm">
                                        <thead class="v3-surface">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.tooth')) ?></th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.condition')) ?></th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.notes')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y v3-border-default v3-surface">
                                            <?php foreach ($snapshotRows as $row): ?>
                                                <tr>
                                                    <td class="px-4 py-2 v3-text-ink"><?= esc((string) ($row['tooth_number'] ?? '--')) ?></td>
                                                    <td class="px-4 py-2 v3-text-ink"><?= esc(ucwords(str_replace('_', ' ', (string) ($row['condition_type'] ?? '')))) ?></td>
                                                    <td class="px-4 py-2 v3-text-muted"><?= esc((string) (($row['condition_description'] ?? '') ?: ($row['treatment_notes'] ?? '—'))) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm v3-text-muted"><?= esc(lang('Contracts.noSnapshot')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="contract-card v3-surface rounded-lg border v3-border p-6">
                    <h3 class="text-lg font-semibold v3-text-ink mb-4"><?= esc(lang('Contracts.treatmentItems')) ?></h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y v3-border-default text-sm">
                            <thead class="v3-surface">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.code')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.tooth')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.description')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.quantity')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold v3-text-muted uppercase"><?= esc(lang('Contracts.price')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y v3-border-default">
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td class="px-4 py-2"><?= esc($item['procedure_code'] ?? '-') ?></td>
                                            <td class="px-4 py-2"><?= esc($item['tooth_no'] ?? '-') ?></td>
                                            <td class="px-4 py-2"><?= esc($item['description'] ?? '-') ?></td>
                                            <td class="px-4 py-2"><?= esc($item['qty'] ?? 1) ?></td>
                                            <td class="px-4 py-2"><?= number_format((float) ($item['price'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center v3-text-muted"><?= esc(lang('Contracts.noItems')) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($contract['cost_details'])): ?>
                        <div class="mt-4 rounded-lg border v3-border v3-surface p-3 text-sm v3-text-ink">
                            <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.costDetails')) ?></div>
                            <div><?= nl2br(esc($contract['cost_details'])) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4 text-right text-sm v3-text-ink">
                        <span class="font-semibold"><?= esc(lang('Contracts.totalLabel')) ?></span>
                        <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="contract-card v3-surface rounded-lg border v3-border p-6">
                    <h3 class="text-lg font-semibold v3-text-ink mb-4"><?= esc(lang('Contracts.contractDetails')) ?></h3>
                    <div class="space-y-4 text-sm v3-text-ink">
                        <?php if (!empty($contract['terms_conditions'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.terms')) ?></div>
                                <div><?= nl2br(esc($contract['terms_conditions'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['payment_terms'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.paymentTerms')) ?></div>
                                <div><?= nl2br(esc($contract['payment_terms'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['duration_plan'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.durationPlan')) ?></div>
                                <div><?= esc($contract['duration_plan']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['notes'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.notes')) ?></div>
                                <div><?= nl2br(esc($contract['notes'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-xs uppercase tracking-wide v3-text-muted mb-1"><?= esc(lang('Contracts.createdBy')) ?></div>
                            <div><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?></div>
                        </div>
                        <?php if (empty($contract['terms_conditions']) && empty($contract['payment_terms']) && empty($contract['duration_plan']) && empty($contract['notes'])): ?>
                            <div class="text-sm v3-text-muted"><?= esc(lang('Contracts.noTerms')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="contract-card v3-surface rounded-lg border v3-border p-6" id="signatureSection">
                    <h3 class="text-lg font-semibold v3-text-ink mb-4"><?= esc(lang('Contracts.signature')) ?></h3>
                    <?php if (!empty($signature)): ?>
                        <div class="space-y-3">
                            <p class="text-sm v3-text-ink"><?= esc(lang('Contracts.signedBy')) ?> <strong><?= esc($signature['signer_name'] ?? 'Patient') ?></strong></p>
                            <p class="text-xs v3-text-muted"><?= esc(lang('Contracts.signedAt')) ?> <?= esc($signature['signed_at'] ?? '') ?></p>
                            <?php if (!empty($signature['signature_data'])): ?>
                                <img src="<?= esc($signature['signature_data']) ?>" alt="Signature" class="border v3-border rounded-lg">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if (($contract['status'] ?? 'draft') === 'draft' && !empty($signature_enabled)): ?>
                            <?php
                                $signAction = base_url('contracts/' . $contract['id'] . '/sign');
                                if (!empty($return_to)) {
                                    $signAction .= '?return=' . urlencode($return_to);
                                }
                            ?>
                            <form action="<?= $signAction ?>" method="post" id="signatureForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="signature_data" id="signatureData">
                                <?php if (!empty($return_to)): ?>
                                    <input type="hidden" name="return" value="<?= esc($return_to) ?>">
                                <?php endif; ?>
                                <?php $defaultSignerName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')); ?>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-1"><?= esc(lang('Contracts.signerName')) ?></label>
                                        <input type="text" name="signer_name" required
                                               value="<?= esc($defaultSignerName) ?>"
                                               class="w-full px-3 py-2 border v3-border rounded-lg text-sm v3-input" placeholder="Patient full name">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-2"><?= esc(lang('Contracts.drawSignature')) ?></label>
                                        <canvas id="signatureCanvas" class="border v3-border rounded-lg w-full" height="150" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                        <button type="button" id="clearSignatureBtn"
                                                class="mt-2 inline-flex items-center px-3 py-1.5 rounded-md border v3-border v3-surface text-xs font-medium v3-text-ink v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary">
                                            <i class="fas fa-eraser mr-1.5 v3-text-muted"></i><?= esc(lang('Contracts.clearSignature')) ?>
                                        </button>
                                    </div>
                                    <button type="submit" class="w-full px-4 py-2 v3-bg-primary v3-text-on-primary rounded-lg font-semibold v3-hover-primary v3-btn v3-btn--primary">
                                        <?= esc(lang('Contracts.signContract')) ?>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-sm v3-text-muted"><?= esc(lang('Contracts.signingDisabled')) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        canvas.style.touchAction = 'none';

        const getScale = () => {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return { rect, scaleX, scaleY };
        };

        const startDraw = (e) => {
            if (e.cancelable) e.preventDefault();
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(getX(e), getY(e));
        };
        const endDraw = (e) => {
            if (e && e.cancelable) e.preventDefault();
            drawing = false;
        };
        const draw = (e) => {
            if (e.cancelable) e.preventDefault();
            if (!drawing) return;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#111827';
            ctx.lineTo(getX(e), getY(e));
            ctx.stroke();
        };
        const getX = (e) => {
            const point = e.touches ? e.touches[0] : e;
            const { rect, scaleX } = getScale();
            return (point.clientX - rect.left) * scaleX;
        };
        const getY = (e) => {
            const point = e.touches ? e.touches[0] : e;
            const { rect, scaleY } = getScale();
            return (point.clientY - rect.top) * scaleY;
        };

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mouseleave', endDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchend', endDraw, { passive: false });
        canvas.addEventListener('touchcancel', endDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });

        document.getElementById('clearSignatureBtn')?.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        document.getElementById('signatureForm')?.addEventListener('submit', () => {
            const dataUrl = canvas.toDataURL('image/png');
            document.getElementById('signatureData').value = dataUrl;
        });
    }
</script>
<?= $this->endSection() ?>
