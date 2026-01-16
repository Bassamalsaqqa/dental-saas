<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --------------------------------------------------------------------
// Control Plane Entry/Exit (SaaS)
// --------------------------------------------------------------------
$routes->group('controlplane', function ($routes) {
    $routes->get('/', 'ControlPlane\Entry::index'); // P5-18c
    $routes->get('dashboard', 'ControlPlane\Dashboard::index', ['filter' => 'controlplane']); // P5-11-UX
    $routes->get('console', 'ControlPlane\Console::index', ['filter' => 'controlplane']); // P5-16
    $routes->get('operations', 'ControlPlane\Operations::index', ['filter' => 'controlplane']); // P5-19
    $routes->get('settings', 'ControlPlane\Settings::index', ['filter' => 'controlplane']); // P5-19
    $routes->post('enter', 'ControlPlane::enter');
    
    // Danger Zone (P5-17)
    $routes->group('danger', ['filter' => 'controlplane'], function($routes) {
        $routes->get('/', 'ControlPlane\Danger::index');
        $routes->post('exit', 'ControlPlane\Danger::exitGlobalMode');
    });
    
    // Onboarding (P5-11)
    $routes->group('onboarding', ['filter' => 'controlplane'], function($routes) {
        $routes->get('clinic/create', 'ControlPlane\Onboarding::createClinic');
        $routes->post('clinic/create', 'ControlPlane\Onboarding::processCreateClinic');
    });
});

// --------------------------------------------------------------------
// Authentication routes
// --------------------------------------------------------------------
$routes->group('auth', function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::login');
    $routes->get('logout', 'Auth::logout');
    $routes->post('logout', 'Auth::logout');
    $routes->get('forgot-password', 'Auth::forgot_password');
    $routes->post('forgot-password', 'Auth::forgot_password');
    $routes->get('reset-password/(:any)', 'Auth::reset_password/$1');
    $routes->post('reset-password/(:any)', 'Auth::reset_password/$1');
    $routes->get('change-password', 'Auth::change_password');
    $routes->post('change-password', 'Auth::change_password');
    $routes->get('activate/(:num)/(:any)', 'Auth::activate/$1/$2');
    $routes->get('activate/(:num)', 'Auth::activate/$1');
    $routes->get('deactivate/(:num)', 'Auth::deactivate/$1');
    $routes->post('deactivate/(:num)', 'Auth::deactivate/$1');
    $routes->get('create-user', 'Auth::create_user');
    $routes->post('create-user', 'Auth::create_user');
    $routes->get('edit-user/(:num)', 'Auth::edit_user/$1');
    $routes->post('edit-user/(:num)', 'Auth::edit_user/$1');
    $routes->get('create-group', 'Auth::create_group');
    $routes->post('create-group', 'Auth::create_group');
    $routes->get('edit-group/(:num)', 'Auth::edit_group/$1');
    $routes->post('edit-group/(:num)', 'Auth::edit_group/$1');
});

// Logout route (outside auth group for easy access)
$routes->get('logout', 'Auth::logout');
$routes->post('logout', 'Auth::logout');

// Redirect root to dashboard (Tenant Plane)
$routes->get('/', 'Dashboard::index', ['filter' => ['auth', 'tenant']]);

// --------------------------------------------------------------------
// Clinic Selection Wall (Auth Only)
// --------------------------------------------------------------------
$routes->group('clinic', ['filter' => 'auth'], function($routes) {
    $routes->get('select', 'ClinicSelector::select');
    $routes->post('select', 'ClinicSelector::processSelect');
    $routes->get('no-clinic', 'ClinicSelector::noClinic');
    $routes->post('switch', 'ClinicSelector::switchClinic');
    $routes->get('switch/(:num)', 'ClinicSelector::switch/$1');
});

// --------------------------------------------------------------------
// Tenant Plane Routes
// --------------------------------------------------------------------

// Dashboard routes
$routes->group('dashboard', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Dashboard::index', ['filter' => 'permission:dashboard:read']);
    $routes->get('stats', 'Dashboard::getStats', ['filter' => 'permission:dashboard:read']);
    $routes->get('chart-data', 'Dashboard::getChartData', ['filter' => 'permission:dashboard:read']);
});

