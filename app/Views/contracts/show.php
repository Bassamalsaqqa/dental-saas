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
<div class="w-full max-w-none px-4 sm:px-6 lg:px-12 py-8">
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Treatment Contract</h1>
                <p class="text-gray-600 mt-1">Patient: <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            </div>
            <div class="flex space-x-3">
                <?php if (!empty($return_to)): ?>
                    <a href="<?= base_url($return_to) ?>"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Examination
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('patient/' . $patient['id']) ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patient
                </a>
                <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                    <a href="<?= base_url('contracts/' . $contract['id'] . '/edit') ?>"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-pen mr-2"></i>Edit Draft
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-200 hover:bg-gray-50">
                    <i class="fas fa-print mr-2"></i>Print
                </a>
                <?php if (!empty($contract['signed_attachment_id'])): ?>
                    <a href="<?= base_url('file/download/' . $contract['signed_attachment_id']) ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-download mr-2"></i>Download
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 text-sm">
                <?= esc(session('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6 w-full max-w-none">
            <?php $creatorName = trim(($contract['creator_first_name'] ?? '') . ' ' . ($contract['creator_last_name'] ?? '')); ?>
            <div class="space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Contract Odontogram</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                            <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                        </span>
                    </div>
                    <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
                        <?php if (!empty($snapshot['svg_snapshot'])): ?>
                            <?= $snapshot['svg_snapshot'] ?>
                        <?php elseif (!empty($snapshotRows)): ?>
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700">Recorded tooth conditions at the time of this plan</p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tooth</th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Condition</th>
                                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
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
                            <p class="text-sm text-gray-500">No snapshot captured.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Treatment Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tooth</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
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
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No items added.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($contract['cost_details'])): ?>
                        <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm text-gray-700">
                            <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Cost Details</div>
                            <div><?= nl2br(esc($contract['cost_details'])) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4 text-right text-sm text-gray-700">
                        <span class="font-semibold">Total:</span>
                        <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Contract Details</h3>
                    <div class="space-y-4 text-sm text-gray-700">
                        <?php if (!empty($contract['terms_conditions'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Terms &amp; Conditions</div>
                                <div><?= nl2br(esc($contract['terms_conditions'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['payment_terms'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Payment Terms</div>
                                <div><?= nl2br(esc($contract['payment_terms'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['duration_plan'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Duration Plan</div>
                                <div><?= esc($contract['duration_plan']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contract['notes'])): ?>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Notes</div>
                                <div><?= nl2br(esc($contract['notes'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Created By</div>
                            <div><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?></div>
                        </div>
                        <?php if (empty($contract['terms_conditions']) && empty($contract['payment_terms']) && empty($contract['duration_plan']) && empty($contract['notes'])): ?>
                            <div class="text-sm text-gray-500">No additional contract terms provided.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6" id="signatureSection">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Signature</h3>
                    <?php if (!empty($signature)): ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-700">Signed by: <strong><?= esc($signature['signer_name'] ?? 'Patient') ?></strong></p>
                            <p class="text-xs text-gray-500">Signed at: <?= esc($signature['signed_at'] ?? '') ?></p>
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
                            <form action="<?= $signAction ?>" method="post" id="signatureForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="signature_data" id="signatureData">
                                <?php if (!empty($return_to)): ?>
                                    <input type="hidden" name="return" value="<?= esc($return_to) ?>">
                                <?php endif; ?>
                                <?php $defaultSignerName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')); ?>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Typed Name</label>
                                        <input type="text" name="signer_name" required
                                               value="<?= esc($defaultSignerName) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Patient full name">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Draw Signature</label>
                                        <canvas id="signatureCanvas" class="border border-gray-300 rounded-lg w-full" height="150" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                        <button type="button" id="clearSignatureBtn"
                                                class="mt-2 inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                                            <i class="fas fa-eraser mr-1.5 text-gray-500"></i>Clear Signature
                                        </button>
                                    </div>
                                    <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700">
                                        Sign Contract
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Signature capture is disabled for this plan or already signed.</p>
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
