<?php

namespace App\Controllers;

use App\Models\FileAttachmentModel;

class FileController extends BaseController
{
    protected $attachmentModel;
    protected $storageService;

    public function __construct()
    {
        $this->attachmentModel = new FileAttachmentModel();
        $this->storageService = service('storage');
    }

    /**
     * Download or stream a file attachment
     */
    public function download($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        // Get the attachment record (scoped)
        $attachment = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->find($id);

        if (!$attachment) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        $filePath = $this->storageService->getAbsolutePath($attachment);

        if (!file_exists($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Physical file missing');
        }

        // Return the file stream
        return $this->response->download($filePath, null)
            ->setFileName($attachment['original_name'])
            ->setContentType($attachment['mime_type']);
    }
}
