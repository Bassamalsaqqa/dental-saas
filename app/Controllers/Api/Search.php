<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\V1\Search as V1Search;

/**
 * First-Party Session Search Controller
 *
 * Provides session-authenticated search endpoints for first-party UI controls
 * (such as Select2 dropdowns) by narrowly reusing Api\V1\Search queries.
 */
class Search extends V1Search
{
    /**
     * Resolve session clinic ID for first-party search.
     */
    protected function getClinicIdOrError()
    {
        $session = service('session');
        $clinicId = (int) ($session ? ($session->get('active_clinic_id') ?? 0) : 0);
        if ($clinicId <= 0) {
            return [null, $this->response->setStatusCode(403)->setJSON([
                'error' => [
                    'code' => 'tenant_context_forbidden',
                    'message' => 'Tenant context required.',
                ],
            ])];
        }

        return [$clinicId, null];
    }
}
