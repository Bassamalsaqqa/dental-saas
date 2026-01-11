<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
        }
        
        .header p {
            font-size: 10pt;
            margin: 5px 0 0 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-section h3 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }
        
        .total-amount {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .notes-section {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
        }
        
        .notes-section h3 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 10pt;
            text-align: center;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <?php if (!empty($clinic['logo_path'])): ?>
            <div style="text-align: center; margin-bottom: 10px;">
                <?php 
                $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                    ? $clinic['logo_path'] 
                    : base_url(ltrim($clinic['logo_path'], '/'));
                ?>
                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" style="max-height: 60px; width: auto;">
            </div>
        <?php endif; ?>
        <h1>INVENTORY USAGE RECORD</h1>
        <p>Dental Practice Management System</p>
    </div>
    
    <div class="info-grid">
        <div class="info-section">
            <h3>Usage Information</h3>
            <div class="info-item">
                <span class="info-label">Record ID:</span>
                <?= $usage['id'] ?>
            </div>
            <div class="info-item">
                <span class="info-label">Usage Date:</span>
                <?= formatDate($usage['usage_date']) ?>
            </div>
            <div class="info-item">
                <span class="info-label">Total Cost:</span>
                <?= formatCurrency($usage['total_cost']) ?>
            </div>
            <div class="info-item">
                <span class="info-label">Recorded By:</span>
                <?= $usage['recorded_by_name'] ?>
            </div>
        </div>
        
        <div class="info-section">
            <h3>Treatment Information</h3>
            <div class="info-item">
                <span class="info-label">Treatment ID:</span>
                <?= $usage['treatment_id'] ?>
            </div>
            <div class="info-item">
                <span class="info-label">Treatment:</span>
                <?= $usage['treatment_name'] ?>
            </div>
            <div class="info-item">
                <span class="info-label">Patient:</span>
                <?= $usage['first_name'] . ' ' . $usage['last_name'] ?>
            </div>
            <div class="info-item">
                <span class="info-label">Recorded On:</span>
                <?= formatDateTime($usage['created_at']) ?>
            </div>
        </div>
    </div>
    
    <h3>Items Used</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity Used</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $items = json_decode($usage['items_used'], true);
            foreach ($items as $item): 
            ?>
            <tr>
                <td><?= $item['item_name'] ?></td>
                <td><?= $item['quantity_used'] ?></td>
                <td><?= formatCurrency($item['unit_cost']) ?></td>
                <td><?= formatCurrency($item['total_cost']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-amount">
            Total Material Cost: <?= formatCurrency($usage['total_cost']) ?>
        </div>
    </div>
    
    <?php if (!empty($usage['notes'])): ?>
    <div class="notes-section">
        <h3>Notes</h3>
        <p><?= nl2br($usage['notes']) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>This record was generated on <?= date('F j, Y g:i A') ?></p>
        <p>Dental Practice Management System - Inventory Usage Report</p>
    </div>
</body>
</html>
