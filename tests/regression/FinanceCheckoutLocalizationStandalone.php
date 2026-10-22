<?php
declare(strict_types=1);
$keys = ['checkoutTitle','checkoutHelp','checkoutAmount','checkoutUnavailable','checkoutInvalid','checkoutAlreadyRecorded','checkoutBaseRate','checkoutFailed'];
$checks = 0;
foreach (['en','ar'] as $locale) {
    $path = __DIR__ . '/../../app/Language/' . $locale . '/Finance.php';
    $bytes = file_get_contents($path);
    if (!preg_match('//u', $bytes) || str_starts_with($bytes, "\xef\xbb\xbf")) { throw new RuntimeException('Finance locale must be UTF-8 without a BOM: ' . $locale); }
    $checks++;
    ob_start(); $messages = require $path; $emitted = ob_get_clean();
    if ($emitted !== '') { throw new RuntimeException('Locale emitted bytes before HTTP headers'); }
    $checks++;
    foreach ($keys as $key) {
        $message = $messages[$key] ?? '';
        if ($message === '' || str_contains($message, '???') || str_contains($message, "\xef\xbf\xbd") || ($locale === 'ar' && !preg_match('/\p{Arabic}/u', $message))) { throw new RuntimeException('Corrupt checkout message: ' . $locale . ':' . $key); }
        $checks++;
    }
}
echo json_encode(['status'=>'PASS','checks'=>$checks,'scope'=>'Actual EN/AR checkout translations, UTF-8 and zero output when loading finance locales']), PHP_EOL;
