<?php

namespace App\Jobs;

use App\Libraries\TenantJob;
use App\Models\FinanceModel;
use App\Services\StorageService;
use App\Models\FileAttachmentModel;

/**
 * ExportReportsJob
 * 
 * Generates a financial report for a clinic and persists it.
 */
class ExportReportsJob extends TenantJob
{
    protected $financeModel;
    protected $storageService;

    public function __construct(int $clinicId = null)
    {
        parent::__construct($clinicId);
        $this->financeModel = new FinanceModel();
        $this->storageService = new StorageService();
    }

    /**
     * Run the job
     * 
     * @param array $params ['start_date' => 'Y-m-d', 'end_date' => 'Y-m-d']
     * @return array The attachment record
     */
    public function run(array $params = [])
    {
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-d');

        // Logic similar to Reports::getFinanceReports but scoped
        // We use the model which is already tenant-aware (if we passed clinic_id or set session)
        $stats = $this->financeModel->getFinanceStats($startDate, $endDate);
        
        $content = "Financial Report for " . $this->clinic['name'] . "\n";
        $content .= "Period: {$startDate} to {$endDate}\n";
        $content .= "-----------------------------------\n";
        $content .= "Total Revenue: " . ($stats['total_revenue'] ?? 0) . "\n";
        $content .= "Pending Payments: " . ($stats['pending_payments'] ?? 0) . "\n";
        $content .= "Total Transactions: " . ($stats['total_transactions'] ?? 0) . "\n";
        $content .= "-----------------------------------\n";
        $content .= "Generated at: " . date('Y-m-d H:i:s') . " (Background Job)\n";

        $fileName = "report_finance_bg_" . date('Ymd_His') . ".txt";
        
        // Persist via StorageService
        $attachment = $this->storageService->storeExport(
            $content,
            $fileName,
            'text/plain',
            $this->clinicId,
            'report',
            0,
            'background_report'
        );

        log_message('info', "ExportReportsJob completed for Clinic {$this->clinicId}. Attachment ID: {$attachment['id']}");

        return $attachment;
    }
}
