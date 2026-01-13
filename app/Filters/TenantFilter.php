<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Tenant Filter
 * 
 * Enforces tenant context for Tenant Plane routes.
 * 
 * S1-03 Implementation: Placeholder/Pass-through.
 * Phase 2 Implementation: Will enforce active_clinic_id and membership.
 */
class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Phase 2 Logic will go here.
        // For now, allow request to proceed to satisfy S1-03 route structure.
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
