<?php

namespace App\Services;

use CodeIgniter\Files\File;
use App\Models\FileAttachmentModel;

class StorageService
{
    protected $attachmentModel;
    protected $basePath;

    public function __construct()
    {
        $this->attachmentModel = new FileAttachmentModel();
        $this->basePath = WRITEPATH . 'uploads';
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
