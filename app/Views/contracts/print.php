<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Treatment Contract</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1, h2 { margin: 0 0 12px 0; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 16px; }
        .section { margin-bottom: 20px; }
        .muted { color: #6b7280; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f9fafb; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; }
        .total { text-align: right; font-weight: bold; margin-top: 8px; }
        .signature img { max-width: 240px; border: 1px solid #e5e7eb; padding: 6px; border-radius: 6px; }
        .odontogram svg { width: 100%; height: auto; }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Treatment Contract</h1>
            <div class="muted">Generated on <?= date('Y-m-d H:i') ?></div>
        </div>
        <div class="muted">
            Status: <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
        </div>
    </div>

    <div class="section">
        <h2>Patient</h2>
        <div><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></div>
        <div class="muted">Patient ID: <?= esc($patient['patient_id'] ?? $patient['id']) ?></div>
    </div>

    <div class="section odontogram">
        <h2>Contract Odontogram</h2>
        <?= $snapshot['svg_snapshot'] ?? '<div class="muted">No snapshot captured.</div>' ?>
    </div>

    <div class="section">
        <h2>Treatment Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Tooth</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['procedure_code'] ?? '-') ?></td>
                            <td><?= esc($item['tooth_no'] ?? '-') ?></td>
                            <td><?= esc($item['description'] ?? '-') ?></td>
                            <td><?= esc($item['qty'] ?? 1) ?></td>
                            <td><?= number_format((float) ($item['price'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="muted">No items added.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($contract['cost_details'])): ?>
            <div class="muted" style="margin-top: 8px;">
                <strong>Cost Details:</strong> <?= nl2br(esc($contract['cost_details'])) ?>
            </div>
        <?php endif; ?>
        <div class="total">Total: <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?></div>
    </div>

    <?php if (!empty($contract['terms_conditions'])): ?>
        <div class="section">
            <h2>Terms &amp; Conditions</h2>
            <div><?= nl2br(esc($contract['terms_conditions'])) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($contract['payment_terms'])): ?>
        <div class="section">
            <h2>Payment Terms</h2>
            <div><?= nl2br(esc($contract['payment_terms'])) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($contract['duration_plan'])): ?>
        <div class="section">
            <h2>Duration Plan</h2>
            <div><?= esc($contract['duration_plan']) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($contract['notes'])): ?>
        <div class="section">
            <h2>Notes</h2>
            <div><?= nl2br(esc($contract['notes'])) ?></div>
        </div>
    <?php endif; ?>

    <div class="section signature">
        <h2>Signature</h2>
        <?php if (!empty($signature)): ?>
            <div>Signed by: <?= esc($signature['signer_name'] ?? 'Patient') ?></div>
            <div class="muted">Signed at: <?= esc($signature['signed_at'] ?? '') ?></div>
            <?php if (!empty($signature['signature_data'])): ?>
                <img src="<?= esc($signature['signature_data']) ?>" alt="Signature">
            <?php endif; ?>
        <?php else: ?>
            <div class="muted">Not signed.</div>
        <?php endif; ?>
    </div>
</body>
</html>