// Patient routes
$routes->group('patient', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Patient::index', ['filter' => 'permission:patients:view']);
    $routes->get('create', 'Patient::create', ['filter' => 'permission:patients:create']);
    $routes->post('store', 'Patient::store', ['filter' => 'permission:patients:create']);
    $routes->get('search', 'Patient::search', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)', 'Patient::show/$1', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/edit', 'Patient::edit/$1', ['filter' => 'permission:patients:edit']);
    $routes->post('(:num)/update', 'Patient::update/$1', ['filter' => 'permission:patients:edit']);
    $routes->delete('(:num)', 'Patient::delete/$1', ['filter' => 'permission:patients:delete']);
    $routes->get('(:num)/data', 'Patient::getPatientData/$1', ['filter' => 'permission:patients:view']);
    $routes->get('get-data', 'Patient::getData', ['filter' => 'permission:patients:view']);
    $routes->post('get-data', 'Patient::getData', ['filter' => 'permission:patients:view']);
    $routes->get('get-statistics', 'Patient::getStatistics', ['filter' => 'permission:patients:view']);
});


// Patient routes (plural alias)
$routes->group('patients', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Patient::index', ['filter' => 'permission:patients:view']);
    $routes->get('create', 'Patient::create', ['filter' => 'permission:patients:create']);
    $routes->post('store', 'Patient::store', ['filter' => 'permission:patients:create']);
    $routes->get('search', 'Patient::search', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)', 'Patient::show/$1', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/edit', 'Patient::edit/$1', ['filter' => 'permission:patients:edit']);
    $routes->post('(:num)/update', 'Patient::update/$1', ['filter' => 'permission:patients:edit']);
    $routes->delete('(:num)', 'Patient::delete/$1', ['filter' => 'permission:patients:delete']);
    $routes->get('(:num)/data', 'Patient::getPatientData/$1', ['filter' => 'permission:patients:view']);
    $routes->get('get-data', 'Patient::getData', ['filter' => 'permission:patients:view']);
    $routes->post('get-data', 'Patient::getData', ['filter' => 'permission:patients:view']);
    $routes->get('get-statistics', 'Patient::getStatistics', ['filter' => 'permission:patients:view']);
});

// Examination routes
$routes->group('examination', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Examination::index', ['filter' => 'permission:examinations:view']);
    $routes->get('create', 'Examination::create', ['filter' => 'permission:examinations:create']);
    $routes->post('store', 'Examination::store', ['filter' => 'permission:examinations:create']);
    $routes->get('calendar', 'Examination::calendar', ['filter' => 'permission:examinations:view']);
    $routes->get('calendar-events', 'Examination::getCalendarEvents', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)', 'Examination::show/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)/edit', 'Examination::edit/$1', ['filter' => 'permission:examinations:edit']);
    $routes->post('(:num)/update', 'Examination::update/$1', ['filter' => 'permission:examinations:edit']);
    $routes->post('(:num)/complete', 'Examination::complete/$1', ['filter' => 'permission:examinations:edit']);
    $routes->get('(:num)/print', 'Examination::print/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)/duplicate', 'Examination::duplicate/$1', ['filter' => 'permission:examinations:create']);
    $routes->delete('(:num)/delete', 'Examination::delete/$1', ['filter' => 'permission:examinations:delete']);
    $routes->post('update-tooth-condition', 'Examination::updateToothCondition', ['filter' => 'permission:examinations:edit']);
    $routes->get('(:num)/data', 'Examination::getExaminationData/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('by-date', 'Examination::getExaminationsByDate', ['filter' => 'permission:examinations:view']);
    $routes->post('getExaminationsData', 'Examination::getExaminationsData', ['filter' => 'permission:examinations:view']);
    $routes->get('getExaminationsData', 'Examination::getExaminationsData', ['filter' => 'permission:examinations:view']);
    $routes->get('getExaminationStats', 'Examination::getExaminationStats', ['filter' => 'permission:examinations:view']);
});

