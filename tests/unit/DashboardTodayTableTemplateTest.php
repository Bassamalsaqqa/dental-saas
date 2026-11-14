<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use DOMDocument;
use DOMXPath;

/**
 * DB-Free template test for dashboard/index.php
 *
 * Verifies Reception and Clinical Handoff enhancements:
 * - Main label 'Clinic work' / 'عمل العيادة', no subtitle/tagline/secondary header/KPI
 * - Single accessible scroll region carrying visit-table-scroll and grid-scroll classes
 * - 7th column Actions header with 7-column grid layout
 * - Arrival form for scheduled/confirmed
 * - Ready form for arrived
 * - Start visit form for ready ('بدء الزيارة / Start visit')
 * - In-chair actions: direct clinical encounter link + Ready for reception form ('جاهز للاستقبال / Ready for reception')
 * - Direct clinical encounter action variations (single, multiple, none)
 * - View action for terminal stages and non-permitted roles
 * - No nested buttons inside anchors
 * - Patient-scoped aria-labels across all interactive controls in EN and AR
 * - today-transitions-config and studio-today-transitions.js inclusion
 */
final class DashboardTodayTableTemplateTest extends CIUnitTestCase
{
    private function renderDashboardView(string $scenario = 'all_stages', bool $canEditVisits = true, string $locale = 'en'): array
    {
        $fixture = dirname(__DIR__) . '/_support/fixtures/DashboardTodayTablePresenter.php';
        $cmd = escapeshellarg(PHP_BINARY) . ' '
            . escapeshellarg($fixture) . ' '
            . escapeshellarg($locale) . ' '
            . escapeshellarg($canEditVisits ? 'allowed' : 'denied') . ' '
            . escapeshellarg($scenario);

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

        $headingNodes = $xpath->query('//div[contains(@class,"page-title-row")]//h1');
        $this->assertSame(1, $headingNodes->length, 'Page title row must contain exactly one h1.');
        $this->assertStringContainsString('Clinic work', $headingNodes->item(0)->textContent);

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//div[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row must not contain any subtitle paragraph.');
    }

    public function testMainLabelIsClinicWorkWithoutSubtitleInArabic(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'ar');

