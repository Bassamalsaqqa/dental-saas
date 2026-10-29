<?php
// First-party outline geometry. No icon font or remote runtime.
$studioIcon = static function (string $name, string $class = ''): string {
    $paths = [
        'more'=>'<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
        'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/>',
        'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h1m6 0h1m-8 3h1"/>',
        'people'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3m1-16a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"/>',
        'chart'=>'<path d="M5 3h14v18H5zM9 7h6m-6 4h6m-6 4h3"/><path d="M3 7h2m-2 4h2m-2 4h2"/>',
        'tooth'=>'<path d="M12 5C8 1 3 4 4 9c1 4 3 13 5 12 2-1 0-8 3-8s1 7 3 8c2 1 4-8 5-12 1-5-4-8-8-4Z"/>',
        'pill'=>'<path d="m5 13 8-8a5 5 0 0 1 7 7l-8 8a5 5 0 0 1-7-7Zm4-4 7 7"/>',
        'wallet'=>'<path d="M20 7H5a2 2 0 0 1 0-4h13v4m2 0v14H5a2 2 0 0 1-2-2V5m18 7h-6v5h6Z"/>',
        'box'=>'<path d="m12 3 9 5v10l-9 4-9-4V8l9-5Zm0 9v10M3 8l9 4 9-4M7 5.8l9 4.5"/>',
        'report'=>'<path d="M4 3v18h17M8 16v-5m5 5V6m5 10V9"/>',
        'settings'=>'<path d="M4 7h16M4 17h16"/><circle cx="8" cy="7" r="3"/><circle cx="16" cy="17" r="3"/>',
        'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M9 21h6"/>',
        'plus'=>'<path d="M12 5v14M5 12h14"/>',
        'chevron'=>'<path d="m9 5 7 7-7 7"/>',
        'down'=>'<path d="m6 9 6 6 6-6"/>',
        'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'close'=>'<path d="m6 6 12 12M6 18 18 6"/>',
        'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
        'check'=>'<path d="m5 12 4 4L19 6"/>',
        'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'globe'=>'<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>',
        'help'=>'<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 1 1 4 3v2m-1 3h.01"/>',
        'logout'=>'<path d="M9 4H4v16h5m5-8h7m-3-3 3 3-3 3"/>',
        'clinic'=>'<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 7v10M7 12h10"/>',
        'filter'=>'<path d="M4 7h16M7 12h10m-7 5h4"/>',
        'shield'=>'<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',
    ];
    return '<svg class="si '.esc($class,'attr').'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['chart']).'</svg>';
};
$studioInitials = static function (string $name): string {
    $parts=preg_split('/\s+/u',trim($name),-1,PREG_SPLIT_NO_EMPTY);
    return mb_strtoupper(implode('',array_map(static fn($part)=>mb_substr($part,0,1),array_slice($parts ?: [],0,2)))) ?: 'D';
};
?>