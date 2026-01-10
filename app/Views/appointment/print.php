<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details - <?= $appointment['appointment_id'] ?></title>
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
            max-width: 800px;
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
        
        .appointment-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 15px;
        }
        
        .appointment-id {
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
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-scheduled { background: #dbeafe; color: #1e40af; }
        .status-confirmed { background: #dcfce7; color: #166534; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-no_show { background: #fed7aa; color: #9a3412; }
        
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
            min-height: 100px;
            font-style: italic;
            color: #6b7280;
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
        <!-- Header -->
        <div class="header">
            <div class="clinic-name"><?= esc($clinic['name']) ?></div>
            <div class="clinic-subtitle">Professional Dental Services</div>
            <div class="appointment-title">
                Appointment Details
                <span class="appointment-id">#<?= $appointment['appointment_id'] ?></span>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Patient Information -->
            <div class="section">
                <div class="section-title">Patient Information</div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Patient ID:</span>
                    <span class="info-value"><?= $appointment['patient_number'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?= $appointment['phone'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= $appointment['email'] ?></span>
                </div>
            </div>
            
            <!-- Appointment Details -->
            <div class="section">
                <div class="section-title">Appointment Details</div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value"><?= ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?= date('F d, Y', strtotime($appointment['appointment_date'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Time:</span>
                    <span class="info-value"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value"><?= $appointment['duration'] ?> minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-<?= $appointment['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Notes Section -->
        <div class="notes-section">
            <div class="section-title">Additional Notes</div>
            <div class="notes-content">
                <?php if (!empty($appointment['notes'])): ?>
                    <?= nl2br(htmlspecialchars($appointment['notes'])) ?>
                <?php else: ?>
                    No additional notes for this appointment.
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="print-date">
                Printed on: <?= date('F d, Y \a\t g:i A') ?>
            </div>
            <div class="clinic-info">
                <strong><?= esc($clinic['name']) ?></strong><br>
                <?= esc($clinic['address']) ?><br>
                Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?><br>
                <?php if ($clinic['website']): ?>
                Website: <?= esc($clinic['website']) ?><br>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
