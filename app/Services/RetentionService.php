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
        $mode = $this->settingsModel->withoutTenantScope()->getGlobalSetting('retention_mode', 'latest');
        
        switch ($mode) {
            case 'keep_last_n':
                $n = (int)$this->settingsModel->withoutTenantScope()->getGlobalSetting('retention_last_n', 5);
                $this->pruneToLastN($clinicId, $entityType, $entityId, $purpose, $n);
                break;
                
            case 'keep_x_days':
                $days = (int)$this->settingsModel->withoutTenantScope()->getGlobalSetting('retention_days', 30);
                $this->pruneOlderThan($clinicId, $entityType, $entityId, $purpose, $days);
                break;
                
            case 'latest':
            default:
                // Retain the newly persisted artifact; never supersede the entire scope.
                $this->pruneToLastN($clinicId, $entityType, $entityId, $purpose, 1);
                break;
        }
    }

    /**
     * Prune attachments keeping only the newest N
     */
    protected function pruneToLastN(int $clinicId, string $entityType, $entityId, string $purpose, int $n)
    {
        $n = max(1, $n);
        $attachments = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('purpose', $purpose)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
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
        // Soft deletion is not authorization to destroy clinical evidence.
        // A separately approved purge contract must define retention, legal holds,
        // signed-document protection, audit, backup and physical-file ownership.
        throw new \DomainException('Physical attachment cleanup is disabled pending an approved retention and purge policy.');
    }
}
