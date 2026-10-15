<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ProviderDisplayLabel;
use App\Services\ProviderDirectoryService;
use Tests\Support\PhaseBFixture;

/**
 * Validates distinct visible doctor identities and stable option values across
 * full create/edit/shared/global quick/patient drawer and dayboard column headers/actions.
 */
final class PR2DoctorIdentityUiTest extends PhaseBFixture
{
    private ProviderDirectoryService $directoryService;
    private array $bookingContext;
    private array $viewUserContext;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['form', 'url', 'settings', 'auth', 'navigation', 'html']);
        $this->directoryService = new ProviderDirectoryService($this->db);
        $this->bookingContext = $this->directoryService->getBookingContext($this->clinic);

        $clinicRow = $this->db->table('clinics')->where('id', $this->clinic)->get()->getRowArray() ?? [];
        $clinicRow['tagline'] = $clinicRow['tagline'] ?? 'Dental Care';
        $userRow = $this->db->table('users')->where('id', $this->actor)->get()->getRowArray() ?? [];

        $this->viewUserContext = [
            'clinic' => $clinicRow,
            'user' => (object) $userRow,
            'user_clinics' => [$clinicRow],
            'active_clinic_id' => $this->clinic,
            'current_clinic' => $clinicRow,
        ];
    }

    public function testProviderDisplayLabelPureFormatter(): void
    {
        $this->assertSame('#12', ProviderDisplayLabel::formatReference(12));
        $this->assertSame('', ProviderDisplayLabel::formatReference(0));
        $this->assertSame('', ProviderDisplayLabel::formatReference(null));

        $this->assertSame('#12 - Dr. Jane', ProviderDisplayLabel::forOption(12, 'Dr. Jane'));
        $this->assertSame('#12', ProviderDisplayLabel::forOption(12, ''));
        $this->assertSame('Dr. Jane', ProviderDisplayLabel::forOption(0, 'Dr. Jane'));

        $this->assertSame('#99 - Dr. Old (Currently Inactive)', ProviderDisplayLabel::forInactiveOption(99, 'Dr. Old', 'Currently Inactive'));
        $this->assertSame('Unassigned - Room A (Previously recorded chair)', ProviderDisplayLabel::forUnassignedOption('Room A', 'Previously recorded chair', 'Unassigned'));
        $this->assertSame('Unassigned (Previously recorded chair)', ProviderDisplayLabel::forUnassignedOption('', 'Previously recorded chair', 'Unassigned'));
    }

    public function testCreateViewRendersDistinctVisibleOptionTextsForDuplicateNamedDoctors(): void
    {
        foreach (['en', 'ar'] as $locale) {
            service('request')->setLocale($locale);
            \Config\Services::language()->setLocale($locale);

            $data = array_merge($this->viewUserContext, [
                'title' => 'New Appointment',
                'patients' => [],
                'validation' => \Config\Services::validation(),
                'loadSelect2' => true,
                'selected_patient' => null,
                'examination_id' => null,
                'clinical_procedure_id' => null,
                'appointment_type_prefill' => null,
                'chair_options' => ['Legacy room'],
                'booking_context' => $this->bookingContext,
                'requested_date' => '2026-11-09',
                'provider_id_prefill' => '',
                'return_to' => 'appointment',
            ]);

            $html = view('appointment/create', $data);
            $doc = new \DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($doc);

            $options = $xpath->query('//select[@id="providerSelect"]/option[@value!=""]');
            $this->assertSame(4, $options->length, "Must render exactly 4 provider options in {$locale}");

            $texts = [];
            $values = [];
            foreach ($options as $option) {
                /** @var \DOMElement $option */
                $val = (int) $option->getAttribute('value');
                $txt = trim($option->textContent);
                $values[] = $val;
                $texts[] = $txt;

                $this->assertStringStartsWith('#' . $val . ' - ', $txt, "Option text must start with stable #ID in {$locale}");
                $this->assertStringContainsString('Duplicate Doctor', $txt);
            }

            // Numeric IDs unchanged and match synthetic fixtures
            $this->assertSame($this->providers, $values);
            // All visible option texts must be distinct despite duplicate doctor names
            $this->assertCount(4, array_unique($texts), "Option texts must be distinct for same-named doctors in {$locale}");

            // No first doctor autoselected when prefill is empty
            $selectedOptions = $xpath->query('//select[@id="providerSelect"]/option[@value!=""][@selected]');
            $this->assertSame(0, $selectedOptions->length, "No active doctor should be autoselected by default in {$locale}");

            // Prefill selection stability
            $prefillId = $this->providers[2];
            $data['provider_id_prefill'] = $prefillId;
            $htmlPrefilled = view('appointment/create', $data);
            $docPrefilled = new \DOMDocument();
            @$docPrefilled->loadHTML('<?xml encoding="UTF-8">' . $htmlPrefilled);
            $xpathPrefilled = new \DOMXPath($docPrefilled);

            $selectedPrefill = $xpathPrefilled->query('//select[@id="providerSelect"]/option[@selected]');
            $this->assertSame(1, $selectedPrefill->length);
            $this->assertSame((string) $prefillId, $selectedPrefill->item(0)->getAttribute('value'));
        }
    }

    public function testEditViewDistinguishesDuplicateDoctorsAndPreservesInactiveAndLegacyNull(): void
    {
        $patientRow = $this->db->table('patients')->where('id', $this->patient)->get()->getRowArray() ?? [];

        foreach (['en', 'ar'] as $locale) {
            service('request')->setLocale($locale);
            \Config\Services::language()->setLocale($locale);

            $appointmentRow = [
                'id' => 888,
                'appointment_id' => 'APT-888',
                'patient_id' => $this->patient,
                'first_name' => $patientRow['first_name'] ?? 'Synthetic',
                'last_name' => $patientRow['last_name'] ?? 'Patient',
                'appointment_date' => '2026-11-09',
                'appointment_time' => '10:00:00',
                'duration' => 30,
                'appointment_type' => 'consultation',
                'chair_name' => 'Historical Chair',
                'provider_user_id' => $this->providers[1],
                'status' => 'scheduled',
                'notes' => 'Synthetic test note',
            ];

            $data = array_merge($this->viewUserContext, [
                'title' => 'Edit Appointment',
                'appointment' => $appointmentRow,
                'patients' => [$patientRow],
                'validation' => \Config\Services::validation(),
                'loadSelect2' => true,
                'chair_options' => ['Legacy room'],
                'booking_context' => $this->bookingContext,
                'return_to' => 'appointment',
            ]);

            $html = view('appointment/edit', $data);
            $doc = new \DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($doc);

            // Active doctor choices
            $options = $xpath->query('//select[@id="providerSelect"]/option[@value!=""]');
            $this->assertSame(4, $options->length);

            $texts = [];
            foreach ($options as $opt) {
                $txt = trim($opt->textContent);
                $val = (int) $opt->getAttribute('value');
                $texts[] = $txt;
                $this->assertStringStartsWith('#' . $val . ' - ', $txt);
            }
            $this->assertCount(4, array_unique($texts));

            // Selected prefill must be the current provider
            $selected = $xpath->query('//select[@id="providerSelect"]/option[@selected]');
            $this->assertSame(1, $selected->length);
            $this->assertSame((string) $this->providers[1], $selected->item(0)->getAttribute('value'));

            // Test Inactive Current Doctor: retains stored snapshot + visible stable ID
            $inactiveAppointment = $appointmentRow;
            $inactiveAppointment['provider_user_id'] = 77777;
            $inactiveAppointment['chair_name'] = 'Dr. Inactive Specialist';
            $dataInactive = $data;
            $dataInactive['appointment'] = $inactiveAppointment;

            $htmlInactive = view('appointment/edit', $dataInactive);
            $docInactive = new \DOMDocument();
            @$docInactive->loadHTML('<?xml encoding="UTF-8">' . $htmlInactive);
            $xpathInactive = new \DOMXPath($docInactive);

            $inactiveOpt = $xpathInactive->query('//select[@id="providerSelect"]/option[@value="77777"]');
            $this->assertSame(1, $inactiveOpt->length, "Must render inactive doctor option with numeric ID");
            $this->assertTrue($inactiveOpt->item(0)->hasAttribute('selected'));
            $inactiveText = trim($inactiveOpt->item(0)->textContent);
            $this->assertStringStartsWith('#77777 - ', $inactiveText);
            $this->assertStringContainsString('Dr. Inactive Specialist', $inactiveText);

            // Test Legacy NULL: clearly unassigned, not fabricated (#0 or #null forbidden), retains snapshot
            $legacyNullAppointment = $appointmentRow;
            $legacyNullAppointment['provider_user_id'] = null;
            $legacyNullAppointment['chair_name'] = 'Historical Dental Unit 3';
            $dataLegacy = $data;
            $dataLegacy['appointment'] = $legacyNullAppointment;

            $htmlLegacy = view('appointment/edit', $dataLegacy);
            $docLegacy = new \DOMDocument();
            @$docLegacy->loadHTML('<?xml encoding="UTF-8">' . $htmlLegacy);
            $xpathLegacy = new \DOMXPath($docLegacy);

            $legacyOpt = $xpathLegacy->query('//select[@id="providerSelect"]/option[@value=""]');
            // Two empty options: the placeholder option and the legacy unassigned chair option
            $this->assertGreaterThanOrEqual(2, $legacyOpt->length);
            $foundLegacyOption = false;
            foreach ($legacyOpt as $opt) {
                $t = trim($opt->textContent);
                if (str_contains($t, 'Historical Dental Unit 3')) {
                    $foundLegacyOption = true;
                    $this->assertStringNotContainsString('#', $t, "Legacy NULL must not fabricate a numeric #ID");
                    $this->assertTrue($opt->hasAttribute('selected'), "Legacy appointment must preselect legacy option");
                }
            }
            $this->assertTrue($foundLegacyOption, "Legacy unassigned stored chair option must be rendered");
        }
    }

    public function testSharedQuickModalAndPatientDrawerRenderDistinctOptions(): void
    {
        $patientRow = $this->db->table('patients')->where('id', $this->patient)->get()->getRowArray();

        foreach (['en', 'ar'] as $locale) {
            service('request')->setLocale($locale);
            \Config\Services::language()->setLocale($locale);

            // 1. Shared quick create modal
            $modalData = [
                'isRtl' => ($locale === 'ar'),
                'return_to' => 'appointment',
                'selected_patient' => $patientRow,
                'chair_options' => ['Legacy room'],
                'booking_context' => $this->bookingContext,
                'provider_id_prefill' => null,
                'requested_date' => '2026-11-09',
            ];

            $modalHtml = view('appointment/partials/quick_create_modal', $modalData);
            $docModal = new \DOMDocument();
            @$docModal->loadHTML('<?xml encoding="UTF-8">' . $modalHtml);
            $xpathModal = new \DOMXPath($docModal);

            $modalOptions = $xpathModal->query('//select[@name="provider_user_id"]/option[@value!=""]');
            $this->assertSame(4, $modalOptions->length);
            $modalTexts = [];
            foreach ($modalOptions as $opt) {
                $txt = trim($opt->textContent);
                $modalTexts[] = $txt;
                $this->assertStringStartsWith('#' . $opt->getAttribute('value') . ' - ', $txt);
            }
            $this->assertCount(4, array_unique($modalTexts));

            // 2. Global quick create modals
            $globalData = [
                'is_rtl' => ($locale === 'ar'),
                'chair_options' => ['Legacy room'],
                'booking_context' => $this->bookingContext,
                'requested_date' => '2026-11-09',
            ];
            $globalHtml = view('components/global_quick_create_modals', $globalData);
            $docGlobal = new \DOMDocument();
            @$docGlobal->loadHTML('<?xml encoding="UTF-8">' . $globalHtml);
            $xpathGlobal = new \DOMXPath($docGlobal);

            $globalOptions = $xpathGlobal->query('//select[@name="provider_user_id"]/option[@value!=""]');
            $this->assertSame(4, $globalOptions->length);
            $globalTexts = [];
            foreach ($globalOptions as $opt) {
                $txt = trim($opt->textContent);
                $globalTexts[] = $txt;
                $this->assertStringStartsWith('#' . $opt->getAttribute('value') . ' - ', $txt);
            }
            $this->assertCount(4, array_unique($globalTexts));

            // 3. Patient appointment drawer (form_fields)
            $drawerData = [
                'contextual' => true,
                'selected_patient' => $patientRow,
                'chair_options' => ['Legacy room'],
                'booking_context' => $this->bookingContext,
                'provider_id_prefill' => $this->providers[3],
                'requested_date' => '2026-11-09',
                'field_id_prefix' => 'patient_drawer_',
            ];
            $drawerHtml = view('appointment/partials/form_fields', $drawerData);
            $docDrawer = new \DOMDocument();
            @$docDrawer->loadHTML('<?xml encoding="UTF-8">' . $drawerHtml);
            $xpathDrawer = new \DOMXPath($docDrawer);

            $drawerOptions = $xpathDrawer->query('//select[@name="provider_user_id"]/option[@value!=""]');
            $this->assertSame(4, $drawerOptions->length);
            $selectedDrawer = $xpathDrawer->query('//select[@name="provider_user_id"]/option[@selected]');
            $this->assertSame(1, $selectedDrawer->length);
            $this->assertSame((string) $this->providers[3], $selectedDrawer->item(0)->getAttribute('value'));
        }
    }

    public function testDayboardHeaderAndActionsDisplayStableDoctorReference(): void
    {
        $rows = [];
        foreach ($this->providers as $i => $id) {
            $rows[] = [
                'id' => 300 + $i,
                'provider_user_id' => $id,
                'appointment_date' => '2026-11-09',
                'appointment_time' => '09:30:00',
                'duration' => 30,
                'status' => 'scheduled',
                'first_name' => 'Synthetic',
                'last_name' => 'Patient ' . $i,
            ];
        }

        foreach (['en', 'ar'] as $locale) {
            service('request')->setLocale($locale);
            \Config\Services::language()->setLocale($locale);

            $dayboardHtml = view('appointment/partials/doctor_dayboard', [
                'booking_context' => $this->bookingContext,
                'appointments' => $rows,
                'selected_date' => '2026-11-09',
            ]);

            // Save layout partial fixtures for Codex headless QA
            $fixturePath = dirname(__DIR__, 2) . '/tmp/pr2-ui-' . $locale . '.html';
            file_put_contents($fixturePath, $dayboardHtml);

            $doc = new \DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $dayboardHtml);
            $xpath = new \DOMXPath($doc);

            foreach ($this->providers as $docId) {
                // Column exists for each doctor
                $col = $xpath->query('//div[@data-provider-user-id="' . $docId . '"]');
                $this->assertSame(1, $col->length, "Column for doctor {$docId} must exist in {$locale}");

                // Distinct non-shrinking LTR reference badge adjacent to doctor name
                $badge = $xpath->query('.//span[contains(@class, "doctor-reference-badge")]', $col->item(0));
                $this->assertSame(1, $badge->length, "Doctor {$docId} must have reference badge in {$locale}");
                $this->assertSame('#' . $docId, trim($badge->item(0)->textContent));
                $this->assertSame('ltr', $badge->item(0)->getAttribute('dir'), "Reference badge must be explicitly dir='ltr'");

                // Heading contains doctor name and title includes #ID
                $h4 = $xpath->query('.//h4[contains(@class, "truncate")]', $col->item(0));
                $this->assertSame(1, $h4->length);
                $this->assertStringContainsString('Duplicate Doctor', $h4->item(0)->textContent);
                $this->assertStringStartsWith('#' . $docId . ' - ', $h4->item(0)->getAttribute('title'));

                // Visible Book action button contains visible doctor reference and accessible label
                $bookBtn = $xpath->query('.//a[@data-appointment-provider="' . $docId . '"]', $col->item(0));
                $this->assertSame(1, $bookBtn->length);
                $btnText = $bookBtn->item(0)->textContent;
                $this->assertStringContainsString('(#' . $docId . ')', $btnText, "Visible Book button must include (#ID) in {$locale}");

                $ariaLabel = $bookBtn->item(0)->getAttribute('aria-label');
                $this->assertStringContainsString('#' . $docId, $ariaLabel, "Aria-label must include doctor reference in {$locale}");
            }
        }
    }
}