        $headingNodes = $xpath->query('//div[contains(@class,"page-title-row")]//h1');
        $this->assertSame(1, $headingNodes->length, 'Page title row must contain exactly one h1.');
        $this->assertStringContainsString('عمل العيادة', $headingNodes->item(0)->textContent);

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//div[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row in Arabic must not contain any subtitle paragraph.');
    }

    public function testSingleScrollContainerWithGridScrollClass(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $scrollContainers = $xpath->query('//div[contains(@class,"visit-table-scroll")]');
        $this->assertSame(1, $scrollContainers->length, 'Exactly one visit-table-scroll container must exist.');

        $container = $scrollContainers->item(0);
        $class = $container->getAttribute('class');
        $this->assertStringContainsString('grid-scroll', $class, 'visit-table-scroll must also carry grid-scroll class to avoid duplicate wrappers.');
        $this->assertSame('0', $container->getAttribute('tabindex'));
        $this->assertSame('region', $container->getAttribute('role'));
    }

    public function testTableHasSevenColumnsWithActionsHeader(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $tables = $xpath->query('//table[contains(@class,"visit-table")]');
        $this->assertGreaterThanOrEqual(1, $tables->length);
        $table = $tables->item(0);

        $this->assertSame('12,24,14,14,10,12,14', $table->getAttribute('data-grid-widths'));

        $headers = $xpath->query('//table[contains(@class,"visit-table")]//thead//th');
        $this->assertSame(7, $headers->length, 'Table must have exactly 7 column headers.');

        $actionHeader = $xpath->query('//table[contains(@class,"visit-table")]//thead//th[contains(@class,"visit-action-head")]');
        $this->assertSame(1, $actionHeader->length);
        $this->assertSame('Actions', trim($actionHeader->item(0)->textContent));
    }

    public function testArrivalActionRenderedForScheduledAndConfirmedWhenPermitted(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // Check scheduled row Arrival form (id=101)
        $form1 = $xpath->query('//tr[@data-visit-id="101"]//form[@data-appointment-transition]');
        $this->assertSame(1, $form1->length);
        $statusInput1 = $xpath->query('.//input[@name="status"]', $form1->item(0));
        $this->assertSame('arrived', $statusInput1->item(0)->getAttribute('value'));
        $expectedInput1 = $xpath->query('.//input[@name="expected_status"]', $form1->item(0));
        $this->assertSame('scheduled', $expectedInput1->item(0)->getAttribute('value'));

        $button1 = $xpath->query('.//button[@type="submit"]', $form1->item(0));
        $this->assertSame(1, $button1->length);
        $this->assertSame('Arrival', trim($button1->item(0)->textContent));

        // Check confirmed row Arrival form (id=102)
        $form2 = $xpath->query('//tr[@data-visit-id="102"]//form[@data-appointment-transition]');
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
        $form = $xpath->query('//tr[@data-visit-id="103"]//form[@data-appointment-transition]');
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
        $form = $xpath->query('//tr[@data-visit-id="104"]//form[@data-appointment-transition]');
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

        $row105 = $xpath->query('//tr[@data-visit-id="105"]');
        $this->assertSame(1, $row105->length);

        // Encounter link
        $encounterLink = $xpath->query('.//td[contains(@class,"visit-action")]//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(1, $encounterLink->length, 'In-chair row must render encounter action link.');
        $this->assertSame('Open examination', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('examination/801', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('return_to=dashboard', $encounterLink->item(0)->getAttribute('href'));
        $this->assertSame('Open examination - InChair Patient', $encounterLink->item(0)->getAttribute('aria-label'));

        // Transition form for checkout (Ready for reception)
        $form = $xpath->query('.//td[contains(@class,"visit-action")]//form[@data-appointment-transition]', $row105->item(0));
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

        $encounterLink = $xpath->query('//tr[@data-visit-id="105"]//a[contains(@class,"encounter-btn")]');
        $this->assertSame(1, $encounterLink->length);
        $this->assertSame('Patient examinations', trim($encounterLink->item(0)->textContent));
        $this->assertStringContainsString('patient/505', $encounterLink->item(0)->getAttribute('href'));
        $this->assertStringContainsString('tab=examinations', $encounterLink->item(0)->getAttribute('href'));
        $this->assertSame('Patient examinations - InChair Patient', $encounterLink->item(0)->getAttribute('aria-label'));
    }

    public function testEncounterNoneRoutesToExaminationCreate(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('encounter_none', true, 'en');

        $encounterLink = $xpath->query('//tr[@data-visit-id="105"]//a[contains(@class,"encounter-btn")]');
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
        $forms = $xpath->query('//tr[@data-visit-id="106"]//form');
        $this->assertSame(0, $forms->length);

        $link = $xpath->query('//tr[@data-visit-id="106"]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(1, $link->length);
        $this->assertSame('View', trim($link->item(0)->textContent));
        $this->assertStringContainsString('appointment/106', $link->item(0)->getAttribute('href'));
    }

    public function testWithoutEditPermissionAllRowsRenderViewOnly(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', false, 'en');

        $forms = $xpath->query('//table[contains(@class,"visit-table")]//form');
        $this->assertSame(0, $forms->length, 'No transition forms should render without appointments.edit permission.');

        $actionLinks = $xpath->query('//table[contains(@class,"visit-table")]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(6, $actionLinks->length);
        foreach ($actionLinks as $link) {
            $this->assertSame('View', trim($link->textContent));
        }
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
        $scrollContainer = $xpath->query('//div[contains(@class,"visit-table-scroll")]');
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
            $row = $xpath->query('//tr[@data-visit-id="' . $visitId . '"]');
            $this->assertSame(1, $row->length, "Row {$visitId} must exist");
            $this->assertSame($exp['patient'], $row->item(0)->getAttribute('data-patient-name'));

            if ($exp['action'] === 'button') {
                $btn = $xpath->query('.//button[@type="submit"]', $row->item(0));
                $this->assertSame(1, $btn->length);
                $this->assertSame($exp['text'], trim($btn->item(0)->textContent));
                $this->assertSame($exp['label'], $btn->item(0)->getAttribute('aria-label'));
            } else {
                $link = $xpath->query('.//td[contains(@class,"visit-action")]//a', $row->item(0));
                $this->assertSame(1, $link->length);
                $this->assertSame($exp['text'], trim($link->item(0)->textContent));
                $this->assertSame($exp['label'], $link->item(0)->getAttribute('aria-label'));
            }
        }
    }

    public function testAccessiblePatientScopedLabelsInArabic(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'ar');

        $scrollContainer = $xpath->query('//div[contains(@class,"visit-table-scroll")]');
        $this->assertSame(1, $scrollContainer->length);
        $this->assertSame('0', $scrollContainer->item(0)->getAttribute('tabindex'));
        $this->assertSame('region', $scrollContainer->item(0)->getAttribute('role'));

        // Check scheduled row in Arabic (arrival action)
        $btn101 = $xpath->query('//tr[@data-visit-id="101"]//button[@type="submit"]');
        $this->assertSame(1, $btn101->length);
        $this->assertStringContainsString('Scheduled Patient', $btn101->item(0)->getAttribute('aria-label'));

        // Check arrived row in Arabic (ready action)
        $btn103 = $xpath->query('//tr[@data-visit-id="103"]//button[@type="submit"]');
        $this->assertSame(1, $btn103->length);
        $this->assertStringContainsString('Arrived Patient', $btn103->item(0)->getAttribute('aria-label'));

        // Check ready row in Arabic (start visit action)
        $btn104 = $xpath->query('//tr[@data-visit-id="104"]//button[@type="submit"]');
        $this->assertSame(1, $btn104->length);
        $this->assertSame('بدء الزيارة', trim($btn104->item(0)->textContent));
        $this->assertStringContainsString('Ready Patient', $btn104->item(0)->getAttribute('aria-label'));

        // Check in_chair row in Arabic (encounter action + ready for reception)
        $row105 = $xpath->query('//tr[@data-visit-id="105"]');
        $encounter105 = $xpath->query('.//a[contains(@class,"encounter-btn")]', $row105->item(0));
        $this->assertSame(1, $encounter105->length);
        $this->assertSame('فتح الفحص', trim($encounter105->item(0)->textContent));

        $btn105 = $xpath->query('.//button[@type="submit"]', $row105->item(0));
        $this->assertSame(1, $btn105->length);
        $this->assertSame('جاهز للاستقبال', trim($btn105->item(0)->textContent));

        // Check completed row in Arabic (view action)
        $link106 = $xpath->query('//tr[@data-visit-id="106"]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(1, $link106->length);
        $this->assertStringContainsString('Completed Patient', $link106->item(0)->getAttribute('aria-label'));
    }
}
