<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Details - <?= esc($treatment['treatment_id'] ?? $treatment['id']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }

        .print-container {
            max-width: 820px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .clinic-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .clinic-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 15px;
        }

        .report-id {
            font-size: 18px;
            color: #6b7280;
            font-weight: normal;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .info-label {
            font-weight: 600;
            color: #4b5563;
        }

        .info-value {
            font-weight: bold;
            color: #1f2937;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active { background: #dcfce7; color: #166534; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-on_hold { background: #fef3c7; color: #92400e; }

        .notes-section {
            grid-column: 1 / -1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .notes-content {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            min-height: 90px;
            color: #4b5563;
        }

        .footer {
            text-align: center;
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }

        .print-date {
            margin-bottom: 10px;
        }

        .clinic-info {
            font-size: 12px;
            line-height: 1.4;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                margin: 0;
                padding: 0;
                max-width: none;
            }

            .section {
                break-inside: avoid;
            }

            .content {
                break-inside: avoid;
            }
        }

        @page {
            margin: 0.5in;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <?php if (!empty($clinic['logo_path'])): ?>
                <div style="text-align: center; margin-bottom: 10px;">
                    <?php 
                        $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0)
                            ? $clinic['logo_path']
                            : base_url(ltrim($clinic['logo_path'], '/'));
                    ?>
                    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" style="max-height: 80px; width: auto;">
                </div>
            <?php else: ?>
                <div style="text-align: center; margin-bottom: 10px; font-size: 48px; color: #2563eb;">
                    <span style="font-family: 'Font Awesome 6 Free'; font-weight: 900;">&#xf5c9;</span>
                </div>
            <?php endif; ?>
            <div class="clinic-name"><?= esc($clinic['name']) ?></div>
            <div class="clinic-subtitle"><?= esc($clinic['tagline']) ?></div>
            <div class="report-title">
                Treatment Details
                <span class="report-id">#<?= esc($treatment['treatment_id'] ?? $treatment['id']) ?></span>
            </div>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">Patient Information</div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= esc(($treatment['first_name'] ?? '') . ' ' . ($treatment['last_name'] ?? '')) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?= esc($treatment['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= esc($treatment['email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?= $treatment['date_of_birth'] ? date('F d, Y', strtotime($treatment['date_of_birth'])) : 'N/A' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value"><?= esc(ucfirst($treatment['gender'] ?? 'N/A')) ?></span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Treatment Details</div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value"><?= esc(ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? 'N/A'))) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tooth #:</span>
                    <span class="info-value"><?= esc($treatment['tooth_number'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="badge status-<?= esc($treatment['status'] ?? 'active') ?>">
                        <?= esc(ucfirst(str_replace('_', ' ', $treatment['status'] ?? 'active'))) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cost:</span>
                    <span class="info-value"><?= formatCurrency($treatment['cost'] ?? 0) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value"><?= $treatment['start_date'] ? date('F d, Y', strtotime($treatment['start_date'])) : 'N/A' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estimated Duration:</span>
                    <span class="info-value"><?= esc($treatment['estimated_duration'] ?? 'N/A') ?> minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Completion Date:</span>
                    <span class="info-value"><?= $treatment['completion_date'] ? date('F d, Y', strtotime($treatment['completion_date'])) : 'Not completed' ?></span>
                </div>
            </div>
        </div>

        <div class="notes-section">
            <div class="section-title">Treatment Description</div>
            <div class="notes-content">
                <?= !empty($treatment['treatment_description']) ? nl2br(htmlspecialchars($treatment['treatment_description'])) : 'No description provided.' ?>
            </div>
        </div>

        <?php if (!empty($treatment['treatment_notes'])): ?>
            <div class="notes-section">
                <div class="section-title">Treatment Notes</div>
                <div class="notes-content">
                    <?= nl2br(htmlspecialchars($treatment['treatment_notes'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer">
            <div class="print-date">
                Printed on: <?= date('F d, Y \a\t g:i A') ?>
            </div>
            <div class="clinic-info">
                <strong><?= esc($clinic['name']) ?></strong><br>
                <?= esc($clinic['address']) ?><br>
                Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?><br>
                <?php if (!empty($clinic['website'])): ?>
                    Website: <?= esc($clinic['website']) ?><br>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
