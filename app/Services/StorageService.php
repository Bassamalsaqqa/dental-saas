<?php

namespace App\Services;

use CodeIgniter\Files\File;
use App\Models\FileAttachmentModel;
use App\Services\PlanGuard;

class StorageService
{
    protected $attachmentModel;
    protected $basePath;
    protected $planGuard;

    public function __construct()
    {
        $this->attachmentModel = new FileAttachmentModel();
        $this->basePath = WRITEPATH . 'uploads';
        $this->planGuard = new PlanGuard();
    }

    /**
     * Store a file in a tenant-isolated directory
     */
    public function store($file, $clinicId, $entityType = null, $entityId = null)
    {
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
        }

        $tenantPath = 'clinic_' . $clinicId;
        $uploadPath = $this->basePath . DIRECTORY_SEPARATOR . $tenantPath;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $data = [
            'clinic_id'     => $clinicId,
            'file_name'     => $newName,
            'original_name' => $file->getClientName(),
            'file_path'     => $tenantPath . DIRECTORY_SEPARATOR . $newName,
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'created_by'    => session()->get('user_id')
        ];

        $id = $this->attachmentModel->insert($data);
        
        return $this->attachmentModel->find($id);
    }

    /**
     * Store an export artifact (content-based) in a tenant-isolated directory
     */
    public function storeExport($content, $fileName, $mimeType, $clinicId, $entityType = null, $entityId = null, $purpose = null)
    {
        // Enforce Plan Limits (P5-10)
        $this->planGuard->assertFeature($clinicId, 'exports.enabled');
        $this->planGuard->assertQuota($clinicId, 'exports', 1);

        $tenantPath = 'clinic_' . $clinicId;
        $uploadPath = $this->basePath . DIRECTORY_SEPARATOR . $tenantPath;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileHash = md5($content);
        $uniqueName = $fileHash . '_' . $fileName;
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $uniqueName;

        // Check for idempotency/superseding
        if ($entityType && $entityId && $purpose) {
            $this->attachmentModel->supersedePrevious($clinicId, $entityType, $entityId, $purpose);
        }

        file_put_contents($fullPath, $content);

        $data = [
            'clinic_id'     => $clinicId,
            'file_name'     => $uniqueName,
            'original_name' => $fileName,
            'file_path'     => $tenantPath . DIRECTORY_SEPARATOR . $uniqueName,
            'mime_type'     => $mimeType,
            'file_size'     => strlen($content),
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'purpose'       => $purpose,
            'file_hash'     => $fileHash,
            'created_by'    => session()->get('user_id') ?? 0
        ];

        $id = $this->attachmentModel->insert($data);
        
        // Enforce retention policy after successful persistence
        if ($entityType && $entityId && $purpose) {
            $retentionService = new \App\Services\RetentionService();
            $retentionService->enforcePolicy($clinicId, $entityType, $entityId, $purpose);
        }
        
        return $this->attachmentModel->find($id);
    }

    /**
     * Get the absolute path for a file attachment
     */
    public function getAbsolutePath($attachment)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $attachment['file_path'];
    }

    /**
     * Delete an attachment and its physical file
     */
    public function delete($attachmentId, $clinicId)
    {
        $attachment = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->find($attachmentId);

        if (!$attachment) {
            return false;
        }

        $filePath = $this->getAbsolutePath($attachment);
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return $this->attachmentModel->delete($attachmentId);
    }
}
