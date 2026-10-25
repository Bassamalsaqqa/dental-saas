<?php
// Presentation-only memoization inside this CI view render. Mutations and route filters
// always revalidate canonical permissions; no session or cross-request cache is stored.
$v4PermissionContext = implode(':', [(int) session('user_id'), (int) session('active_clinic_id'), (int) (bool) session('global_mode'), (int) (bool) session('is_impersonating')]);
$v4ViewData = $this->getData();
$v4Can = $v4ViewData['v4Can'] ?? null;
if (!is_callable($v4Can) || ($v4ViewData['v4PermissionContext'] ?? null) !== $v4PermissionContext) {
    $v4PermissionResults = [];
    $v4Can = static function (string $module, string $action) use (&$v4PermissionResults): bool {
        return $v4PermissionResults[$module . '.' . $action] ??= (bool) has_permission($module, $action);
    };
    $this->setVar('v4Can', $v4Can);
    $this->setVar('v4PermissionContext', $v4PermissionContext);
}
unset($v4ViewData);
?>
