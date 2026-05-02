<?php

namespace App\Controllers;

use App\Models\ProviderPayoutModel;
use App\Models\SupplierModel;
use App\Models\UserModel;

class Providers extends BaseController
{
    protected SupplierModel $supplierModel;
    protected ProviderPayoutModel $providerPayoutModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
        $this->providerPayoutModel = new ProviderPayoutModel();
        $this->userModel = new UserModel();
    }

    private function checkAccess(): bool
    {
        return has_permission('inventory', 'view') || has_permission('finance', 'view');
    }

    public function index(): string
    {
        if (! $this->checkAccess()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->view('providers/index', [
            'title' => lang('Provider.moduleTitle'),
            'pageTitle' => lang('Provider.pageTitle'),
        ]);
    }

    public function getData(): \CodeIgniter\HTTP\Response
    {
        if (! $this->checkAccess()) {
            return $this->response->setStatusCode(403)->setJSON([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        try {
            $clinicId = (int) session()->get('active_clinic_id');
            if ($clinicId <= 0) {
                return $this->response->setStatusCode(422)->setJSON([
                    'draw' => (int) $this->request->getPost('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            $draw = (int) $this->request->getPost('draw');
            $start = (int) $this->request->getPost('start');
            $length = (int) $this->request->getPost('length');
            $search = trim((string) $this->request->getPost('search')['value'] ?? '');
            $typeFilter = trim((string) $this->request->getPost('type'));

            $length = max(1, min($length, 200));

            $supplierRows = $this->supplierModel->getDirectoryDataByClinic($clinicId, $search);
            $providerRows = $this->providerPayoutModel->getDirectoryProvidersByClinic($clinicId, $search);

            $merged = [];

            foreach ($supplierRows as $s) {
                $merged[] = [
                    'id' => (int) $s['id'],
                    'type' => 'supplier',
                    'type_label' => lang('Provider.typeSupplier'),
                    'name' => $s['name'],
                    'contact_person' => $s['contact_person'] ?? '',
                    'phone' => $s['phone'] ?? '',
                    'email' => $s['email'] ?? '',
                    'status' => (int) ($s['is_active'] ?? 1) === 1 ? 'active' : 'inactive',
                    'status_label' => (int) ($s['is_active'] ?? 1) === 1 ? lang('Provider.statusActive') : lang('Provider.statusInactive'),
                    'stats_summary' => '',
                    'created_at' => $s['created_at'] ?? '',
                    'link' => base_url('inventory/suppliers/' . $s['id']),
                    'link_label' => lang('Provider.viewSupplier'),
                    'show_link' => has_permission('inventory', 'view'),
                ];
            }

            foreach ($providerRows as $p) {
                $userId = (int) ($p['provider_user_id'] ?? 0);
                $firstName = $p['first_name'] ?? '';
                $lastName = $p['last_name'] ?? '';
                $snapshot = $p['provider_name_snapshot'] ?? '';

                $resolvedName = trim($firstName . ' ' . $lastName);
                if ($resolvedName === '') {
                    $resolvedName = $snapshot !== '' ? $snapshot : ($userId > 0 ? 'User #' . $userId : lang('Provider.guestProvider'));
                }

                if ($userId > 0 && $p['provider_type'] === 'user') {
                    $link = base_url('users/' . $userId);
                    $linkLabel = lang('Provider.viewProvider');
                    $showLink = has_permission('finance', 'view');
                } else {
                    $link = base_url('finance/payouts');
                    $linkLabel = lang('Provider.viewProvider');
                    $showLink = has_permission('finance', 'view');
                }

                $outstanding = (float) ($p['provider_balance_total'] ?? 0);

                $merged[] = [
                    'id' => $userId > 0 ? $userId : 0,
                    'type' => 'clinical_provider',
                    'type_label' => lang('Provider.typeClinicalProvider'),
                    'name' => $resolvedName,
                    'contact_person' => '',
                    'phone' => '',
                    'email' => '',
                    'status' => $outstanding > 0 ? 'balance_due' : 'settled',
                    'status_label' => number_format($outstanding, 2),
                    'stats_summary' => (int) ($p['payout_count'] ?? 0) . ' ' . ($outstanding > 0 ? '' : ''),
                    'created_at' => $p['latest_payout_date'] ?? '',
                    'link' => $link,
                    'link_label' => $linkLabel,
                    'show_link' => $showLink,
                ];
            }

            if ($typeFilter !== '' && in_array($typeFilter, ['supplier', 'clinical_provider'], true)) {
                $merged = array_values(array_filter($merged, fn($row) => $row['type'] === $typeFilter));
            }

            usort($merged, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            $recordsFiltered = count($merged);
            $pageData = array_slice($merged, $start, $length);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsFiltered,
                'recordsFiltered' => $recordsFiltered,
                'data' => $pageData,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Providers::getData error: ' . $e->getMessage());

            return $this->response->setJSON([
                'draw' => (int) $this->request->getPost('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }
    }

    public function getStatistics(): \CodeIgniter\HTTP\Response
    {
        if (! $this->checkAccess()) {
            return $this->response->setStatusCode(403)->setJSON([]);
        }

        try {
            $clinicId = (int) session()->get('active_clinic_id');
            if ($clinicId <= 0) {
                return $this->response->setJSON([]);
            }

            $supplierStats = $this->supplierModel->getDirectoryStatsByClinic($clinicId);
            $providerStats = $this->providerPayoutModel->getDirectoryProviderStats($clinicId);

            return $this->response->setJSON([
                'total_suppliers' => $supplierStats['total'],
                'active_suppliers' => $supplierStats['active'],
                'total_providers' => $providerStats['total'],
                'outstanding_balance' => $providerStats['outstanding_balance'],
                'total_combined' => $supplierStats['total'] + $providerStats['total'],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Providers::getStatistics error: ' . $e->getMessage());

            return $this->response->setJSON([
                'total_suppliers' => 0,
                'active_suppliers' => 0,
                'total_providers' => 0,
                'outstanding_balance' => 0,
                'total_combined' => 0,
            ]);
        }
    }
}