// Examination routes (plural alias)
$routes->group('examinations', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Examination::index', ['filter' => 'permission:examinations:view']);
    $routes->get('create', 'Examination::create', ['filter' => 'permission:examinations:create']);
    $routes->post('store', 'Examination::store', ['filter' => 'permission:examinations:create']);
    $routes->get('calendar', 'Examination::calendar', ['filter' => 'permission:examinations:view']);
    $routes->get('calendar-events', 'Examination::getCalendarEvents', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)', 'Examination::show/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)/edit', 'Examination::edit/$1', ['filter' => 'permission:examinations:edit']);
    $routes->post('(:num)/update', 'Examination::update/$1', ['filter' => 'permission:examinations:edit']);
    $routes->post('(:num)/complete', 'Examination::complete/$1', ['filter' => 'permission:examinations:edit']);
    $routes->get('(:num)/print', 'Examination::print/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('(:num)/duplicate', 'Examination::duplicate/$1', ['filter' => 'permission:examinations:create']);
    $routes->delete('(:num)/delete', 'Examination::delete/$1', ['filter' => 'permission:examinations:delete']);
    $routes->post('update-tooth-condition', 'Examination::updateToothCondition', ['filter' => 'permission:examinations:edit']);
    $routes->get('(:num)/data', 'Examination::getExaminationData/$1', ['filter' => 'permission:examinations:view']);
    $routes->get('by-date', 'Examination::getExaminationsByDate', ['filter' => 'permission:examinations:view']);
    $routes->post('getExaminationsData', 'Examination::getExaminationsData', ['filter' => 'permission:examinations:view']);
    $routes->get('getExaminationsData', 'Examination::getExaminationsData', ['filter' => 'permission:examinations:view']);
    $routes->get('getExaminationStats', 'Examination::getExaminationStats', ['filter' => 'permission:examinations:view']);
});

// Odontogram routes
$routes->group('odontogram', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Odontogram::list', ['filter' => 'permission:patients:view']);
    $routes->post('get-patients-data', 'Odontogram::getPatientsData', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)', 'Odontogram::index/$1', ['filter' => 'permission:patients:view']);
    $routes->post('update-tooth', 'Odontogram::updateTooth', ['filter' => 'permission:patients:edit']);
    $routes->get('tooth-condition', 'Odontogram::getToothCondition', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/data', 'Odontogram::getOdontogramData/$1', ['filter' => 'permission:patients:view']);
    $routes->post('reset-tooth', 'Odontogram::resetTooth', ['filter' => 'permission:patients:edit']);
    $routes->get('(:num)/export', 'Odontogram::export/$1', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/print', 'Odontogram::print/$1', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/pdf', 'Odontogram::pdf/$1', ['filter' => 'permission:patients:view']);
    $routes->get('(:num)/download-pdf', 'Odontogram::downloadPdf/$1', ['filter' => 'permission:patients:view']);
});

// Finance routes
$routes->group('finance', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Finance::index', ['filter' => 'permission:finance:view']);
    $routes->get('create', 'Finance::create', ['filter' => 'permission:finance:create']);
    $routes->post('store', 'Finance::store', ['filter' => 'permission:finance:create']);
    $routes->get('reports', 'Finance::reports', ['filter' => 'permission:finance:reports']);
    $routes->get('(:num)', 'Finance::show/$1', ['filter' => 'permission:finance:view']);
    $routes->get('(:num)/edit', 'Finance::edit/$1', ['filter' => 'permission:finance:edit']);
    $routes->post('(:num)/update', 'Finance::update/$1', ['filter' => 'permission:finance:edit']);
    $routes->delete('(:num)', 'Finance::delete/$1', ['filter' => 'permission:finance:delete']);
    $routes->post('(:num)/mark-paid', 'Finance::markAsPaid/$1', ['filter' => 'permission:finance:edit']);
    $routes->get('(:num)/invoice', 'Finance::generateInvoice/$1', ['filter' => 'permission:finance:view']);
    $routes->get('stats', 'Finance::getFinanceStats', ['filter' => 'permission:finance:view']);
    $routes->get('monthly-revenue', 'Finance::getMonthlyRevenue', ['filter' => 'permission:finance:view']);
    $routes->post('getFinancesData', 'Finance::getFinancesData', ['filter' => 'permission:finance:view']);
    $routes->get('getFinancesData', 'Finance::getFinancesData', ['filter' => 'permission:finance:view']);
    $routes->get('export', 'Finance::export', ['filter' => 'permission:finance:export']);
    $routes->post('bulk-mark-paid', 'Finance::bulkMarkAsPaid', ['filter' => 'permission:finance:edit']);
    $routes->post('bulk-delete', 'Finance::bulkDelete', ['filter' => 'permission:finance:delete']);
});

