# CP-UX-02 Verification

## Scope
Control plane navigation & IA alignment (nav groups, onboarding entry, plan request anchor, distinct filters).

## Evidence

### Navigation groups + onboarding route alignment
Command:
`rg -n "Platform|Tenants|Plans & Billing|Templates|Governance|System|controlplane/clinic/create|plan-requests" app\\Views\\layouts\\main_control_plane.php app\\Views\\control_plane`

Output:
```
app\\Views\\layouts\\main_control_plane.php:16:            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
app\\Views\\layouts\\main_control_plane.php:72:                                'label' => 'Platform',
app\\Views\\layouts\\main_control_plane.php:78:                                'label' => 'Tenants',
app\\Views\\layouts\\main_control_plane.php:81:                                    ['title' => 'Onboarding', 'url' => 'controlplane/clinic/create', 'icon' => 'fas fa-user-plus', 'color' => 'emerald'],
app\\Views\\layouts\\main_control_plane.php:85:                                'label' => 'Plans & Billing',
app\\Views\\layouts\\main_control_plane.php:88:                                    ['title' => 'Plan Requests', 'url' => 'controlplane/plans#plan-requests', 'icon' => 'fas fa-inbox', 'color' => 'indigo'],
app\\Views\\layouts\\main_control_plane.php:92:                                'label' => 'Templates',
app\\Views\\layouts\\main_control_plane.php:94:                                    ['title' => 'Email Templates', 'url' => 'controlplane/settings#template-editor', 'icon' => 'fas fa-envelope-open-text', 'color' => 'teal'],
app\\Views\\layouts\\main_control_plane.php:98:                                'label' => 'Governance',
app\\Views\\layouts\\main_control_plane.php:104:                                'label' => 'System',
app\\Views\\control_plane\\dashboard.php:103:                    <a href="<?= base_url('controlplane/clinic/create') ?>" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-4 text-emerald-700 font-semibold">
app\\Views\\control_plane\\operations.php:62:            <a href="<?= base_url('controlplane/clinic/create') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-indigo-100 bg-indigo-50 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
app\\Views\\control_plane\\plans.php:5:    <div class="glass-card rounded-3xl p-6 md:p-8" id="plan-requests">
app\\Views\\control_plane\\plans.php:191:        <form method="get" action="<?= base_url('controlplane/plans#plan-requests') ?>" class="mt-6 grid grid-cols-1 lg:grid-cols-[2fr,1fr,auto] gap-4 items-end">
app\\Views\\control_plane\\plans.php:209:                <a href="<?= base_url('controlplane/plans#plan-requests') ?>" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">Reset</a>
app\\Views\\control_plane\\clinics.php:14:            <a href="<?= base_url('controlplane/clinic/create') ?>"
app\\Views\\control_plane\\onboarding\\create_clinic.php:41:            <form action="<?= base_url('controlplane/clinic/create') ?>" method="POST" class="p-6 space-y-6">
```

### Distinct request filters
Command:
`rg -n "request_status|request_q" app\\Controllers\\ControlPlane\\Plans.php app\\Controllers\\ControlPlane\\PlanRequests.php app\\Views\\control_plane\\plans.php`

Output:
```
app\\Controllers\\ControlPlane\\Plans.php:68:        $requestStatus = strtolower(trim((string) ($request ? $request->getGet('request_status') : '')));
app\\Controllers\\ControlPlane\\Plans.php:69:        $requestSearch = trim((string) ($request ? $request->getGet('request_q') : ''));
app\\Views\\control_plane\\plans.php:194:                <input type="text" name="request_q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="Clinic or plan name"
app\\Views\\control_plane\\plans.php:200:                <select name="request_status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
app\\Controllers\\ControlPlane\\PlanRequests.php:31:        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('request_status') : '')));
app\\Controllers\\ControlPlane\\PlanRequests.php:32:        $search = trim((string) ($request ? $request->getGet('request_q') : ''));
```
