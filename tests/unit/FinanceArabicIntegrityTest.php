<?php
namespace Tests\unit;

use PHPUnit\Framework\TestCase;

final class FinanceArabicIntegrityTest extends TestCase
{
    public function testArabicSourcesAndValuesHaveNoEncodingReplacements(): void
    {
        $problems=[];
        foreach (['Finance','Studio','UI'] as $group) {
            $path=dirname(__DIR__,2).'/app/Language/ar/'.$group.'.php';
            self::assertSame(1,preg_match('//u',file_get_contents($path)), $group.' must be valid UTF-8.');
            $values=require $path;
            foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($values)) as $key=>$value) {
                if(is_string($value)&&preg_match('/\?{2,}|\x{FFFD}/u',$value))$problems[]=$group.'.'.$key;
            }
        }
        self::assertSame([],$problems,'Translation values contain encoding replacements.');
    }

    public function testAuditedArabicLabelsHaveTheirIntendedText(): void
    {
        $expected=['Finance'=>['addProviderShare'=>'إضافة حصة لمقدم خدمة'],'Studio'=>['bookingChairWhen'=>'عمود الحجز والموعد'],'UI'=>['appointmentBookingSettings'=>'إعدادات الحجز','appointmentChairLabel'=>'عمود الحجز:']];
        foreach($expected as $group=>$keys){
            $values=require dirname(__DIR__,2).'/app/Language/ar/'.$group.'.php';
            $source=file_get_contents(dirname(__DIR__,2).'/app/Language/ar/'.$group.'.php');
            foreach($keys as $key=>$text){
                self::assertSame($text,$values[$key],$group.'.'.$key);
                self::assertSame(1,substr_count($source,"'".$key."'"),$group.'.'.$key.' must not be silently overridden by a duplicate key.');
            }
        }
    }
}
