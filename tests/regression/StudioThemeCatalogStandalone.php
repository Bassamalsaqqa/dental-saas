<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/app/Services/StudioThemeCatalog.php';
use App\Services\StudioThemeCatalog as Catalog;

$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
function rejects(array $payload, string $message): void {
    try { Catalog::normalize($payload); } catch (DomainException $e) { check(true, $message); return; }
    throw new RuntimeException($message);
}
check(count(Catalog::presets()) === 5, 'Five curated presets');
$grouped=array_merge(...array_values(Catalog::roleGroups()));
check(count(array_unique($grouped))===count($grouped), 'No duplicated editor roles');
check(array_diff(Catalog::roles(),$grouped)===[] && array_diff($grouped,Catalog::roles())===[], 'All semantic roles grouped exactly once');
foreach (Catalog::presets() as $id => $_) {
    $theme = Catalog::normalize(['preset'=>$id]);
    check($theme === Catalog::normalize($theme), 'Canonical roundtrip '.$id);
    check(count($theme['colors']) === count(Catalog::roles()), 'Complete semantic roles '.$id);
    foreach (Catalog::contrastPairs() as $pair => [$fg,$bg,$minimum]) {
        check(Catalog::contrast($theme['colors'][$fg], $theme['colors'][$bg]) >= $minimum, 'Real pair '.$id.' '.$pair);
    }
    $css = Catalog::css($theme);
    check(!str_contains($css, '<') && !str_contains($css, 'url(') && substr_count($css, '{') === 1, 'Safe declaration map '.$id);
}
check(abs(Catalog::contrast('#000000','#ffffff')-21.0)<0.000001, 'Reference black-white ratio');
check(Catalog::contrast('#445566','#445566') === 1.0, 'Identical color reference');
check(Catalog::normalize(['colors'=>['ink'=>'#102E27']])['colors']['ink']==='#102e27', 'Canonical HEX');
foreach (['red','#fff','#ffffff00',' #102e27','#102e27 ','#102e27;display:none','url(https://example.invalid)','</style><script>bad</script>',[],123,null] as $value) {
    rejects(['colors'=>['ink'=>$value]], 'Reject non-HEX input');
}
foreach ([['colors'=>['--evil'=>'#123456']],['colors'=>['ink;display:none'=>'#123456']],['colors'=>'#123456'],['css'=>'body{}'],['preset'=>'unknown'],['preset'=>[]],['schema'=>2],['schema'=>'1'],['density'=>'tiny'],['text_size'=>'9px']] as $payload) {
    rejects($payload, 'Reject unknown property or enum');
}
rejects(['colors'=>['ink'=>'#f3f7ef']], 'Block unreadable body');
rejects(['colors'=>['on-frame'=>'#153f36']], 'Block unreadable navigation');
rejects(['colors'=>['focus'=>'#f3f7ef']], 'Block invisible focus');
rejects(['colors'=>['danger'=>'#edd0c5']], 'Block unreadable clinical warning');
rejects(['colors'=>['control-border'=>'#e1ebdb']], 'Block invisible control boundary');
$large = Catalog::normalize(['density'=>'compact','text_size'=>'large']);
check(str_contains(Catalog::css($large), '--grid-row-height:80px'), 'Bounded density');
check(str_contains(Catalog::css($large), '--text-body:1.25rem'), 'Bounded readable scale');
// Existing semantic fallback is still authoritative. Additional new roles do not change it implicitly.
$source = file_get_contents(dirname(__DIR__,2).'/public/assets/css/studio-theme.css');
preg_match_all('/--color-([a-z-]+):\s*(#[a-fA-F0-9]{6});/', $source, $matches, PREG_SET_ORDER);
foreach ($matches as $match) check(Catalog::palette('studio-forest')[$match[1]] === strtolower($match[2]), 'No fallback drift '.$match[1]);
echo json_encode(['passed'=>$checks,'presets'=>5,'pairs_per_preset'=>count(Catalog::contrastPairs()),'scope'=>'Pure palette/input/contrast contract; not browser or persistence acceptance']), PHP_EOL;