// Appointment routes
$routes->group('appointment', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Appointment::index', ['filter' => 'permission:appointments:view']);
    $routes->get('create', 'Appointment::create', ['filter' => 'permission:appointments:create']);
    $routes->post('store', 'Appointment::store', ['filter' => 'permission:appointments:create']);
    $routes->get('calendar', 'Appointment::calendar', ['filter' => 'permission:appointments:calendar']);
    $routes->get('(:num)', 'Appointment::show/$1', ['filter' => 'permission:appointments:view']);
    $routes->get('(:num)/edit', 'Appointment::edit/$1', ['filter' => 'permission:appointments:edit']);
    $routes->get('(:num)/print', 'Appointment::print/$1', ['filter' => 'permission:appointments:view']);
    $routes->post('(:num)/update', 'Appointment::update/$1', ['filter' => 'permission:appointments:edit']);
    $routes->delete('(:num)', 'Appointment::delete/$1', ['filter' => 'permission:appointments:delete']);
    $routes->post('(:num)/confirm', 'Appointment::confirm/$1', ['filter' => 'permission:appointments:edit']);
    $routes->post('(:num)/complete', 'Appointment::complete/$1', ['filter' => 'permission:appointments:edit']);
    $routes->post('(:num)/cancel', 'Appointment::cancel/$1', ['filter' => 'permission:appointments:edit']);
    $routes->get('available-time-slots', 'Appointment::getAvailableTimeSlots', ['filter' => 'permission:appointments:view']);
    $routes->get('calendar-events', 'Appointment::getCalendarEvents', ['filter' => 'permission:appointments:view']);
});

// Appointment routes (plural alias)
$routes->group('appointments', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Appointment::index', ['filter' => 'permission:appointments:view']);
    $routes->get('create', 'Appointment::create', ['filter' => 'permission:appointments:create']);
    $routes->post('store', 'Appointment::store', ['filter' => 'permission:appointments:create']);
    $routes->get('calendar', 'Appointment::calendar', ['filter' => 'permission:appointments:calendar']);
    $routes->get('(:num)', 'Appointment::show/$1', ['filter' => 'permission:appointments:view']);
    $routes->get('(:num)/edit', 'Appointment::edit/$1', ['filter' => 'permission:appointments:edit']);
    $routes->post('(:num)/update', 'Appointment::update/$1', ['filter' => 'permission:appointments:edit']);
    $routes->delete('(:num)', 'Appointment::delete/$1', ['filter' => 'permission:appointments:delete']);
    $routes->post('(:num)/confirm', 'Appointment::confirm/$1', ['filter' => 'permission:appointments:edit']);
    $routes->post('(:num)/complete', 'Appointment::complete/$1', ['filter' => 'permission:appointments:edit']);
    $routes->post('(:num)/cancel', 'Appointment::cancel/$1', ['filter' => 'permission:appointments:edit']);
    $routes->get('available-time-slots', 'Appointment::getAvailableTimeSlots', ['filter' => 'permission:appointments:view']);
    $routes->get('calendar-events', 'Appointment::getCalendarEvents', ['filter' => 'permission:appointments:view']);
});

