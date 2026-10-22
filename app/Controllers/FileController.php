<?php

namespace App\Controllers;

use App\Models\FileAttachmentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class FileController extends BaseController
{
    protected $attachmentModel;
    protected $storageService;
    protected $permissionService;

    /**
     * Map of supported clinical/internal entities to table and permission specs.
     * Soft-delete (deleted_at IS NULL) is enforced on all six clinical/contract tables that have deleted_at;
     * inventory_usage does not possess a deleted_at column and remains soft_delete => false.
     */
    private const ENTITY_MAP = [
        'patient'         => ['table' => 'patients',            'module' => 'patients',     'action' => 'view', 'has_patient' => false, 'soft_delete' => true],
        'examination'     => ['table' => 'examinations',        'module' => 'examinations', 'action' => 'view', 'has_patient' => true,  'soft_delete' => true],
        'appointment'     => ['table' => 'appointments',        'module' => 'appointments', 'action' => 'view', 'has_patient' => true,  'soft_delete' => true],
        'treatment'       => ['table' => 'treatments',          'module' => 'treatments',   'action' => 'view', 'has_patient' => true,  'soft_delete' => true],
        'prescription'    => ['table' => 'prescriptions',       'module' => 'prescriptions','action' => 'view', 'has_patient' => true,  'soft_delete' => true],
        'contract'        => ['table' => 'treatment_contracts', 'module' => 'patients',     'action' => 'view', 'has_patient' => true,  'soft_delete' => true],
        'inventory_usage' => ['table' => 'inventory_usage',     'module' => 'inventory',    'action' => 'view', 'has_patient' => false, 'soft_delete' => false],
        'inventory'       => ['table' => 'inventory_usage',     'module' => 'inventory',    'action' => 'view', 'has_patient' => false, 'soft_delete' => false],
    ];

    private const ALLOWED_FINANCE_EXPORT_PURPOSES = [
        'finance_export',
        'finance_ledger_export',
        'invoice_print',
    ];

    private const ALLOWED_REPORT_PURPOSES = [
        'report_html',
        'report_excel',
        'report_csv',
        'background_report',
    ];

    private const ALLOWED_INLINE_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private const ACTIVE_OR_UNTRUSTED_MIMES = [
        'text/html',
        'text/javascript',
        'application/javascript',
        'application/x-javascript',
        'image/svg+xml',
        'application/xml',
        'text/xml',
        'application/x-httpd-php',
        'application/x-sh',
        'application/x-csh',
        'application/xhtml+xml',
    ];

    public function __construct()
    {
        $this->attachmentModel = new FileAttachmentModel();
        $this->storageService = service('storage');
        $this->permissionService = service('permission') ?? new \App\Services\PermissionService();
    }

    /**
     * Download a file attachment with strict fail-closed authorization.
     * Forces application/octet-stream on active/untrusted types.
     */
    public function download($id)
    {
        $this->response->noCache();
        $auth = $this->authorizeAttachmentAccess((int) $id, 'download');
        if ($auth instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $auth;
        }

        $attachment = $auth['attachment'];
        $realFilePath = $auth['real_file_path'];

        $safeFileName = basename($attachment['original_name'] ?: 'file');
        $safeFileName = str_replace(["\r", "\n", '"'], '', $safeFileName);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $realFilePath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        $detectedMime = strtolower(trim((string) $detectedMime));

        $declaredMime = strtolower(trim((string) ($attachment['mime_type'] ?: 'application/octet-stream')));

        $contentType = $declaredMime;
        if (
            empty($detectedMime)
            || in_array($detectedMime, self::ACTIVE_OR_UNTRUSTED_MIMES, true)
            || in_array($declaredMime, self::ACTIVE_OR_UNTRUSTED_MIMES, true)
        ) {
            $contentType = 'application/octet-stream';
        }

        return $this->response->download($realFilePath, null)
            ->setFileName($safeFileName)
            ->setContentType($contentType)
            ->setHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Inline stream a file attachment (images and PDFs only) with strict fail-closed authorization.
     * Verifies detected MIME via finfo_file; active or forged uploads are rejected (403 Forbidden).
     */
    public function inline($id)
    {
        // Apply before authorization so controller-produced denial responses also
        // prevent retaining clinical content across logout or clinic changes.
        $this->response->noCache();
        $auth = $this->authorizeAttachmentAccess((int) $id, 'inline');
        if ($auth instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $auth;
        }

        $attachment = $auth['attachment'];
        $realFilePath = $auth['real_file_path'];

        $declaredMime = strtolower(trim((string) ($attachment['mime_type'] ?? '')));
        if ($declaredMime === 'image/jpg') {
            $declaredMime = 'image/jpeg';
        }

        // Verify physical file MIME type using finfo_file (never trust DB metadata alone)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $realFilePath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        $detectedMime = strtolower(trim((string) $detectedMime));

        // Active uploads (HTML, scripts, XML, SVG, etc.) or non-previewable files must NEVER be served inline
        if (
            !in_array($detectedMime, self::ALLOWED_INLINE_MIMES, true)
            || !in_array($declaredMime, self::ALLOWED_INLINE_MIMES, true)
            || $detectedMime !== $declaredMime
        ) {
            return $this->failForbidden('Inline viewing is not permitted for active, unverified, or non-previewable file types.');
        }

        $safeFileName = basename($attachment['original_name'] ?: 'file');
        $safeFileName = str_replace(["\r", "\n", '"'], '', $safeFileName);

        return $this->response
            ->setHeader('Content-Type', $detectedMime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeFileName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'; base-uri 'none'; sandbox")
            ->setBody((string) file_get_contents($realFilePath));
    }

    /** Serve a prebuilt verified raster only after original attachment authorization. */
    public function preview($id, string $kind)
    {
        $this->response->noCache();
        if (!in_array($kind, ['thumbnail', 'preview'], true)) { throw PageNotFoundException::forPageNotFound(); }
        $auth = $this->authorizeAttachmentAccess((int)$id, 'inline');
        if ($auth instanceof \CodeIgniter\HTTP\ResponseInterface) { return $auth; }
        if (!config('MediaDerivatives')->enabled) { return $this->inline($id); }
        try {
            $variant = (new \App\Services\AttachmentDerivativeService())->lookup((int)$auth['attachment']['clinic_id'], (int)$id, $kind);
        } catch (\DomainException $e) { throw PageNotFoundException::forPageNotFound(); }
        if (!$variant) { return $this->inline($id); }
        return $this->response->setHeader('Content-Type', $variant['mime_type'])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'; base-uri 'none'; sandbox")
            ->setBody((string)file_get_contents($variant['absolute_path']));
    }

    /**
     * Authorize attachment access with fail-closed entity-specific module permission,
     * tenant scoping, object ownership, and filesystem confinement.
     *
     * @param int $id Attachment ID
     * @param string $mode 'download' or 'inline'
     * @return array|\CodeIgniter\HTTP\ResponseInterface Array of authorized data or Response on failure
     */
    protected function authorizeAttachmentAccess(int $id, string $mode)
    {
        $session = service('session');

        // 1. Airlock: Control plane global mode cannot access tenant resources
        if ($session && $session->get('global_mode') === true) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 2. Tenant Context: active_clinic_id is required
        $clinicId = $session ? (int) ($session->get('active_clinic_id') ?? 0) : 0;
        if ($clinicId <= 0) {
            return $this->failTenantContext('TENANT_CONTEXT_REQUIRED');
        }

        // 3. Authenticated Actor
        $ionAuth = new \App\Libraries\IonAuth();
        $userId = (int) ($ionAuth->getUserId() ?? $session->get('user_id') ?? 0);
        if ($userId <= 0) {
            return $this->failUnauthorized('Authentication required.');
        }

        $db = Database::connect();

        // 4. Validate active user account
        $userRow = $db->table('users')->where('id', $userId)->where('active', 1)->get()->getRowArray();
        if (!$userRow) {
            return $this->failUnauthorized('User account is inactive or disabled.');
        }

        // 5. Canonical tenant role check / validated super admin impersonation
        $tenantRoleId = $this->permissionService->getTenantRoleId($userId, $clinicId);
        $isImpersonating = $session->get('is_impersonating') === true;
        $impersonatedClinicId = (int) ($session->get('impersonated_clinic_id') ?? 0);
        $isSuperAdminImpersonating = ($isImpersonating && $impersonatedClinicId === $clinicId && $this->permissionService->isSuperAdmin($userId));

        if ($tenantRoleId === null && !$isSuperAdminImpersonating) {
            return $this->failForbidden('Access denied to this clinic.');
        }

        // 6. Attachment existence & strict clinic scoping
        $attachment = $this->attachmentModel
            ->where('clinic_id', $clinicId)
            ->find($id);

        if (!$attachment) {
            throw new PageNotFoundException('File not found');
        }

        // 7. Object ownership & module permission authorization
        $entityType = strtolower(trim((string) ($attachment['entity_type'] ?? '')));
        $entityId = isset($attachment['entity_id']) ? (int) $attachment['entity_id'] : null;
        $purpose = (string) ($attachment['purpose'] ?? '');

        if (isset(self::ENTITY_MAP[$entityType])) {
            $config = self::ENTITY_MAP[$entityType];
            if ($entityId === null || $entityId <= 0) {
                throw new PageNotFoundException('File not found');
            }

            $query = $db->table($config['table'])
                ->where('id', $entityId)
                ->where('clinic_id', $clinicId);

            if ($config['soft_delete']) {
                $query->where('deleted_at IS NULL');
            }

            $record = $query->get()->getRowArray();
            if (!$record) {
                throw new PageNotFoundException('File not found');
            }

            if ($config['has_patient']) {
                $patientId = (int) ($record['patient_id'] ?? 0);
                if ($patientId <= 0 || !$this->patientExistsInClinic($db, $patientId, $clinicId)) {
                    throw new PageNotFoundException('File not found');
                }
            }

            if (!$this->permissionService->hasPermission($userId, $config['module'], $config['action'])) {
                return $this->failForbidden("You do not have permission to access {$config['module']} files.");
            }
        } elseif ($entityType === 'finance') {
            if ($entityId !== null && $entityId > 0) {
                // Individual finance record attachment: strictly finance:view and deleted_at IS NULL
                $record = $db->table('finances')
                    ->where('id', $entityId)
                    ->where('clinic_id', $clinicId)
                    ->where('deleted_at IS NULL')
                    ->get()
                    ->getRowArray();
                if (!$record) {
                    throw new PageNotFoundException('File not found');
                }
                if (!empty($record['patient_id'])) {
                    $patientId = (int) $record['patient_id'];
                    if (!$this->patientExistsInClinic($db, $patientId, $clinicId)) {
                        throw new PageNotFoundException('File not found');
                    }
                }
                if (!$this->permissionService->hasPermission($userId, 'finance', 'view')) {
                    return $this->failForbidden('You do not have permission to access financial records.');
                }
            } elseif ($entityId === 0) {
                // Finance global export artifact: strictly finance:export (no view shortcut)
                if (!in_array($purpose, self::ALLOWED_FINANCE_EXPORT_PURPOSES, true)) {
                    throw new PageNotFoundException('File not found');
                }
                if (!$this->permissionService->hasPermission($userId, 'finance', 'export')) {
                    return $this->failForbidden('You do not have permission to access financial exports.');
                }
            } else {
                throw new PageNotFoundException('File not found');
            }
        } elseif ($entityType === 'report') {
            // Reports: entity_id must strictly be 0 and purpose must be allowlisted; requires reports:export
            if ($entityId !== 0 || !in_array($purpose, self::ALLOWED_REPORT_PURPOSES, true)) {
                throw new PageNotFoundException('File not found');
            }
            if (!$this->permissionService->hasPermission($userId, 'reports', 'export')) {
                return $this->failForbidden('You do not have permission to access report files.');
            }
        } elseif ($entityType === 'logo') {
            // Logo: prove attachment is current clinic_logo_path referenced by active clinic settings
            $setting = $db->table('settings')
                ->where('clinic_id', $clinicId)
                ->where('setting_key', 'clinic_logo_path')
                ->get()
                ->getRowArray();
            $logoPath = trim((string) ($setting['setting_value'] ?? ''));
            $isReferenced = ($logoPath !== '' && (
                $logoPath === 'file/download/' . $id
                || $logoPath === 'file/inline/' . $id
                || $logoPath === (string) $id
            ));
            if (!$isReferenced) {
                throw new PageNotFoundException('File not found');
            }
        } else {
            // Unknown/unsupported entity family: fail closed
            throw new PageNotFoundException('File not found');
        }

        // 8. Filesystem Confinement
        $filePath = $this->storageService->getAbsolutePath($attachment);
        $realFilePath = realpath($filePath);

        if ($realFilePath === false || !is_file($realFilePath)) {
            throw new PageNotFoundException('Physical file missing');
        }

        $rawClinicDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId;
        if (is_link($rawClinicDir)) {
            log_message('critical', 'Clinic upload directory alias detected for clinic {clinic_id}: {path}', [
                'clinic_id' => $clinicId,
                'path' => $rawClinicDir,
            ]);
            throw new PageNotFoundException('File not found');
        }

        $tenantUploadDir = realpath($rawClinicDir);
        $trustedUploadRoot = realpath(WRITEPATH . 'uploads');
        if ($tenantUploadDir === false || $trustedUploadRoot === false) {
            throw new PageNotFoundException('Physical file missing');
        }

        // Windows junctions are not reliably reported by is_link(). Resolve the
        // trusted root, then require this clinic's own directory identity.
        $expectedClinicDir = $trustedUploadRoot . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId;
        $sameClinicDir = PHP_OS_FAMILY === 'Windows'
            ? strcasecmp($tenantUploadDir, $expectedClinicDir) === 0
            : $tenantUploadDir === $expectedClinicDir;
        if (!$sameClinicDir) {
            throw new PageNotFoundException('File not found');
        }

        $prefix = rtrim($tenantUploadDir, '\\/') . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realFilePath, $prefix)) {
            log_message('critical', 'Filesystem confinement violation attempt for attachment {id}: {path}', [
                'id' => $id,
                'path' => $realFilePath,
            ]);
            throw new PageNotFoundException('File not found');
        }

        return [
            'attachment' => $attachment,
            'real_file_path' => $realFilePath,
            'user_id' => $userId,
            'clinic_id' => $clinicId,
        ];
    }

    private function patientExistsInClinic($db, int $patientId, int $clinicId): bool
    {
        return $db->table('patients')
            ->where('id', $patientId)
            ->where('clinic_id', $clinicId)
            ->where('deleted_at IS NULL')
            ->countAllResults() > 0;
    }

    protected function failTenantContext(string $code)
    {
        return $this->response->setStatusCode(403)->setJSON(['error' => $code]);
    }

    protected function failUnauthorized(string $message)
    {
        if ($this->request->isAJAX() || ($this->request->hasHeader('Accept') && str_contains($this->request->getHeaderLine('Accept'), 'application/json'))) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => [
                    'code' => 'unauthenticated',
                    'message' => $message,
                ],
            ]);
        }
        return redirect()->to('/auth/login');
    }

    protected function failForbidden(string $message)
    {
        if ($this->request->isAJAX() || ($this->request->hasHeader('Accept') && str_contains($this->request->getHeaderLine('Accept'), 'application/json'))) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => [
                    'code' => 'forbidden',
                    'message' => $message,
                ],
            ]);
        }
        return $this->response->setStatusCode(403)->setBody('Forbidden: ' . $message);
    }
}
