<?php helper('asset'); ?>
<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-contracts.css') ?>?v=<?= esc(asset_version('assets/css/v4-contracts.css')) ?>">
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
<div class="v4-contract"><header class="v4-page-heading"><div><h1><?= esc(lang('V4.contractReview')) ?></h1><p><a href="<?= workflow_context_url('patient/'.$patient['id'],$return_to??null) ?>" dir="auto"><?= esc($patient['first_name'].' '.$patient['last_name']) ?></a><span class="v4-contract-reference">#<?= (int)$contract['id'] ?></span></p></div><div class="v4-page-actions"><a class="v4-button" href="<?= esc(workflow_back('patient/'.$patient['id'],$return_to??null)) ?>"><?= esc(lang('V4.back')) ?></a><?php if(($contract['status']??'draft')==='draft'): ?><a class="v4-button" href="<?= workflow_context_url('contracts/'.$contract['id'].'/edit',$return_to??null) ?>"><?= esc(lang('V4.editDraft')) ?></a><?php endif; ?><a class="v4-button" href="<?= base_url('contracts/'.$contract['id'].'/print') ?>"><i class="fas fa-print" aria-hidden="true"></i><?= esc(lang('Finance.print')) ?></a><?php if(!empty($contract['signed_attachment_id'])): ?><a class="v4-button v4-button--primary" href="<?= base_url('file/download/'.$contract['signed_attachment_id']) ?>"><?= esc(lang('V4.signedDocument')) ?></a><?php endif; ?></div></header><?php if(session('success')): ?><div class="v4-form-notice"><?= esc(session('success')) ?></div><?php endif; ?><?php if(session('error')): ?><div class="v4-form-error"><?= esc(session('error')) ?></div><?php endif; ?><?php $creatorName=trim(($contract['creator_first_name']??'').' '.($contract['creator_last_name']??'')); ?><div class="v4-contract-layout"><div class="v4-contract-main"><section class="v4-contract-section">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800"><?= esc(lang('V4.contractChart')) ?></h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                            <?php $v4ContractState=(string)($contract['status']??'draft');$v4ContractStateLabel=lang('V4.'.$v4ContractState); ?><?= esc($v4ContractStateLabel==='V4.'.$v4ContractState?ucfirst($v4ContractState):$v4ContractStateLabel) ?>
                        </span>
                    </div>
                    <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
                        <?php if (!empty($snapshot['svg_snapshot'])): ?>
                            <?= $snapshot['svg_snapshot'] ?>
                        <?php elseif (!empty($snapshotRows)): ?>
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700"><?= esc(lang('V4.snapshotContext')) ?></p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.tooth')) ?></th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.condition')) ?></th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.notes')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            <?php foreach ($snapshotRows as $row): ?>
                                                <tr>
                                                    <td class="px-4 py-2 text-gray-700"><?= esc((string) ($row['tooth_number'] ?? '--')) ?></td>
                                                    <td class="px-4 py-2 text-gray-700"><?= esc(ucwords(str_replace('_', ' ', (string) ($row['condition_type'] ?? '')))) ?></td>
                                                    <td class="px-4 py-2 text-gray-600"><?= esc((string) (($row['condition_description'] ?? '') ?: ($row['treatment_notes'] ?? '—'))) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500"><?= esc(lang('V4.noSnapshot')) ?></p>
                        <?php endif; ?>
                    </div>
                </section><section class="v4-contract-section">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?= esc(lang('V4.treatmentItems')) ?></h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.code')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.tooth')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.description')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.quantity')) ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase"><?= esc(lang('V4.price')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
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
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500"><?= esc(lang('V4.noItems')) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($contract['cost_details'])): ?>
                        <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm text-gray-700">
                            <div class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?= esc(lang('V4.costDetails')) ?></div>
                            <div><?= nl2br(esc($contract['cost_details'])) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4 text-right text-sm text-gray-700">
                        <span class="font-semibold"><?= esc(lang('V4.total')) ?></span>
                        <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                    </div>
                </section>
</div><aside class="v4-contract-agreement"><section class="v4-contract-section">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?= esc(lang('V4.contractDetails')) ?></h3>
                    <div class="space-y-4 text-sm text-gray-700">
                        <?php if (!empty($contract['terms_conditions'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Terms &amp; Conditions</div>
                                <div><?= nl2br(esc($contract['terms_conditions'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['payment_terms'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?= esc(lang('V4.paymentTerms')) ?></div>
                                <div><?= nl2br(esc($contract['payment_terms'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['duration_plan'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?= esc(lang('V4.durationPlan')) ?></div>
                                <div><?= esc($contract['duration_plan']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['notes'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?= esc(lang('V4.notes')) ?></div>
                                <div><?= nl2br(esc($contract['notes'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?= esc(lang('V4.createdBy')) ?></div>
                            <div><?= $creatorName !== '' ? esc($creatorName) : lang('V4.unknown') ?></div>
                        </div>
                        <?php if (empty($contract['terms_conditions']) && empty($contract['payment_terms']) && empty($contract['duration_plan']) && empty($contract['notes'])): ?>
                            <div class="text-sm text-gray-500"><?= esc(lang('V4.noTerms')) ?></div>
                        <?php endif; ?>
                    </div>
                </section><section class="v4-contract-section" id="signatureSection">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?= esc(lang('V4.signature')) ?></h3>
                    <?php if (!empty($signature)): ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-700"><?= esc(lang('V4.signedBy')) ?> <strong><?= esc($signature['signer_name'] ?? 'Patient') ?></strong></p>
                            <p class="text-xs text-gray-500"><?= esc(lang('V4.signedAt')) ?> <?= esc($signature['signed_at'] ?? '') ?></p>
                            <?php if (!empty($signature['signature_data'])): ?>
                                <img src="<?= esc($signature['signature_data']) ?>" alt="Signature" class="border border-gray-200 rounded-lg">
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
                            <form action="<?= $signAction ?>" method="post" id="signatureForm" class="v4-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="signature_data" id="signatureData">
                                <?php if (!empty($return_to)): ?>
                                    <input type="hidden" name="return" value="<?= esc($return_to) ?>">
                                <?php endif; ?>
                                <?php $defaultSignerName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')); ?>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('V4.typedName')) ?></label>
                                        <input type="text" name="signer_name" required
                                               value="<?= esc($defaultSignerName) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="<?= esc(lang('V4.patientFullName'),'attr') ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= esc(lang('V4.drawSignature')) ?></label>
                                        <canvas id="signatureCanvas" class="border border-gray-300 rounded-lg w-full" height="150" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                        <button type="button" id="clearSignatureBtn"
                                                class="mt-2 inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                            <i class="fas fa-eraser mr-1.5 text-gray-500"></i><?= esc(lang('V4.clearSignature')) ?>
                                        </button>
                                    </div>
                                    <button type="submit" class="v4-button v4-button--primary">
                                        <?= esc(lang('V4.signContract')) ?>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-sm text-gray-500"><?= esc(lang('V4.signatureUnavailable')) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
</aside></div></div><script>
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
