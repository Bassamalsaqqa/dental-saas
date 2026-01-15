<?php

namespace App\Services;

use App\Models\FileAttachmentModel;
use App\Models\SettingsModel;

/**
 * RetentionService
 * 
 * Enforces export retention policies platform-wide.
 */
class RetentionService
{
    protected $attachmentModel;
    protected $settingsModel;

    public function __construct()
    {
        $this->attachmentModel = new FileAttachmentModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Enforce retention policy for a specific scope
     * 
     * @param int $clinicId
     * @param string $entityType
     * @param mixed $entityId
     * @param string $purpose
     */
    public function enforcePolicy(int $clinicId, string $entityType, $entityId, string $purpose)
    {
        $mode = $this->settingsModel->getGlobalSetting('retention_mode', 'latest');
        
        switch ($mode) {
            case 'keep_last_n':
                $n = (int)$this->settingsModel->getGlobalSetting('retention_last_n', 5);
                $this->pruneToLastN($clinicId, $entityType, $entityId, $purpose, $n);
                break;
                
            case 'keep_x_days':
                $days = (int)$this->settingsModel->getGlobalSetting('retention_days', 30);
                $this->pruneOlderThan($clinicId, $entityType, $entityId, $purpose, $days);
                break;
                
            case 'latest':
            default:
                // Default behavior already handled by supersedePrevious in storeExport
                // but we can call it here for completeness if needed.
                $this->attachmentModel->supersedePrevious($clinicId, $entityType, $entityId, $purpose);
                break;
        }
    }

    /**
     * Prune attachments keeping only the newest N
     */
    protected function pruneToLastN(int $clinicId, string $entityType, $entityId, string $purpose, int $n)
    {
        $attachments = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('purpose', $purpose)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (count($attachments) > $n) {
            $toDelete = array_slice($attachments, $n);
            foreach ($toDelete as $att) {
                $this->attachmentModel->delete($att['id']);
            }
        }
    }

    /**
     * Prune attachments older than X days
     */
    protected function pruneOlderThan(int $clinicId, string $entityType, $entityId, string $purpose, int $days)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('purpose', $purpose)
            ->where('created_at <', $cutoff)
            ->delete();
    }

    /**
     * Perform physical file cleanup for soft-deleted attachments (Superadmin Action)
     * 
     * @return int Number of files removed
     */
    public function physicalCleanup()
    {
        // Find only soft-deleted attachments
        $deleted = $this->attachmentModel->onlyDeleted()->findAll();
        $count = 0;
        $storageService = service('storage');

        foreach ($deleted as $att) {
            $filePath = $storageService->getAbsolutePath($att);
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $count++;
                }
            }
            // Permanently remove from DB
            $this->attachmentModel->deletePermanently($att['id']);
        }

        return $count;
    }
}
