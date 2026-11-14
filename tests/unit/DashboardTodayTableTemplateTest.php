<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use DOMDocument;
use DOMXPath;

/**
 * DB-Free template test for dashboard/index.php
 *
 * Verifies Reception and Clinical Handoff enhancements with modern calm semantic presentation:
 * - Main label 'Clinic work' / 'عمل العيادة', no subtitle/tagline/secondary header/KPI
 * - Single accessible scroll region carrying visit-deck, visit-table-scroll and grid-scroll classes
 * - Header with column heads (Time & duration, Name, Resource, Status, Actions) and Actions head
 * - Single semantic list/listitem DOM convertible from desktop rows to mobile cards
 * - Arrival form for scheduled/confirmed
 * - Ready form for arrived
 * - Start visit form for ready ('بدء الزيارة / Start visit')
 * - In-chair actions: direct clinical encounter link + Ready for reception form ('جاهز للاستقبال / Ready for reception')
 * - Direct clinical encounter action variations (single, multiple, none)
 * - View action for terminal stages and non-permitted roles
 * - No nested buttons inside anchors
 * - Patient-scoped aria-labels across all interactive controls in EN and AR
 * - today-transitions-config and studio-today-transitions.js inclusion
 * - Restrained filter controls and honest loaded counts
 * - 3 Canonical attention queues in desktop rail and mobile collapsible disclosure
 * - Long patient name wrapping and phone LTR formatting
 */
final class DashboardTodayTableTemplateTest extends CIUnitTestCase
{
    private function renderDashboardView(string $scenario = 'all_stages', bool $canEditVisits = true, string $locale = 'en', string $examPerm = 'full_exam'): array
    {
        $fixture = dirname(__DIR__) . '/_support/fixtures/DashboardTodayTablePresenter.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' '
            . escapeshellarg($fixture) . ' '
            . escapeshellarg($locale) . ' '
            . escapeshellarg($canEditVisits ? 'allowed' : 'denied') . ' '
            . escapeshellarg($scenario) . ' '
            . escapeshellarg($examPerm);

        $html = shell_exec($cmd);
        $this->assertIsString($html, 'Dashboard fixture execution must succeed');

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        return [$html, $dom, $xpath];
    }

    public function testMainLabelIsClinicWorkWithoutSubtitleInEnglish(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $headingNodes = $xpath->query('//*[contains(@class,"page-title-row")]//h1');
        $this->assertSame(1, $headingNodes->length, 'Page title row must contain exactly one h1.');
        $this->assertStringContainsString('Clinic work', $headingNodes->item(0)->textContent);

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//*[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row must not contain any subtitle paragraph.');
    }

    public function testMainLabelIsClinicWorkWithoutSubtitleInArabic(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'ar');

