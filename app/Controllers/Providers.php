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
            $searchParam = $this->request->getPost('search');
            $search = is_array($searchParam) ? trim((string) ($searchParam['value'] ?? '')) : '';
            $typeFilter = trim((string) $this->request->getPost('type'));

            $length = max(1, min($length, 200));

            $allSupplierRows = $this->supplierModel->getDirectoryDataByClinic($clinicId);
            $allProviderRows = $this->providerPayoutModel->getDirectoryProvidersByClinic($clinicId);
            $supplierRows = $search === '' ? $allSupplierRows : $this->supplierModel->getDirectoryDataByClinic($clinicId, $search);
            $providerRows = $search === '' ? $allProviderRows : $this->providerPayoutModel->getDirectoryProvidersByClinic($clinicId, $search);

            $allMerged = $this->buildDirectoryRows($allSupplierRows, $allProviderRows);
            $merged = $search === '' ? $allMerged : $this->buildDirectoryRows($supplierRows, $providerRows);

            $totalByPermission = $this->filterRowsByTypePermission($allMerged, '');
            if ($typeFilter !== '' && in_array($typeFilter, ['supplier', 'clinical_provider'], true)) {
                $merged = array_values(array_filter($merged, fn($row) => $row['type'] === $typeFilter));
                $totalByPermission = array_values(array_filter($totalByPermission, fn($row) => $row['type'] === $typeFilter));
            }

            $merged = $this->filterRowsByTypePermission($merged, $typeFilter);
            usort($merged, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            usort($totalByPermission, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            $recordsFiltered = count($merged);
            $pageData = array_slice($merged, $start, $length);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => count($totalByPermission),
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

    private function buildDirectoryRows(array $supplierRows, array $providerRows): array
    {
        $merged = [];

        foreach ($supplierRows as $s) {
            $contact = $this->bestContact($s['whatsapp'] ?? '', $s['phone'] ?? '', $s['email'] ?? '');
            $itemCount = (int) ($s['item_count'] ?? 0);
            $billCount = (int) ($s['bill_count'] ?? 0);
            $openBalance = (float) ($s['open_balance'] ?? 0);

            $merged[] = [
                'id' => (int) $s['id'],
                'type' => 'supplier',
                'type_label' => lang('Provider.typeSupplier'),
                'name' => (string) ($s['name'] ?? ''),
                'contact' => $contact,
                'activity_count' => $itemCount + $billCount,
                'activity_label' => lang('Provider.supplierActivity', ['items' => $itemCount, 'bills' => $billCount]),
                'outstanding' => $openBalance,
                'outstanding_label' => number_format($openBalance, 2),
                'status' => (int) ($s['is_active'] ?? 1) === 1 ? 'active' : 'inactive',
                'status_label' => (int) ($s['is_active'] ?? 1) === 1 ? lang('Provider.statusActive') : lang('Provider.statusInactive'),
                'last_activity' => $s['last_activity'] ?: ($s['created_at'] ?? ''),
                'link' => base_url('inventory/suppliers/' . (int) $s['id']),
                'link_label' => lang('Provider.viewSupplier'),
                'show_link' => has_permission('inventory', 'view'),
            ];
        }

        foreach ($providerRows as $p) {
            $userId = (int) ($p['provider_user_id'] ?? 0);
            $providerType = (string) ($p['provider_type'] ?? 'guest');
            $resolvedName = trim((string) ($p['first_name'] ?? '') . ' ' . (string) ($p['last_name'] ?? ''));
            $snapshot = trim((string) ($p['provider_name_snapshot'] ?? ''));
            if ($resolvedName === '') {
                $resolvedName = $snapshot !== '' ? $snapshot : ($userId > 0 ? 'User #' . $userId : lang('Provider.guestProvider'));
            }

            $outstanding = (float) ($p['provider_balance_total'] ?? 0);
            $isUserProvider = $userId > 0 && $providerType === 'user';

            $merged[] = [
                'id' => $userId > 0 ? $userId : 0,
                'type' => 'clinical_provider',
                'type_label' => $isUserProvider ? lang('Provider.typeClinicalProvider') : lang('Provider.typeGuestProvider'),
                'name' => $resolvedName,
                'contact' => $this->bestContact($p['phone'] ?? '', '', $p['email'] ?? ''),
                'activity_count' => (int) ($p['payout_count'] ?? 0),
                'activity_label' => lang('Provider.providerActivity', ['cases' => (int) ($p['payout_count'] ?? 0)]),
                'outstanding' => $outstanding,
                'outstanding_label' => number_format($outstanding, 2),
                'status' => $outstanding > 0 ? 'balance_due' : 'settled',
                'status_label' => $outstanding > 0 ? lang('Provider.statusDue') : lang('Provider.statusSettled'),
                'last_activity' => $p['latest_payout_date'] ?? '',
                'link' => $isUserProvider ? base_url('users/' . $userId) : base_url('finance/payouts'),
                'link_label' => $isUserProvider ? lang('Provider.viewProvider') : lang('Provider.viewPayouts'),
                'show_link' => has_permission('finance', 'view'),
            ];
        }

        return $merged;
    }

    private function filterRowsByTypePermission(array $rows, string $typeFilter): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($typeFilter): bool {
            if ($typeFilter !== '' && ($row['type'] ?? '') !== $typeFilter) {
                return false;
            }

            if (($row['type'] ?? '') === 'supplier') {
                return has_permission('inventory', 'view');
            }

            if (($row['type'] ?? '') === 'clinical_provider') {
                return has_permission('finance', 'view');
            }

            return false;
        }));
    }

    private function bestContact(?string $whatsapp, ?string $phone, ?string $email): string
    {
        $whatsapp = trim((string) $whatsapp);
        $phone = trim((string) $phone);
        $email = trim((string) $email);

        if ($whatsapp !== '') {
            return $whatsapp;
        }

        if ($phone !== '') {
            return $phone;
        }

        return $email;
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
            $canViewInventory = has_permission('inventory', 'view');
            $canViewFinance = has_permission('finance', 'view');

            return $this->response->setJSON([
                'total_suppliers' => $canViewInventory ? $supplierStats['total'] : 0,
                'active_suppliers' => $canViewInventory ? $supplierStats['active'] : 0,
                'total_providers' => $canViewFinance ? $providerStats['total'] : 0,
                'outstanding_balance' => $canViewFinance ? $providerStats['outstanding_balance'] : 0,
                'total_combined' => ($canViewInventory ? $supplierStats['total'] : 0) + ($canViewFinance ? $providerStats['total'] : 0),
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