// Treatment routes
$routes->group('treatment', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Treatment::index', ['filter' => 'permission:treatments:view']);
    $routes->get('create', 'Treatment::create', ['filter' => 'permission:treatments:create']);
    $routes->post('store', 'Treatment::store', ['filter' => 'permission:treatments:create']);
    $routes->get('(:num)', 'Treatment::show/$1', ['filter' => 'permission:treatments:view']);
    $routes->get('(:num)/edit', 'Treatment::edit/$1', ['filter' => 'permission:treatments:edit']);
    $routes->post('(:num)/update', 'Treatment::update/$1', ['filter' => 'permission:treatments:edit']);
    $routes->delete('(:num)', 'Treatment::delete/$1', ['filter' => 'permission:treatments:delete']);
    $routes->post('(:num)/complete', 'Treatment::complete/$1', ['filter' => 'permission:treatments:edit']);
    $routes->get('stats', 'Treatment::getTreatmentStats', ['filter' => 'permission:treatments:view']);
    $routes->get('get-treatments', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->post('get-treatments', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->post('getTreatmentsData', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->get('getTreatmentsData', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
});

// Treatment routes (plural alias)
$routes->group('treatments', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Treatment::index', ['filter' => 'permission:treatments:view']);
    $routes->get('create', 'Treatment::create', ['filter' => 'permission:treatments:create']);
    $routes->post('store', 'Treatment::store', ['filter' => 'permission:treatments:create']);
    $routes->get('(:num)', 'Treatment::show/$1', ['filter' => 'permission:treatments:view']);
    $routes->get('(:num)/edit', 'Treatment::edit/$1', ['filter' => 'permission:treatments:edit']);
    $routes->post('(:num)/update', 'Treatment::update/$1', ['filter' => 'permission:treatments:edit']);
    $routes->delete('(:num)', 'Treatment::delete/$1', ['filter' => 'permission:treatments:delete']);
    $routes->post('(:num)/complete', 'Treatment::complete/$1', ['filter' => 'permission:treatments:edit']);
    $routes->get('stats', 'Treatment::getTreatmentStats', ['filter' => 'permission:treatments:view']);
    $routes->get('get-treatments', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->post('get-treatments', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->post('getTreatmentsData', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
    $routes->get('getTreatmentsData', 'Treatment::getTreatmentsData', ['filter' => 'permission:treatments:view']);
});

// Prescription routes
$routes->group('prescription', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Prescription::index', ['filter' => 'permission:prescriptions:view']);
    $routes->get('create', 'Prescription::create', ['filter' => 'permission:prescriptions:create']);
    $routes->post('store', 'Prescription::store', ['filter' => 'permission:prescriptions:create']);
    $routes->get('stats', 'Prescription::getPrescriptionStats', ['filter' => 'permission:prescriptions:view']);
    $routes->get('get-prescriptions', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
    $routes->post('get-prescriptions', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
    $routes->post('remove/(:num)', 'Prescription::delete/$1', ['filter' => 'permission:prescriptions:delete']);
    $routes->get('(:num)/print', 'Prescription::print/$1', ['filter' => 'permission:prescriptions:print']);
    $routes->get('(:num)/edit', 'Prescription::edit/$1', ['filter' => 'permission:prescriptions:edit']);
    $routes->post('(:num)/update', 'Prescription::update/$1', ['filter' => 'permission:prescriptions:edit']);
    $routes->delete('(:num)', 'Prescription::delete/$1', ['filter' => 'permission:prescriptions:delete']);
    $routes->get('(:num)', 'Prescription::show/$1', ['filter' => 'permission:prescriptions:view']);
    $routes->post('getPrescriptionsData', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
    $routes->get('getPrescriptionsData', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
});

// Prescription routes (plural alias)
$routes->group('prescriptions', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Prescription::index', ['filter' => 'permission:prescriptions:view']);
    $routes->get('create', 'Prescription::create', ['filter' => 'permission:prescriptions:create']);
    $routes->post('store', 'Prescription::store', ['filter' => 'permission:prescriptions:create']);
    $routes->get('(:num)', 'Prescription::show/$1', ['filter' => 'permission:prescriptions:view']);
    $routes->get('(:num)/edit', 'Prescription::edit/$1', ['filter' => 'permission:prescriptions:edit']);
    $routes->post('(:num)/update', 'Prescription::update/$1', ['filter' => 'permission:prescriptions:edit']);
    $routes->delete('(:num)', 'Prescription::delete/$1', ['filter' => 'permission:prescriptions:delete']);
    $routes->get('(:num)/print', 'Prescription::print/$1', ['filter' => 'permission:prescriptions:print']);
    $routes->get('stats', 'Prescription::getPrescriptionStats', ['filter' => 'permission:prescriptions:view']);
    $routes->get('get-prescriptions', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
    $routes->post('get-prescriptions', 'Prescription::getPrescriptionsData', ['filter' => 'permission:prescriptions:view']);
});

// Reports routes
$routes->group('reports', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Reports::index', ['filter' => 'permission:reports:view']);
    $routes->get('chart-data', 'Reports::getChartData', ['filter' => 'permission:reports:view']);
    $routes->get('export', 'Reports::export', ['filter' => 'permission:reports:export']);
});

// Inventory routes
$routes->group('inventory', ['filter' => ['auth', 'tenant']], function ($routes) {
    // Specific routes first (before generic (:num) routes)
    $routes->get('usage', 'Inventory::usage', ['filter' => 'permission:inventory:view']);
    $routes->post('record-usage', 'Inventory::recordUsage', ['filter' => 'permission:inventory:edit']);
    $routes->get('usage-history', 'Inventory::usageHistory', ['filter' => 'permission:inventory:view']);
    $routes->get('usage-details/(:num)', 'Inventory::usageDetails/$1', ['filter' => 'permission:inventory:view']);
    $routes->get('usage-print/(:num)', 'Inventory::usagePrint/$1', ['filter' => 'permission:inventory:view']);

    // Server-side processing routes
    $routes->post('getInventoryData', 'Inventory::getInventoryData', ['filter' => 'permission:inventory:view']);
    $routes->get('getInventoryData', 'Inventory::getInventoryData', ['filter' => 'permission:inventory:view']);
    $routes->post('getInventoryStats', 'Inventory::getInventoryStats', ['filter' => 'permission:inventory:view']);
    $routes->get('getInventoryStats', 'Inventory::getInventoryStats', ['filter' => 'permission:inventory:view']);
    $routes->post('getUsageHistoryData', 'Inventory::getUsageHistoryData', ['filter' => 'permission:inventory:view']);
    $routes->get('getUsageHistoryData', 'Inventory::getUsageHistoryData', ['filter' => 'permission:inventory:view']);
    $routes->post('getLowStockData', 'Inventory::getLowStockData', ['filter' => 'permission:inventory:view']);
    $routes->get('getLowStockData', 'Inventory::getLowStockData', ['filter' => 'permission:inventory:view']);
    $routes->get('low-stock', 'Inventory::lowStock', ['filter' => 'permission:inventory:view']);
    $routes->get('expired', 'Inventory::expired', ['filter' => 'permission:inventory:view']);
    $routes->get('stats', 'Inventory::getInventoryStats', ['filter' => 'permission:inventory:view']);

    // Generic routes last
    $routes->get('/', 'Inventory::index', ['filter' => 'permission:inventory:view']);
    $routes->get('create', 'Inventory::create', ['filter' => 'permission:inventory:create']);
    $routes->post('store', 'Inventory::store', ['filter' => 'permission:inventory:create']);
    $routes->get('(:num)', 'Inventory::show/$1', ['filter' => 'permission:inventory:view']);
    $routes->get('(:num)/edit', 'Inventory::edit/$1', ['filter' => 'permission:inventory:edit']);
    $routes->post('(:num)/update', 'Inventory::update/$1', ['filter' => 'permission:inventory:edit']);
    $routes->delete('(:num)', 'Inventory::delete', ['filter' => 'permission:inventory:delete']);
    $routes->post('(:num)/adjust', 'Inventory::adjust', ['filter' => 'permission:inventory:edit']);
});

// Doctors routes
$routes->group('doctors', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Doctor::index', ['filter' => 'permission:users:view']);
    $routes->get('create', 'Doctor::create', ['filter' => 'permission:users:create']);
    $routes->post('store', 'Doctor::store', ['filter' => 'permission:users:create']);
    $routes->get('(:num)', 'Doctor::show/$1', ['filter' => 'permission:users:view']);
    $routes->get('(:num)/edit', 'Doctor::edit/$1', ['filter' => 'permission:users:edit']);
    $routes->post('(:num)/update', 'Doctor::update/$1', ['filter' => 'permission:users:edit']);
    $routes->delete('(:num)', 'Doctor::delete/$1', ['filter' => 'permission:users:delete']);
});

// Users routes
$routes->group('users', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Users::index', ['filter' => 'permission:users:view']);
    $routes->get('create', 'Users::create', ['filter' => 'permission:users:create']);
    $routes->post('store', 'Users::store', ['filter' => 'permission:users:create']);
    $routes->get('stats', 'Users::getUserStats', ['filter' => 'permission:users:view']);
    $routes->get('(:num)', 'Users::show/$1', ['filter' => 'permission:users:view']);
    $routes->get('(:num)/edit', 'Users::edit/$1', ['filter' => 'permission:users:edit']);
    $routes->post('(:num)/update', 'Users::update/$1', ['filter' => 'permission:users:edit']);
    $routes->delete('(:num)', 'Users::delete/$1', ['filter' => 'permission:users:delete']);
    $routes->get('(:num)/change-password', 'Users::changePassword/$1', ['filter' => 'permission:users:edit']);
    $routes->post('(:num)/update-password', 'Users::updatePassword/$1', ['filter' => 'permission:users:edit']);
    $routes->post('(:num)/toggle-status', 'Users::toggleStatus/$1', ['filter' => 'permission:users:edit']);
    // RBAC AJAX methods
    $routes->post('assign-role', 'Users::assignRole', ['filter' => 'permission:users:edit']);
    $routes->post('remove-role', 'Users::removeRole', ['filter' => 'permission:users:edit']);
    $routes->post('grant-permission', 'Users::grantPermission', ['filter' => 'permission:users:edit']);
    $routes->post('revoke-permission', 'Users::revokePermission', ['filter' => 'permission:users:edit']);
    $routes->post('get-permissions', 'Users::getUserPermissionsAjax', ['filter' => 'permission:users:view']);
});

// Roles routes
$routes->group('roles', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'RoleController::index', ['filter' => 'permission:users:view']);
    $routes->get('create', 'RoleController::create', ['filter' => 'permission:users:create']);
    $routes->post('store', 'RoleController::store', ['filter' => 'permission:users:create']);
    $routes->get('stats', 'RoleController::stats', ['filter' => 'permission:users:view']);
    $routes->get('sync', 'RoleController::sync', ['filter' => 'permission:users:edit']);
    $routes->get('(:num)', 'RoleController::show/$1', ['filter' => 'permission:users:view']);
    $routes->get('(:num)/edit', 'RoleController::edit/$1', ['filter' => 'permission:users:edit']);
    $routes->post('(:num)/update', 'RoleController::update/$1', ['filter' => 'permission:users:edit']);
    $routes->delete('(:num)', 'RoleController::delete/$1', ['filter' => 'permission:users:delete']);
    $routes->post('(:num)/toggle-status', 'RoleController::toggleStatus/$1', ['filter' => 'permission:users:edit']);
    $routes->get('(:num)/permissions', 'RoleController::getPermissions/$1', ['filter' => 'permission:users:view']);
});

// Notifications routes (accessible to all authenticated users - Tenant Plane)
$routes->group('', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications/ledger', 'NotificationLedger::index', ['filter' => 'permission:settings:view']); // New
    $routes->post('notifications/ledger/retry/(:num)', 'NotificationLedger::retry/$1', ['filter' => 'permission:settings:edit']); // New
    $routes->get('api/notifications', 'Notifications::api');
    $routes->post('notifications/mark-read', 'Notifications::markAsRead');
    $routes->post('notifications/mark-read/(:num)', 'Notifications::markAsRead/$1');
    $routes->delete('notifications/delete/(:num)', 'Notifications::delete/$1');
});

// Activity Log routes (accessible to all authenticated users - Tenant Plane)
$routes->group('', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('activity-log', 'ActivityLog::index');
    $routes->get('activity-log/api', 'ActivityLog::api');
});

// File routes (Tenant Plane)
$routes->group('file', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('download/(:num)', 'FileController::download/$1');
});

