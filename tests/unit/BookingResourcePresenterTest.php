<?php
namespace Tests\unit;

use PHPUnit\Framework\TestCase;

final class BookingResourcePresenterTest extends TestCase
{
    private function render(string $locale, string $scenario): \DOMDocument
    {
        $fixture = dirname(__DIR__).'/_support/fixtures/BookingPresenter.php';
        $html = shell_exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($fixture).' '.escapeshellarg($locale).' '.escapeshellarg($scenario));
        self::assertIsString($html);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        return $dom;
    }

    public function testManualBookingUsesTruthfulLabelsAndExplicitChoice(): void
    {
        foreach (['en','ar'] as $locale) {
            $dom = $this->render($locale,'legacy');
            $xp = new \DOMXPath($dom);
            self::assertSame(1,$xp->query('//select[@name="chair_name"]')->length);
            self::assertSame(0,$xp->query('//select[@name="provider_user_id"]')->length);
            self::assertSame(0,$xp->query('//select[@name="chair_name"]/option[@selected]')->length);
            self::assertSame(0,$xp->query('//a[contains(@href,"settings")]')->length,'Unauthorized settings navigation stays hidden.');
            self::assertSame($locale==='ar'?'عمود الحجز:':'Booking column:',trim(str_replace(' *','',$xp->query('//label[@for="chairName"]')->item(0)->textContent)));
        }
    }

    public function testHistoricalAndValidationColumnSelectionsArePreserved(): void
    {
        foreach (['edit'=>'Historical column','reentry'=>'Column B'] as $scenario=>$expected) {
            $xp = new \DOMXPath($this->render('en',$scenario));
            self::assertSame($expected,$xp->query('//select[@name="chair_name"]/option[@selected]')->item(0)->getAttribute('value'));
        }
    }

    public function testBookingSettingsLinkIsLocalizedForAuthorizedReaders(): void
    {
        foreach (['en'=>'Booking settings','ar'=>'إعدادات الحجز'] as $locale=>$text) {
            $xp=new \DOMXPath($this->render($locale,'settings'));
            $links=$xp->query('//a[contains(@href,"settings?tab=hours")]');
            self::assertSame(1,$links->length);
            self::assertSame($text,$links->item(0)->textContent);
            self::assertSame($locale==='ar'?'rtl':'ltr',$xp->query('//html')->item(0)->getAttribute('dir'));
        }
    }

    public function testDynamicSchedulingRetainsStableDoctorIdsAndUnavailableGuard(): void
    {
        foreach (['en','ar'] as $locale) {
            $xp = new \DOMXPath($this->render($locale,'dynamic'));
            self::assertSame(1,$xp->query('//select[@name="provider_user_id"]/option[@value="11"]')->length);
            self::assertSame(0,$xp->query('//select[@name="chair_name"]')->length);
            $xp = new \DOMXPath($this->render($locale,'unavailable'));
            self::assertSame(1,$xp->query('//select[@name="provider_user_id"][@disabled]')->length);
        }
    }

    public function testLegacyBookingColumnsDisplayDistinctionHelp(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $dom = $this->render($locale, 'hours');
            $xp = new \DOMXPath($dom);
            $help = $xp->query('//fieldset[@data-legacy-scheduling]//p[contains(@class, "settings-information")]');
            self::assertSame(1, $help->length, "Legacy booking column distinction help must be rendered in {$locale}");
            self::assertStringContainsString(
                $locale === 'ar' ? 'هذه أسماء تُدخل يدويًا لأعمدة الحجز' : 'These names are manual booking-column labels',
                $help->item(0)->textContent
            );
        }
    }

    public function testStaffProfileRendersSalaryDistinctionGuidance(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $dom = $this->render($locale, 'user-show');
            $xp = new \DOMXPath($dom);
            $guidance = $xp->query('//section[contains(@class, "staff-provider-section")]//p[contains(@class, "staff-provider-guidance")]');
            self::assertSame(1, $guidance->length, "Staff profile provider section must render salary distinction guidance in {$locale}");
            self::assertStringContainsString(
                $locale === 'ar' ? 'ادفع هنا الحصص المسجلة على معاملات المرضى' : 'Pay shares recorded on patient transactions here',
                $guidance->item(0)->textContent
            );
        }
    }
}
