<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filters\PermissionFilter;
use App\Libraries\ApiRuntimeContext;
use App\Services\PermissionService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;

final class P0PermissionFilterReviewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        Services::resetSingle('permission');
        parent::tearDown();
    }

    #[DataProvider('validArguments')]
    public function testExactRequestedActionReachesAuthorization(array $arguments, string $action): void
    {
        $spy = $this->permissionSpy();
        $request = service('request');
        $request->getUri()->setPath('api/v1/search/users');
        ApiRuntimeContext::setUserId(42);
        ApiRuntimeContext::setClinicId(84);
        $response = (new PermissionFilter())->before($request, $arguments);
        $this->assertNull($response);
        $this->assertSame([[42, 'users', $action]], $spy->calls);
    }

    public static function validArguments(): array
    {
        return [
            'legacy colon' => [['users:edit'], 'edit'],
            'comma arguments' => [['users', 'edit'], 'edit'],
            'legacy dot' => [['users.edit'], 'edit'],
            'default view' => [['users'], 'view'],
        ];
    }

    #[DataProvider('malformedArguments')]
    public function testMalformedArgumentsDenyBeforeAnAllowingAuthorityCanBypass(?array $arguments): void
    {
        $spy = $this->permissionSpy();
        $request = service('request');
        $request->getUri()->setPath('api/v1/search/users');
        ApiRuntimeContext::setUserId(42);
        ApiRuntimeContext::setClinicId(84);
        $response = (new PermissionFilter())->before($request, $arguments);
        $this->assertNotNull($response, 'Malformed route configuration must deny access.');
        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame([], $spy->calls);
    }

    public static function malformedArguments(): array
    {
        return [
            'missing' => [null], 'empty' => [[]], 'empty module' => [[':edit']],
            'empty action' => [['users:']], 'too many colon segments' => [['users:edit:delete']],
            'conflicting representations' => [['users:edit', 'view']],
            'extra argument' => [['users', 'edit', 'delete']],
            'blank separate action' => [['users', ' ']],
            'mixed delimiters' => [['users:edit.delete']],
        ];
    }

    public function testRegisteredUserUpdateRoutePassesEditThroughTheActualFrameworkFilterPipeline(): void
    {
        $routes = service('routes')->loadRoutes();
        $declaration = null;
        foreach ($routes->getRoutes('POST') as $pattern => $handler) {
            if (!is_string($handler) || !str_contains($handler, 'Users::update/')) {
                continue;
            }
            $options = $routes->getRoutesOptions($pattern, 'POST');
            foreach ((array) ($options['filter'] ?? []) as $filter) {
                if (is_string($filter) && str_starts_with($filter, 'permission:')) {
                    $declaration = $filter;
                }
            }
        }
        $this->assertNotNull($declaration, 'The actual user update route must declare permission.');
        $spy = $this->permissionSpy();
        $request = service('request');
        $request->getUri()->setPath('api/v1/search/users');
        ApiRuntimeContext::setUserId(42);
        ApiRuntimeContext::setClinicId(84);
        $config = new \Config\Filters();
        $config->globals = ['before' => [], 'after' => []];
        $config->required = ['before' => [], 'after' => []];
        $config->filters = [];
        $config->methods = [];
        $pipeline = new \CodeIgniter\Filters\Filters($config, $request, service('response'));
        $pipeline->enableFilters([$declaration])->run('api/v1/search/users');
        $this->assertSame([[42, 'users', 'edit']], $spy->calls,
            'CI4.6 drops trailing :edit before the custom parser: normalize actual route declarations to permission:users,edit.');
    }
    private function permissionSpy(): PermissionService
    {
        $spy = new class extends PermissionService {
            public array $calls = [];
            public function __construct() {}
            public function hasPermission($userId, $module, $action)
            {
                $this->calls[] = [$userId, $module, $action];
                return true;
            }
        };
        Services::injectMock('permission', $spy);
        return $spy;
    }
}