// Profile routes (P5-11-UX)
$routes->get('profile', 'Profile::index', ['filter' => ['auth', 'tenant']]);
$routes->group('profile', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Profile::index');
    $routes->post('update', 'Profile::update');
    $routes->post('change-password', 'Profile::changePassword');
});

// Settings routes (Tenant Plane)
$routes->group('settings', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->get('/', 'Settings::index', ['filter' => 'permission:settings:view']);
    $routes->post('update', 'Settings::update', ['filter' => 'permission:settings:edit']);
    $routes->post('updateClinic', 'Settings::updateClinic', ['filter' => 'permission:settings:edit']);
    $routes->post('updateSystem', 'Settings::updateSystem', ['filter' => 'permission:settings:edit']);
    $routes->post('updateWorkingHours', 'Settings::updateWorkingHours', ['filter' => 'permission:settings:edit']);
    $routes->get('backup', 'Settings::backup', ['filter' => 'permission:settings:view']);
    $routes->post('create-backup', 'Settings::createBackup', ['filter' => 'permission:settings:edit']);
    $routes->post('restore', 'Settings::restore', ['filter' => 'permission:settings:edit']);
    $routes->get('download-backup/(:segment)', 'Settings::downloadBackup/$1', ['filter' => 'permission:settings:view']);
    $routes->get('security', 'Settings::security', ['filter' => 'permission:settings:view']);
    $routes->post('security/update', 'Settings::updateSecurity', ['filter' => 'permission:settings:edit']);
    $routes->get('notification-settings', 'Settings::notifications', ['filter' => 'permission:settings:view']);
    $routes->post('notifications/update', 'Settings::updateNotifications', ['filter' => 'permission:settings:edit']);
    $routes->post('updateRetention', 'Settings::updateRetention', ['filter' => 'permission:settings:edit']);
    $routes->post('pruneExports', 'Settings::pruneExports', ['filter' => 'permission:settings:edit']);
    
    // Notification Channels (P5-09a)
    $routes->get('channels', 'Settings::channels', ['filter' => 'permission:settings:view']);
    $routes->post('updateChannelStatus', 'Settings::updateChannelStatus', ['filter' => 'permission:settings:edit']); // Gated by global_mode inside
    $routes->post('updateChannelConfig', 'Settings::updateChannelConfig', ['filter' => 'permission:settings:edit']);
});