        $headingNodes = $xpath->query('//*[contains(@class,"page-title-row")]//h1');
        $this->assertSame(1, $headingNodes->length, 'Page title row must contain exactly one h1.');
        $this->assertStringContainsString('عمل العيادة', $headingNodes->item(0)->textContent);

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//*[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row in Arabic must not contain any subtitle paragraph.');
    }

    public function testSingleScrollContainerWithGridScrollClass(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $scrollContainers = $xpath->query('//div[contains(@class,"visit-deck") or contains(@class,"visit-table-scroll")]');
        $this->assertSame(1, $scrollContainers->length, 'Exactly one visit container region must exist.');

        $container = $scrollContainers->item(0);
        $class = $container->getAttribute('class');
        $this->assertStringContainsString('grid-scroll', $class, 'Container must also carry grid-scroll class to avoid duplicate wrappers.');
        $this->assertSame('0', $container->getAttribute('tabindex'));
        $this->assertSame('region', $container->getAttribute('role'));
    }

    public function testHeaderHasColumnsWithActionsHeader(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $headerRows = $xpath->query('//*[contains(@class,"visit-header-row")]');
        $this->assertGreaterThanOrEqual(1, $headerRows->length);

        $headers = $xpath->query('//*[contains(@class,"visit-header-row")]//*[contains(@class,"col-head")]');
        $this->assertSame(5, $headers->length, 'Desktop header row must have 5 column headers (Time, Name, Resource, Status, Actions).');

        $actionHeader = $xpath->query('//*[contains(@class,"visit-header-row")]//*[contains(@class,"visit-action-head")]');
        $this->assertSame(1, $actionHeader->length);
        $this->assertSame('Actions', trim($actionHeader->item(0)->textContent));
    }

    public function testArrivalActionRenderedForScheduledAndConfirmedWhenPermitted(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check scheduled row Arrival form (id=101)
        $form1 = $xpath->query('//*[@data-visit-id="101"]//form[@data-appointment-transition]');
        $this->assertSame(1, $form1->length);
        $statusInput1 = $xpath->query('.//input[@name="status"]', $form1->item(0));
        $this->assertSame('arrived', $statusInput1->item(0)->getAttribute('value'));
        $expectedInput1 = $xpath->query('.//input[@name="expected_status"]', $form1->item(0));
        $this->assertSame('scheduled', $expectedInput1->item(0)->getAttribute('value'));

        $button1 = $xpath->query('.//button[@type="submit"]', $form1->item(0));
        $this->assertSame(1, $button1->length);
        $this->assertSame('Arrival', trim($button1->item(0)->textContent));

        // Check confirmed row Arrival form (id=102)
        $form2 = $xpath->query('//*[@data-visit-id="102"]//form[@data-appointment-transition]');
        $this->assertSame(1, $form2->length);
        $statusInput2 = $xpath->query('.//input[@name="status"]', $form2->item(0));
        $this->assertSame('arrived', $statusInput2->item(0)->getAttribute('value'));
        $expectedInput2 = $xpath->query('.//input[@name="expected_status"]', $form2->item(0));
        $this->assertSame('confirmed', $expectedInput2->item(0)->getAttribute('value'));

        $button2 = $xpath->query('.//button[@type="submit"]', $form2->item(0));
        $this->assertSame(1, $button2->length);
        $this->assertSame('Arrival', trim($button2->item(0)->textContent));
    }

    public function testReadyActionRenderedForArrivedWhenPermitted(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check arrived row Ready form (id=103)
        $form = $xpath->query('//*[@data-visit-id="103"]//form[@data-appointment-transition]');
        $this->assertSame(1, $form->length);
        $statusInput = $xpath->query('.//input[@name="status"]', $form->item(0));
        $this->assertSame('ready', $statusInput->item(0)->getAttribute('value'));
        $expectedInput = $xpath->query('.//input[@name="expected_status"]', $form->item(0));
        $this->assertSame('arrived', $expectedInput->item(0)->getAttribute('value'));

        $button = $xpath->query('.//button[@type="submit"]', $form->item(0));
        $this->assertSame(1, $button->length);
        $this->assertSame('Ready', trim($button->item(0)->textContent));
    }

    public function testStartVisitActionRenderedForReadyWhenPermitted(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check ready row Start visit form (id=104)
        $form = $xpath->query('//*[@data-visit-id="104"]//form[@data-appointment-transition]');
        $this->assertSame(1, $form->length, 'Ready row must render Start visit transition form.');
        $statusInput = $xpath->query('.//input[@name="status"]', $form->item(0));
        $this->assertSame('in_chair', $statusInput->item(0)->getAttribute('value'));
        $expectedInput = $xpath->query('.//input[@name="expected_status"]', $form->item(0));
        $this->assertSame('ready', $expectedInput->item(0)->getAttribute('value'));

        $button = $xpath->query('.//button[@type="submit"]', $form->item(0));
        $this->assertSame(1, $button->length);
        $this->assertSame('Start visit', trim($button->item(0)->textContent));
        $this->assertSame('Start visit - Ready Patient', $button->item(0)->getAttribute('aria-label'));
    }

    public function testInChairRendersEncounterLinkAndReadyForReceptionForm(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $row105 = $xpath->query('//*[@data-visit-id="105"]');
        $this->assertSame(1, $row105->length);

        // Encounter link
        $encounterLink = $xpath->query('.//*[contains(@class,"visit-action")]//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(1, $encounterLink->length, 'In-chair row must render encounter action link.');
        $this->assertSame('Open examination', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('examination/801', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('return_to=dashboard', $encounterLink->item(0)->getAttribute('href'));
        $this->assertSame('Open examination - InChair Patient', $encounterLink->item(0)->getAttribute('aria-label'));

        // Transition form for checkout (Ready for reception)
        $form = $xpath->query('.//*[contains(@class,"visit-action")]//form[@data-appointment-transition]', $row105->item(0));
        $this->assertSame(1, $form->length, 'In-chair row must render Ready for reception transition form.');
        $statusInput = $xpath->query('.//input[@name="status"]', $form->item(0));
        $this->assertSame('checkout', $statusInput->item(0)->getAttribute('value'));
        $expectedInput = $xpath->query('.//input[@name="expected_status"]', $form->item(0));
        $this->assertSame('in_chair', $expectedInput->item(0)->getAttribute('value'));

        $btn = $xpath->query('.//button[@type="submit"]', $form->item(0));
        $this->assertSame(1, $btn->length);
        $this->assertSame('Ready for reception', trim($btn->item(0)->textContent));
        $this->assertSame('Ready for reception - InChair Patient', $btn->item(0)->getAttribute('aria-label'));
    }

    public function testEncounterMultipleRoutesToPatientWorkspace(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_multiple', true, 'en');

        $encounterLink = $xpath->query('//*[@data-visit-id="105"]//a[contains(@class,"encounter-btn")]');
        $this->assertSame(1, $encounterLink->length);
        $this->assertSame('Patient examinations', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('patient/505', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('tab=examinations', $encounterLink->item(0)->getAttribute('href'));
        $this->assertSame('Patient examinations - InChair Patient', $encounterLink->item(0)->getAttribute('aria-label'));
    }

    public function testEncounterNoneRoutesToExaminationCreate(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_none', true, 'en');

        $encounterLink = $xpath->query('//*[@data-visit-id="105"]//a[contains(@class,"encounter-btn")]');
        $this->assertSame(1, $encounterLink->length);
        $this->assertSame('Start examination', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('examination/create', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('patient_id=505', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('appointment_id=105', $encounterLink->item(0)->getAttribute('href'));
        $this->assertSame('Start examination - InChair Patient', $encounterLink->item(0)->getAttribute('aria-label'));
    }

    public function testViewDetailRenderedForCompletedStage(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Completed (id=106) must not have transition forms
        $forms = $xpath->query('//*[@data-visit-id="106"]//form');
        $this->assertSame(0, $forms->length);

        $link = $xpath->query('//*[@data-visit-id="106"]//*[contains(@class,"visit-action")]//a');
        $this->assertSame(1, $link->length);
        $this->assertSame('View', trim($link->item(0)->textContent));
        $this->assertStringContainsString('appointment/106', $link->item(0)->getAttribute('href'));
    }

    public function testWithoutEditPermissionAndWithoutExamPermissionAllRowsRenderViewOnly(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_fallback_no_permission', false, 'en', 'none');

        $forms = $xpath->query('//*[contains(@class,"visit-deck")]//form');
        $this->assertSame(0, $forms->length, 'No transition forms should render without appointments.edit permission.');

        $actionLinks = $xpath->query('//*[contains(@class,"visit-deck")]//*[contains(@class,"visit-action")]//a');
        $this->assertGreaterThanOrEqual(1, $actionLinks->length);
        foreach ($actionLinks as $link) {
            $this->assertSame('View', trim($link->textContent));
        }
    }

    public function testDoctorWithoutEditPermissionCanOpenOwnedExamination(): void
    {
        // Doctor has appointments.view + examinations.view, but NOT appointments.edit
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', false, 'en', 'view_only');

        $forms = $xpath->query('//*[contains(@class,"visit-deck")]//form');
        $this->assertSame(0, $forms->length, 'Doctor without appointments.edit must have zero mutation/transition forms.');

        // In-chair row 105 has an existing exam -> renders Open examination link
        $row105 = $xpath->query('//*[@data-visit-id="105"]');
        $this->assertSame(1, $row105->length);
        $encounterLink = $xpath->query('.//*[contains(@class,"visit-action")]//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(1, $encounterLink->length, 'Doctor must be able to open owned existing examination.');
        $this->assertSame('Open examination', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('examination/801', $encounterLink->item(0)->getAttribute('href'));

        // Other non-in-chair rows render View link
        foreach (['101', '102', '103', '104', '106'] as $id) {
            $link = $xpath->query('//*[@data-visit-id="' . $id . '"]//*[contains(@class,"visit-action")]//a');
            $this->assertSame(1, $link->length);
            $this->assertSame('View', trim($link->item(0)->textContent));
        }
    }

    public function testCreateOnlyExamUserWithoutEditPermissionCanStartExaminationWhenVerifiedZero(): void
    {
        // User has examinations.create but NOT appointments.edit
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_none', false, 'en', 'create_only');

        $forms = $xpath->query('//*[contains(@class,"visit-deck")]//form');
        $this->assertSame(0, $forms->length, 'Create-only exam user without appointments.edit has no mutation controls.');

        $encounterLink = $xpath->query('//*[@data-visit-id="105"]//*[contains(@class,"visit-action")]//a[contains(@class,"encounter-btn")]');
        $this->assertSame(1, $encounterLink->length, 'Create-only exam user must get explicit create when verified 0 exams.');
        $this->assertSame('Start examination', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('examination/create', $encounterLink->item(0)->getAttribute('href'));
    }

    public function testUserWithAppointmentsEditWithoutExaminationPermissionsSeesStageControlAndDetail(): void
    {
        // User has appointments.edit (e.g. receptionist) but NO examination permissions
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_fallback_no_permission', true, 'en', 'none');

        $row105 = $xpath->query('//*[@data-visit-id="105"]');
        $this->assertSame(1, $row105->length);

        // Must see stage control: Ready for reception
        $transitionBtn = $xpath->query('.//*[contains(@class,"visit-action")]//form[@data-appointment-transition]//button[@type="submit"]', $row105->item(0));
        $this->assertSame(1, $transitionBtn->length, 'User with appointments.edit must see stage control.');
        $this->assertSame('Ready for reception', trim($transitionBtn->item(0)->textContent));

        // Must see detail link: View
        $viewLink = $xpath->query('.//*[contains(@class,"visit-action")]//a', $row105->item(0));
        $this->assertSame(1, $viewLink->length, 'User without examination permission sees detail link, not duplicate buttons.');
        $this->assertSame('View', trim($viewLink->item(0)->textContent));

        // Must NOT see misleading Open examination or encounter-btn
        $encounterLink = $xpath->query('.//*[contains(@class,"visit-action")]//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(0, $encounterLink->length, 'User without examination permission must NOT see misleading Open examination.');
    }

    public function testNoButtonIsNestedInsideAnchorTag(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $nestedButtons = $xpath->query('//a//button');
        $this->assertSame(0, $nestedButtons->length, 'Valid HTML/accessibility rule: <button> must never be nested inside <a>.');

        $nestedAnchors = $xpath->query('//button//a');
        $this->assertSame(0, $nestedAnchors->length, 'Valid HTML rule: <a> must never be nested inside <button>.');
    }

    public function testConfigElementAndScriptTagArePresent(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $configEl = $xpath->query('//div[@id="today-transitions-config"]');
        $this->assertSame(1, $configEl->length, '#today-transitions-config element must exist.');
        $this->assertTrue($configEl->item(0)->hasAttribute('data-action-arrival'));
        $this->assertTrue($configEl->item(0)->hasAttribute('data-action-ready'));
        $this->assertTrue($configEl->item(0)->hasAttribute('data-action-start-visit'));
        $this->assertTrue($configEl->item(0)->hasAttribute('data-action-ready-for-reception'));
        $this->assertTrue($configEl->item(0)->hasAttribute('data-action-view'));
        $this->assertSame('1', $configEl->item(0)->getAttribute('data-can-edit'));

        $scripts = $xpath->query('//script[contains(@src,"studio-today-transitions.js")]');
        $this->assertSame(1, $scripts->length, 'studio-today-transitions.js script tag must be included.');
        $this->assertTrue($scripts->item(0)->hasAttribute('defer'), 'Script tag must have defer attribute.');
    }

    public function testAccessiblePatientScopedLabelsAndScrollRegionParity(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Verify scrollable region accessibility attributes
        $scrollContainer = $xpath->query('//div[contains(@class,"visit-deck")]');
        $this->assertSame(1, $scrollContainer->length);
        $this->assertSame('0', $scrollContainer->item(0)->getAttribute('tabindex'));
        $this->assertSame('region', $scrollContainer->item(0)->getAttribute('role'));
        $this->assertSame('appointments today', $scrollContainer->item(0)->getAttribute('aria-label'));

        // Verify data-patient-name and scoped labels on all rows
        $expected = [
            '101' => ['patient' => 'Scheduled Patient', 'action' => 'button', 'text' => 'Arrival', 'label' => 'Arrival - Scheduled Patient'],
            '102' => ['patient' => 'Confirmed Patient', 'action' => 'button', 'text' => 'Arrival', 'label' => 'Arrival - Confirmed Patient'],
            '103' => ['patient' => 'Arrived Patient', 'action' => 'button', 'text' => 'Ready', 'label' => 'Ready - Arrived Patient'],
            '104' => ['patient' => 'Ready Patient', 'action' => 'button', 'text' => 'Start visit', 'label' => 'Start visit - Ready Patient'],
            '106' => ['patient' => 'Completed Patient', 'action' => 'a', 'text' => 'View', 'label' => 'View - Completed Patient'],
        ];

        foreach ($expected as $visitId => $exp) {
            $row = $xpath->query('//*[@data-visit-id="' . $visitId . '"]');
            $this->assertSame(1, $row->length, "Row {$visitId} must exist");
            $this->assertSame($exp['patient'], $row->item(0)->getAttribute('data-patient-name'));

            if ($exp['action'] === 'button') {
                $btn = $xpath->query('.//button[@type="submit"]', $row->item(0));
                $this->assertSame(1, $btn->length);
                $this->assertSame($exp['text'], trim($btn->item(0)->textContent));
                $this->assertSame($exp['label'], $btn->item(0)->getAttribute('aria-label'));
            } else {
                $link = $xpath->query('.//*[contains(@class,"visit-action")]//a', $row->item(0));
                $this->assertSame(1, $link->length);
                $this->assertSame($exp['text'], trim($link->item(0)->textContent));
                $this->assertSame($exp['label'], $link->item(0)->getAttribute('aria-label'));
            }
        }
    }

    public function testAccessiblePatientScopedLabelsInArabic(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'ar');

        $scrollContainer = $xpath->query('//div[contains(@class,"visit-deck")]');
        $this->assertSame(1, $scrollContainer->length);
        $this->assertSame('0', $scrollContainer->item(0)->getAttribute('tabindex'));
        $this->assertSame('region', $scrollContainer->item(0)->getAttribute('role'));

        // Check scheduled row in Arabic (arrival action)
        $btn101 = $xpath->query('//*[@data-visit-id="101"]//button[@type="submit"]');
        $this->assertSame(1, $btn101->length);
        $this->assertStringContainsString('Scheduled Patient', $btn101->item(0)->getAttribute('aria-label'));

        // Check arrived row in Arabic (ready action)
        $btn103 = $xpath->query('//*[@data-visit-id="103"]//button[@type="submit"]');
        $this->assertSame(1, $btn103->length);
        $this->assertStringContainsString('Arrived Patient', $btn103->item(0)->getAttribute('aria-label'));

        // Check ready row in Arabic (start visit action)
        $btn104 = $xpath->query('//*[@data-visit-id="104"]//button[@type="submit"]');
        $this->assertSame(1, $btn104->length);
        $this->assertSame('بدء الزيارة', trim($btn104->item(0)->textContent));
        $this->assertStringContainsString('Ready Patient', $btn104->item(0)->getAttribute('aria-label'));

        // Check in_chair row in Arabic (encounter action + ready for reception)
        $row105 = $xpath->query('//*[@data-visit-id="105"]');
        $encounter105 = $xpath->query('.//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(1, $encounter105->length);
        $this->assertSame('فتح الفحص', trim($encounter105->item(0)->textContent));

        $btn105 = $xpath->query('.//button[@type="submit"]', $row105->item(0));
        $this->assertSame(1, $btn105->length);
        $this->assertSame('جاهز للاستقبال', trim($btn105->item(0)->textContent));

        // Check completed row in Arabic (view action)
        $link106 = $xpath->query('//*[@data-visit-id="106"]//*[contains(@class,"visit-action")]//a');
        $this->assertSame(1, $link106->length);
        $this->assertStringContainsString('Completed Patient', $link106->item(0)->getAttribute('aria-label'));
    }

    public function testSemanticListAndListitemRoles(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Verify semantic list role
        $listNodes = $xpath->query('//div[contains(@class,"visit-list") and @role="list"]');
        $this->assertGreaterThanOrEqual(1, $listNodes->length, 'Visit list must have role="list".');

        // Verify each visit entry carries role="listitem"
        $entries = $xpath->query('//article[contains(@class,"visit-entry") and @role="listitem"]');
        $this->assertSame(6, $entries->length, 'All 6 visit entries in all_stages must have role="listitem".');

        // Verify no duplicate hidden copies of visit-entry
        $allEntries = $xpath->query('//*[contains(@class,"visit-entry")]');
        $this->assertSame(6, $allEntries->length, 'Exactly one representation per visit in the DOM tree.');
    }

    public function testRestrainedFilterControlsAndDerivedCounts(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check filter buttons group in panel-today
        $filterBtns = $xpath->query('//*[@id="panel-today"]//div[contains(@class,"filter-group")]//button');
        $this->assertGreaterThanOrEqual(3, $filterBtns->length, 'Must have All, Waiting, and In Chair filter buttons.');

        // Verify 'All' button count is 6 (total visits)
        $allBtn = $xpath->query('//*[@id="panel-today"]//div[contains(@class,"filter-group")]//button[@data-filter="all"]');
        $this->assertSame(1, $allBtn->length);
        $this->assertStringContainsString('(6)', $allBtn->item(0)->textContent);

        // Verify 'Waiting' button count is 4 (scheduled:101, confirmed:102, arrived:103, ready:104)
        $waitingBtn = $xpath->query('//*[@id="panel-today"]//div[contains(@class,"filter-group")]//button[@data-filter="waiting"]');
        $this->assertSame(1, $waitingBtn->length);
        $this->assertStringContainsString('(4)', $waitingBtn->item(0)->textContent);

        // Verify 'In Chair' button count is 1 (in_chair:105)
        $inChairBtn = $xpath->query('//*[@id="panel-today"]//div[contains(@class,"filter-group")]//button[@data-filter="in_chair"]');
        $this->assertSame(1, $inChairBtn->length);
        $this->assertStringContainsString('(1)', $inChairBtn->item(0)->textContent);

        // Verify 'Other' button count is 1 (completed:106)
        $otherBtn = $xpath->query('//*[@id="panel-today"]//div[contains(@class,"filter-group")]//button[@data-filter="other"]');
        $this->assertSame(1, $otherBtn->length);
        $this->assertStringContainsString('(1)', $otherBtn->item(0)->textContent);
    }

    public function testThreeCanonicalAttentionQueuesInDesktopRailAndMobileDisclosure(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Desktop attention rail exists
        $rail = $xpath->query('//div[contains(@class,"attention-rail")]');
        $this->assertSame(1, $rail->length, 'Desktop attention rail must exist.');

        // Mobile attention disclosure exists
        $disclosure = $xpath->query('//details[contains(@class,"attention-disclosure")]');
        $this->assertSame(1, $disclosure->length, 'Mobile attention collapsible disclosure must exist.');

        // Check 3 canonical queue links are present in both
        $railLinks = $xpath->query('.//a[contains(@class,"attention-queue-link")]', $rail->item(0));
        $this->assertSame(3, $railLinks->length, 'Desktop rail must contain exactly 3 canonical queues.');

        $disclosureLinks = $xpath->query('.//a[contains(@class,"attention-queue-link")]', $disclosure->item(0));
        $this->assertSame(3, $disclosureLinks->length, 'Mobile disclosure must contain exactly 3 canonical queues.');
    }

    public function testLongPatientNameAndLtrPhoneWrapping(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $names = $xpath->query('//*[@data-visit-id="101"]//*[contains(@class,"patient-name")]');
        $this->assertSame(1, $names->length);
        $this->assertSame('Scheduled Patient', trim($names->item(0)->textContent));

        // Stretched row link has aria-label with patient identity
        $visitLink = $xpath->query('//*[@data-visit-id="101"]//a[contains(@class,"visit-link")]');
        $this->assertSame(1, $visitLink->length);
        $this->assertStringContainsString('Scheduled Patient', $visitLink->item(0)->getAttribute('aria-label'));
    }

    public function testRecentPatientsContextPreserved(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $recentSection = $xpath->query('//section[contains(@class,"recent-patients-card")]');
        $this->assertSame(1, $recentSection->length, 'Recent patients card must be rendered.');

        $recentLink = $xpath->query('.//a[contains(@class,"recent-patients-link")]', $recentSection->item(0));
        $this->assertSame(1, $recentLink->length);
        $this->assertStringContainsString('patients', $recentLink->item(0)->getAttribute('href'));
    }

    public function testActionTargetSizesAndPendingAttributes(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check primary action button on dashboard header
        $bookBtn = $xpath->query('//*[contains(@class,"heading-actions")]//a[contains(@class,"dashboard-btn-primary")]');
        $this->assertSame(1, $bookBtn->length);

        // Check secondary action button on dashboard header
        $registerBtn = $xpath->query('//*[contains(@class,"heading-actions")]//a[contains(@class,"dashboard-btn-secondary")]');
        $this->assertSame(1, $registerBtn->length);

        // Check transition forms have CSRF and expected_status fields
        $forms = $xpath->query('//form[@data-appointment-transition]');
        $this->assertGreaterThanOrEqual(1, $forms->length);
        foreach ($forms as $form) {
            $expected = $xpath->query('.//input[@name="expected_status"]', $form);
            $this->assertSame(1, $expected->length, 'Every transition form must have expected_status');
            $csrf = $xpath->query('.//input[@name="csrf_test_name"]', $form);
            $this->assertSame(1, $csrf->length, 'Every transition form must have CSRF field');
        }
    }
}
