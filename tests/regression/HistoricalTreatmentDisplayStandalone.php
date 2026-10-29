<?php
declare(strict_types=1);
$locale='en';
function lang(string $key):string {global $locale;[$group,$name]=explode('.',$key,2);$labels=require dirname(__DIR__,2).'/app/Language/'.$locale.'/'.$group.'.php';return $labels[$name]??$key;}
require dirname(__DIR__,2).'/app/Libraries/TreatmentCatalog.php';
use App\Libraries\TreatmentCatalog;
$checks=0;
foreach(['en','ar']as$locale){foreach(['','not_a_recorded_procedure',TreatmentCatalog::KEEP_UNKNOWN]as$key){if(TreatmentCatalog::nameFor($key)!==lang('Treatment.historical_unknown_type'))throw new RuntimeException('Unknown display assigned a category');$checks++;}if(str_contains(lang('Treatment.historical_unknown_type'),'?')||!preg_match('//u',lang('Treatment.historical_unknown_help')))throw new RuntimeException('Broken localization');$checks++;}
if(count(TreatmentCatalog::forSelection())!==43||isset(TreatmentCatalog::all()[TreatmentCatalog::KEEP_UNKNOWN])||str_contains(TreatmentCatalog::validationRule(),TreatmentCatalog::KEEP_UNKNOWN))throw new RuntimeException('Transport choice entered clinical catalog');$checks++;
foreach(TreatmentCatalog::all()as$key=>$name){if(TreatmentCatalog::nameFor($key)!==$name)throw new RuntimeException('Existing clinical label changed');$checks++;}
echo json_encode(['passed'=>$checks,'unknown_en_ar'=>true,'offered_procedures'=>43,'keep_unknown_transport_is_not_clinical_category'=>true]),PHP_EOL;
