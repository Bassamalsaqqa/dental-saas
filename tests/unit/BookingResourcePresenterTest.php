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
            $ui = require APPPATH.'Language/'.$locale.'/UI.php';
            self::assertSame($ui['appointmentChairLabel'],trim(str_replace(' *','',$xp->query('//label[@for="chairName"]')->item(0)->textContent)));
        }
    }

    public function testHistoricalAndValidationColumnSelectionsArePreserved(): void
    {
        foreach (['edit'=>'Historical column','reentry'=>'Column B'] as $scenario=>$expected) {
            $xp = new \DOMXPath($this->render('en',$scenario));
            self::assertSame($expected,$xp->query('//select[@name="chair_name"]/option[@selected]')->item(0)->getAttribute('value'));
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
}
