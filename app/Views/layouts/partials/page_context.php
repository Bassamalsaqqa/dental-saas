<?php
    helper('asset');
    $rawPageTitle = $pageTitle ?? $title ?? ($clinic['name'] ?? 'Denta+');
    $uri = service('uri');
    $segments = $uri->getSegments();
    $segment1 = strtolower((string) ($segments[0] ?? 'dashboard'));
    $segment2 = strtolower((string) ($segments[1] ?? ''));
    $segment3 = strtolower((string) ($segments[2] ?? ''));

    $explicitTitleMap = [
        'Dashboard' => lang('UI.menuDashboard'),
        'Patients' => lang('UI.menuPatients'),
        'Examinations' => lang('UI.menuExaminations'),
        'Appointments' => lang('UI.menuAppointments'),
        'Treatments' => lang('UI.menuTreatments'),
        'Prescriptions' => lang('UI.menuPrescriptions'),
        'Finance' => lang('UI.menuFinance'),
        'Reports' => lang('UI.menuReports'),
        'Inventory' => lang('UI.menuInventory'),
        'Users' => lang('UI.menuUserManagement'),
        'Roles' => lang('UI.menuRoleManagement'),
        'Activity Log' => lang('UI.menuActivityLog'),
        'Settings' => lang('UI.menuSettings'),
        'Create New Treatment' => lang('Treatment.create_new_treatment'),
        'Edit Treatment' => lang('Treatment.edit_treatment'),
        'Treatment Management' => lang('UI.menuTreatments'),
        'Create New Patient' => lang('UI.patientCreateTitle'),
        'Register New Patient' => lang('UI.patientCreateTitle'),
        'New Appointment' => lang('UI.appointmentNew'),
        'Edit Appointment' => lang('UI.appointmentEdit'),
        'New Examination' => lang('UI.newExamination'),
    ];

    $routeTitleMap = [
        'dashboard' => lang('UI.menuDashboard'),
        'patients' => lang('UI.menuPatients'),
        'patient' => $segment2 === 'create' ? lang('UI.patientCreateTitle') : lang('UI.menuPatients'),
        'examinations' => $segment2 === 'create' ? lang('UI.newExamination') : lang('UI.menuExaminations'),
        'examination' => $segment2 === 'create' ? lang('UI.newExamination') : lang('UI.menuExaminations'),
        'appointments' => $segment2 === 'create' ? lang('UI.appointmentNew') : lang('UI.menuAppointments'),
        'appointment' => $segment2 === 'create'
            ? lang('UI.appointmentNew')
            : (($segment3 === 'edit' || $segment2 === 'edit') ? lang('UI.appointmentEdit') : lang('UI.menuAppointments')),
        'treatments' => $segment2 === 'create' ? lang('Treatment.create_new_treatment') : lang('UI.menuTreatments'),
        'treatment' => $segment2 === 'create'
            ? lang('Treatment.create_new_treatment')
            : (($segment3 === 'edit' || $segment2 === 'edit') ? lang('Treatment.edit_treatment') : lang('UI.menuTreatments')),
        'prescriptions' => lang('UI.menuPrescriptions'),
        'prescription' => lang('UI.menuPrescriptions'),
        'providers' => lang('UI.menuProviders'),
        'finance' => lang('UI.menuFinance'),
        'reports' => lang('UI.menuReports'),
        'inventory' => lang('UI.menuInventory'),
        'users' => lang('UI.menuUserManagement'),
        'roles' => lang('UI.menuRoleManagement'),
        'activity-log' => lang('UI.menuActivityLog'),
        'settings' => lang('UI.menuSettings'),
        'notifications' => lang('UI.notifications'),
        'profile' => lang('UI.profileMenu'),
    ];

    $resolvedPageTitle = $explicitTitleMap[$rawPageTitle] ?? ($routeTitleMap[$segment1] ?? $rawPageTitle);
?>