// --------------------------------------------------------------------
// Control Plane Routes (RBAC & Platform Admin)
// --------------------------------------------------------------------

// RBAC Sync routes (Control Plane)
$routes->group('rbac', ['filter' => 'controlplane'], function ($routes) {
    $routes->get('sync', 'SyncController::sync');
    $routes->get('status', 'SyncController::status');
    $routes->get('init', 'SyncController::init');
    $routes->get('setup', function () {
        return view('rbac/setup');
    });
});


// API routes (Tenant Plane)
$routes->group('api', ['filter' => ['auth', 'tenant']], function ($routes) {
    $routes->group('v1', function ($routes) {
        // Patients
        $routes->get('patients', 'Api\Patient::index', ['filter' => 'permission:patients:view']);
        $routes->get('patients/(:segment)', 'Api\Patient::show/$1', ['filter' => 'permission:patients:view']);
        $routes->post('patients', 'Api\Patient::create', ['filter' => 'permission:patients:create']);
        $routes->put('patients/(:segment)', 'Api\Patient::update/$1', ['filter' => 'permission:patients:edit']);
        $routes->delete('patients/(:segment)', 'Api\Patient::delete/$1', ['filter' => 'permission:patients:delete']);

        // Examinations
        $routes->get('examinations', 'Api\Examination::index', ['filter' => 'permission:examinations:view']);
        $routes->get('examinations/(:segment)', 'Api\Examination::show/$1', ['filter' => 'permission:examinations:view']);
        $routes->post('examinations', 'Api\Examination::create', ['filter' => 'permission:examinations:create']);
        $routes->put('examinations/(:segment)', 'Api\Examination::update/$1', ['filter' => 'permission:examinations:edit']);
        $routes->delete('examinations/(:segment)', 'Api\Examination::delete/$1', ['filter' => 'permission:examinations:delete']);

        // Appointments
        $routes->get('appointments', 'Api\Appointment::index', ['filter' => 'permission:appointments:view']);
        $routes->get('appointments/(:segment)', 'Api\Appointment::show/$1', ['filter' => 'permission:appointments:view']);
        $routes->post('appointments', 'Api\Appointment::create', ['filter' => 'permission:appointments:create']);
        $routes->put('appointments/(:segment)', 'Api\Appointment::update/$1', ['filter' => 'permission:appointments:edit']);
        $routes->delete('appointments/(:segment)', 'Api\Appointment::delete/$1', ['filter' => 'permission:appointments:delete']);

        // Finances
        $routes->get('finances', 'Api\Finance::index', ['filter' => 'permission:finance:view']);
        $routes->get('finances/(:segment)', 'Api\Finance::show/$1', ['filter' => 'permission:finance:view']);
        $routes->post('finances', 'Api\Finance::create', ['filter' => 'permission:finance:create']);
        $routes->put('finances/(:segment)', 'Api\Finance::update/$1', ['filter' => 'permission:finance:edit']);
        $routes->delete('finances/(:segment)', 'Api\Finance::delete/$1', ['filter' => 'permission:finance:delete']);
    });

    // Search API endpoints for select dropdowns
    $routes->group('search', function ($routes) {
        $routes->get('patients', 'Api\Search::patients', ['filter' => 'permission:patients:view']);
        $routes->get('users', 'Api\Search::users', ['filter' => 'permission:users:view']);
        $routes->get('examinations', 'Api\Search::examinations', ['filter' => 'permission:examinations:view']);
        $routes->get('treatments', 'Api\Search::treatments', ['filter' => 'permission:treatments:view']);
        $routes->get('inventory', 'Api\Search::inventory', ['filter' => 'permission:inventory:view']);
        $routes->get('medications', 'Api\Search::medications', ['filter' => 'permission:prescriptions:view']);
        $routes->get('treatment-types', 'Api\Search::treatmentTypes', ['filter' => 'permission:treatments:view']);
        $routes->get('departments', 'Api\Search::departments', ['filter' => 'permission:users:view']);
        $routes->get('roles', 'Api\Search::roles', ['filter' => 'permission:users:view']);
    });
});
