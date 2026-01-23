# CP-UX-00 Verification

## Scope
Inventory of control plane views and routes; confirm unified layout usage.

## Evidence

### Control plane views extend main_control_plane
Command:
`rg -n "control_plane|main_control_plane|extend\('layouts/" app\\Views\\control_plane`

Output:
```
app\\Views\\control_plane\\clinics.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\settings.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\plan_form.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\onboarding\\create_clinic.php:2:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\plans.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\entry.php:2:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\operations.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\dashboard.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\clinic_detail.php:1:<?= $this->extend('layouts/main_control_plane') ?>
```

### Control plane routes inventory
Command:
`rg -n "controlplane" app\\Config\\Routes.php`

Output:
```
app\\Config\\Routes.php:14:$routes->group('controlplane', function ($routes) {
app\\Config\\Routes.php:15:    $routes->get('/', 'ControlPlane\\Entry::index'); // P5-18c
app\\Config\\Routes.php:16:    $routes->post('enter', 'ControlPlane::enter');
app\\Config\\Routes.php:20:    $routes->group('controlplane', ['filter' => 'controlplane'], function ($routes) {
app\\Config\\Routes.php:21:        $routes->get('dashboard', 'ControlPlane\\Dashboard::index'); // P5-11-UX
app\\Config\\Routes.php:22:        $routes->get('clinics', 'ControlPlane\\Clinics::index'); // CP-UX-20
app\\Config\\Routes.php:23:        $routes->get('clinics/(:num)', 'ControlPlane\\Clinics::show/$1'); // CP-UX-20 detail
app\\Config\\Routes.php:24:        $routes->post('clinics/(:num)/subscription/start', 'ControlPlane\\Clinics::startSubscription/$1');
app\\Config\\Routes.php:25:        $routes->post('clinics/(:num)/subscription/extend', 'ControlPlane\\Clinics::extendSubscription/$1');
app\\Config\\Routes.php:26:        $routes->post('clinics/(:num)/subscription/cancel', 'ControlPlane\\Clinics::cancelSubscription/$1');
app\\Config\\Routes.php:27:        $routes->post('clinics/(:num)/subscription/update-dates', 'ControlPlane\\Clinics::updateSubscriptionDates/$1');
app\\Config\\Routes.php:28:        $routes->post('clinics/(:num)/terminate', 'ControlPlane\\Clinics::terminate/$1');
app\\Config\\Routes.php:29:        $routes->post('clinics/(:num)/suspend', 'ControlPlane\\Clinics::suspend/$1');
app\\Config\\Routes.php:30:        $routes->post('clinics/(:num)/reactivate', 'ControlPlane\\Clinics::reactivate/$1');
app\\Config\\Routes.php:31:        $routes->get('plans', 'ControlPlane\\Plans::index'); // CP-UX-22
app\\Config\\Routes.php:32:        $routes->get('plans/create', 'ControlPlane\\Plans::create'); // CP-UX-23
app\\Config\\Routes.php:33:        $routes->post('plans', 'ControlPlane\\Plans::store'); // CP-UX-23
app\\Config\\Routes.php:34:        $routes->get('plans/(:num)/edit', 'ControlPlane\\Plans::edit/$1');
app\\Config\\Routes.php:35:        $routes->post('plans/(:num)', 'ControlPlane\\Plans::update/$1');
app\\Config\\Routes.php:36:        $routes->post('plans/(:num)/toggle', 'ControlPlane\\Plans::toggle/$1');
app\\Config\\Routes.php:37:        $routes->post('plan-requests', 'ControlPlane\\PlanRequests::store'); // CP-UX-24
app\\Config\\Routes.php:38:        $routes->post('plan-requests/(:num)/approve', 'ControlPlane\\PlanRequests::approve/$1');
app\\Config\\Routes.php:39:        $routes->post('plan-requests/(:num)/reject', 'ControlPlane\\PlanRequests::reject/$1');
app\\Config\\Routes.php:40:        $routes->get('operations', 'ControlPlane\\Operations::index'); // P5-19
app\\Config\\Routes.php:41:        $routes->get('settings', 'ControlPlane\\Settings::index'); // P5-19
app\\Config\\Routes.php:42:        $routes->post('exit', 'ControlPlane::exit');
app\\Config\\Routes.php:43:        $routes->post('settings/email-templates', 'ControlPlane\\Settings::storeEmailTemplate');
app\\Config\\Routes.php:44:        $routes->post('settings/email-templates/(:num)', 'ControlPlane\\Settings::updateEmailTemplate/$1');
app\\Config\\Routes.php:48:        $routes->get('clinic/create', 'ControlPlane\\Onboarding::createClinic');
app\\Config\\Routes.php:49:        $routes->post('clinic/create', 'ControlPlane\\Onboarding::processCreateClinic');
app\\Config\\Routes.php:473:$routes->group('rbac', ['filter' => 'controlplane'], function ($routes) {
```
