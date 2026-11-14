<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use DOMDocument;
use DOMXPath;

/**
 * DB-Free template test for dashboard/index.php
 *
 * Verifies Reception Today table enhancements:
 * - Subtitle removal per Owner instruction
 * - 7th column Actions header with 7-column grid layout
 * - Arrival and Ready action forms gated by appointments.edit
 * - View action for later stages and non-permitted roles
 * - No nested buttons inside anchors
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

    public function testSubtitleIsRemovedFromHeader(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//div[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row must not contain any subtitle paragraph.');
    }

    public function testSubtitleIsRemovedFromHeaderInArabic(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'ar');

        $this->assertStringNotContainsString('Studio.subtitle', $html);
        $this->assertStringNotContainsString('Streamlined patient flow', $html);
        $this->assertStringNotContainsString('متابعة سريعة للمرضى', $html);

        $subtitleNodes = $xpath->query('//div[contains(@class,"page-title-row")]//p');
        $this->assertSame(0, $subtitleNodes->length, 'Page title row in Arabic must not contain any subtitle paragraph.');
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

    public function testViewDetailRenderedForLaterStages(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', true, 'en');

        // InChair (id=104) and Completed (id=105) must not have transition forms
        $forms = $xpath->query('//tr[@data-visit-id="104" or @data-visit-id="105"]//form');
        $this->assertSame(0, $forms->length);

        // Both rows must have View links
        $links = $xpath->query('//tr[@data-visit-id="104" or @data-visit-id="105"]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(2, $links->length);
        foreach ($links as $link) {
            $this->assertSame('View', trim($link->textContent));
            $this->assertStringContainsString('appointment/', $link->getAttribute('href'));
        }
    }

    public function testWithoutEditPermissionAllRowsRenderViewOnly(): void
    {
        [$html, $dom, $xpath] = $this->renderDashboardView('all_stages', false, 'en');

        $forms = $xpath->query('//table[contains(@class,"visit-table")]//form');
        $this->assertSame(0, $forms->length, 'No transition forms should render without appointments.edit permission.');

        $actionLinks = $xpath->query('//table[contains(@class,"visit-table")]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(5, $actionLinks->length);
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
            '104' => ['patient' => 'InChair Patient', 'action' => 'a', 'text' => 'View', 'label' => 'View - InChair Patient'],
            '105' => ['patient' => 'Completed Patient', 'action' => 'a', 'text' => 'View', 'label' => 'View - Completed Patient'],
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

        // Check completed row in Arabic (view action)
        $link105 = $xpath->query('//tr[@data-visit-id="105"]//td[contains(@class,"visit-action")]//a');
        $this->assertSame(1, $link105->length);
        $this->assertStringContainsString('Completed Patient', $link105->item(0)->getAttribute('aria-label'));
    }
}
