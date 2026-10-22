<?php
declare(strict_types=1);
$view=file_get_contents(__DIR__.'/../../app/Views/patient/show.php');$css=file_get_contents(__DIR__.'/../../public/assets/css/patient-workspace.css');
if(hash('sha256',$css)!=='21a6b98e28852ffd4f9c095766364228bf77ef354d98346a57aaa84f687b49b6')throw new RuntimeException('Extracted non-odontogram CSS changed unexpectedly');
if(str_contains(strtolower($css),'odontogram')||str_contains($css,'<?')||str_contains(strtolower($css),'url('))throw new RuntimeException('Protected/dynamic/relative assets moved');
$a=strpos($view,'    .patient-odontogram-mini-svg {');$b=strpos($view,'</style>',$a);if(hash('sha256',substr($view,$a,$b-$a))!=='7e0cd56a57a068fffddf439002b7085666ce53ae10854e95c9d59d65b03045bc')throw new RuntimeException('Retained chart style changed');
$a=strpos($view,'<script>',$b);
$scripts=substr($view,$a);
// The only accepted post-extraction change is HTML-safe timeline serialization.
$timelineStart=strpos($scripts,'        allTimelineData = [');
$timelineEnd=strpos($scripts,'        filteredTimelineData = ',$timelineStart);
$timeline=substr($scripts,$timelineStart,$timelineEnd-$timelineStart);
$timeline=str_replace(' | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT','',$timeline);
$scripts=substr($scripts,0,$timelineStart).$timeline.substr($scripts,$timelineEnd);
if(hash('sha256',$scripts)!=='e194673888da3a46873654ddd59aef34153684fe68407eb562604069582783de')throw new RuntimeException('Patient/odontogram scripts changed');
if(!str_contains($view,"asset_version('assets/css/patient-workspace.css')"))throw new RuntimeException('Stable stylesheet delivery missing');
echo json_encode(['status'=>'PASS','checks'=>5,'extracted_bytes'=>strlen($css),'scope'=>'Exact stylesheet and retained chart style/script preservation, not full workflow browser acceptance']),PHP_EOL;
