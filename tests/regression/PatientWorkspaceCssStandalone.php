<?php
declare(strict_types=1);
$view=file_get_contents(__DIR__.'/../../app/Views/patient/show.php');$css=file_get_contents(__DIR__.'/../../public/assets/css/patient-workspace.css');
if(hash('sha256',$css)!=='21a6b98e28852ffd4f9c095766364228bf77ef354d98346a57aaa84f687b49b6')throw new RuntimeException('Extracted non-odontogram CSS changed unexpectedly');
if(str_contains(strtolower($css),'odontogram')||str_contains($css,'<?')||str_contains(strtolower($css),'url('))throw new RuntimeException('Protected/dynamic/relative assets moved');
$a=strpos($view,'    .patient-odontogram-mini-svg {');$b=strpos($view,'</style>',$a);if(hash('sha256',substr($view,$a,$b-$a))!=='7e0cd56a57a068fffddf439002b7085666ce53ae10854e95c9d59d65b03045bc')throw new RuntimeException('Retained chart style changed');
// Owner explicitly authorized only HTML-safe JSON flags in chart data serialization.
$normalized=str_replace("\r\n","\n",$view);
$a=strpos($normalized,'const patientMiniOdontogramData =');$b=strpos($normalized,'    // Global variables for filtering',$a);if($a===false||$b===false||hash('sha256',str_replace(' | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT','',substr($normalized,$a,$b-$a)))!=='1c50a8c0af39e8f17beda090424cb2453786a0a76ca371931edcb2d49a7973a8')throw new RuntimeException('Protected odontogram source changed');
$a=strpos($normalized,'    // Odontogram functionality');$b=strpos($normalized,'</script>',$a);if($a===false||$b===false||hash('sha256',str_replace(' | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT','',substr($normalized,$a,$b-$a)))!=='4bf95e54924d7dec70b63f3014b4e1888ddd92e2ea0c3ec6cfeca4f5bfa88c7f')throw new RuntimeException('Protected odontogram source changed');
$a=strpos($normalized,'function getFDINumber(');$b=strpos($normalized,'$isRtl = !empty($is_rtl);',$a);if($a===false||$b===false||hash('sha256',str_replace(' | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT','',substr($normalized,$a,$b-$a)))!=='7d2f84682765d64e942f8dd68b870e1d8fd0267b1dcbc5c618f5520a49c194e6')throw new RuntimeException('Protected odontogram source changed');
if(!str_contains($view,"asset_version('assets/css/patient-workspace.css')"))throw new RuntimeException('Stable stylesheet delivery missing');
echo json_encode(['status'=>'PASS','checks'=>7,'extracted_bytes'=>strlen($css),'scope'=>'Exact stylesheet and retained chart style, rendering/data scripts and SVG loader preservation, not full workflow browser acceptance']),PHP_EOL;
