<?php
// One resolution per shell. No theme SQL or changed first paint while the feature is OFF.
if (!config('StudioThemes')->enabled) return;
$themeClinicId = null;
if (($studioThemePlane ?? 'public') === 'tenant') {
    $candidate = (int)session('active_clinic_id');
    // TenantFilter revalidates this role on every protected tenant request; branding data has no clinic ID.
    $matchesClinic = $candidate > 0 && (int)($studioActor->id ?? 0) === (int)session('user_id');
    $verifiedTenant = (int)session('active_clinic_role_id') > 0;
    $verifiedImpersonation = isset($studioImpersonating) && $studioImpersonating === true;
    if (!$matchesClinic || (int)session('user_id') <= 0 || session('global_mode') === true || (!$verifiedTenant && !$verifiedImpersonation)) return;
    $themeClinicId = $candidate;
}
try {
    $themeStore = new \App\Services\StudioThemeStore(\Config\Database::connect(), true);
    $effectiveTheme = $themeStore->load($themeClinicId);
    $themeCss = \App\Services\StudioThemeCatalog::css($effectiveTheme['theme']);
    $themeNonce = config('App')->CSPEnabled ? service('response')->getCSP()->getStyleNonce() : '';
    echo '<style id="studio-effective-theme" data-theme="'.esc($effectiveTheme['theme']['preset'],'attr').'"'.($themeNonce!==''?' nonce="'.esc($themeNonce,'attr').'"':'').'>'.$themeCss.'</style>';
} catch (\Throwable $error) {
    // Invalid/missing configuration cannot break clinical access. Never echo stored payloads or SQL errors.
    log_message('error','Theme resolution failed safely: {type}',['type'=>get_class($error)]);
}
