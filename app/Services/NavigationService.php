<?php

namespace App\Services;

class NavigationService
{
    private const SESSION_KEY = 'nav_trail';
    private const MAX_STACK = 20;

    private array $skipSegments = ['auth', 'api', 'clinic', 'language', 'webhooks', 'assets', 'files'];

    public function push(string $label, string $url): void
    {
        $trail = $this->trail();
        $last = end($trail);
        if ($last && $last['url'] === $url) {
            return;
        }

        $trail[] = ['label' => $label, 'url' => $url, 'ts' => time()];

        if (count($trail) > self::MAX_STACK) {
            $trail = array_slice($trail, -self::MAX_STACK);
        }

        session()->set(self::SESSION_KEY, $trail);
    }

    public function pop(): ?array
    {
        $trail = $this->trail();
        array_pop($trail);
        session()->set(self::SESSION_KEY, $trail);

        $last = end($trail);
        return $last ?: null;
    }

    public function previous(): ?array
    {
        $trail = $this->trail();
        if (count($trail) < 2) {
            return null;
        }
        return $trail[count($trail) - 2] ?? null;
    }

    public function previousSafe(string $fallback = '/', ?string $explicitReturnTo = null, array $excludePatterns = []): string
    {
        $explicit = $this->normalizeInternalTarget($explicitReturnTo);
        if ($explicit !== null && !$this->isWorkflowUrl($explicit) && !$this->matchesAny($explicit, $excludePatterns)) {
            return base_url($explicit);
        }

        $trail = $this->trail();
        $last = end($trail);
        if ($last && ($this->relativePath((string) ($last['url'] ?? '')) === $this->relativePath((string) service('request')->getUri()))) {
            // Non-workflow GET pages are tracked before rendering. Back targets come from earlier entries.
            array_pop($trail);
        }

        for ($i = count($trail) - 1; $i >= 0; $i--) {
            $url = (string) ($trail[$i]['url'] ?? '');
            if ($url === '' || $this->isWorkflowUrl($url) || $this->matchesAny($url, $excludePatterns)) {
                continue;
            }

            return $url;
        }

        return base_url($fallback);
    }

    public function withReturnTo(string $target, ?string $returnTo): string
    {
        $url = (strpos($target, '://') !== false || strpos($target, '//') === 0)
            ? $target
            : base_url(ltrim($target, '/'));

        if (preg_match('#([?&])return_to=#', $url)) {
            return $url;
        }

        $returnTo = $this->normalizeInternalTarget($returnTo);
        if ($returnTo === null || $this->isWorkflowUrl($returnTo)) {
            return $url;
        }

        if ($this->relativePath($url) === $returnTo) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['return_to' => $returnTo], '', '&', PHP_QUERY_RFC3986);
    }

    public function currentTarget(): ?string
    {
        $uri = service('request')->getUri();
        $target = $this->relativePath((string) $uri);

        return $this->normalizeInternalTarget($target);
    }

    public function completeWorkflow(): void
    {
        $trail = $this->trail();

        while ($trail !== []) {
            $last = end($trail);
            $url = (string) ($last['url'] ?? '');
            if (!$this->isWorkflowUrl($url)) {
                break;
            }

            array_pop($trail);
        }

        session()->set(self::SESSION_KEY, $trail);
    }

    public function isWorkflowUrl(string $url): bool
    {
        $path = strtok($this->relativePath($url), '?') ?: '';
        if ($path === '') {
            return false;
        }

        return (bool) preg_match(
            '#(^|/)(create|edit|store|update|delete|destroy)(/|$)#i',
            $path
        );
    }

    public function trail(): array
    {
        return session()->get(self::SESSION_KEY) ?? [];
    }

    public function shouldSkip(): bool
    {
        $uri = service('uri');
        $segments = $uri->getSegments();
        $first = $segments[0] ?? '';
        if (in_array($first, $this->skipSegments, true)) {
            return true;
        }
        if (service('request')->isAJAX()) {
            return true;
        }
        if (service('request')->getMethod() !== 'get') {
            return true;
        }
        if ($this->isWorkflowUrl(current_url())) {
            return true;
        }
        return false;
    }

    /** Language switching accepts local URLs only; never trust Host or Referer. */
    public function localReturnUrl($target, string $fallback = '/'): string
    {
        // App's development host detection is useful for previews, but is not redirect authority.
        $configured = env('app.baseURL');
        $base = is_string($configured) ? parse_url($configured) : false;
        $fallbackUrl = '/' . ltrim($fallback, '/');
        if ($base === false || !isset($base['scheme'], $base['host'])
            || isset($base['user']) || isset($base['pass'])
            || !in_array(strtolower($base['scheme']), ['http', 'https'], true)) {
            return $fallbackUrl;
        }
        $scheme = config('App')->forceGlobalSecureRequests ? 'https' : strtolower($base['scheme']);
        $origin = $scheme . '://' . $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');
        $basePath = rtrim($base['path'] ?? '', '/');
        $fallbackUrl = $origin . $basePath . '/' . ltrim($fallback, '/');
        if (!is_string($target) || $target === '' || strlen($target) > 4096
            || preg_match('/[\x00-\x20\x7f]/', $target) || str_contains($target, '\\')
            || str_starts_with($target, '//')) {
            return $fallbackUrl;
        }
        $parts = parse_url($target);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) { return $fallbackUrl; }
        $port = (int) ($base['port'] ?? ($scheme === 'https' ? 443 : 80));
        if (isset($parts['scheme']) || isset($parts['host'])) {
            $targetScheme = strtolower($parts['scheme'] ?? '');
            $targetPort = (int) ($parts['port'] ?? ($targetScheme === 'https' ? 443 : 80));
            if ($targetScheme !== $scheme || strtolower($parts['host'] ?? '') !== strtolower($base['host']) || $targetPort !== $port) {
                return $fallbackUrl;
            }
        }
        $path = $parts['path'] ?? '';
        if (!str_starts_with($path, '/')) { $path = $basePath . '/' . $path; }
        // Browsers normalize encoded dots/slashes; reject ambiguous traversal, not just literal ../.
        $decoded = $path;
        for ($i = 0; $i < 5; $i++) {
            if (preg_match('/[\x00-\x1f\x7f]/', $decoded) || str_contains($decoded, '\\')
                || str_contains($decoded, '//') || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)) {
                return $fallbackUrl;
            }
            $next = rawurldecode($decoded);
            if ($next === $decoded) { break; }
            if ($i === 4) { return $fallbackUrl; }
            $decoded = $next;
        }
        if (($basePath !== '' && $decoded !== $basePath && !str_starts_with($decoded, $basePath . '/'))
            || preg_match('/%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)/i', $target)) {
            return $fallbackUrl;
        }
        return $origin . $path . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    private function normalizeInternalTarget(?string $target): ?string
    {
        $target = trim((string) $target);
        if ($target === '') {
            return null;
        }

        if (strpos($target, '://') !== false || strpos($target, '//') === 0) {
            return null;
        }

        $target = ltrim($target, '/');
        if (!preg_match('#^[a-zA-Z0-9/_\\-?=&.]+$#', $target)) {
            return null;
        }

        return $target;
    }

    private function relativePath(string $url): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        $query = (string) (parse_url($url, PHP_URL_QUERY) ?? '');
        $basePath = trim((string) (parse_url(base_url(), PHP_URL_PATH) ?? ''), '/');
        $path = trim($path, '/');

        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath) + 1);
        } elseif ($basePath !== '' && $path === $basePath) {
            $path = '';
        }

        return $query === '' ? $path : $path . '?' . $query;
    }

    private function matchesAny(string $url, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern !== '' && @preg_match($pattern, $url)) {
                if (preg_match($pattern, $url)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function nextActions(string $currentRoute, array $context = []): array
    {
        $actions = [];

        // Patient detail → clinical actions
        if (preg_match('#^patient/\d+$#', $currentRoute)) {
            $id = $context['patient_id'] ?? null;
            if ($id) {
                if (has_permission('appointments', 'create')) {
                    $actions[] = ['label' => lang('UI.patientBookAppointment'), 'icon' => 'fa-calendar-plus', 'url' => base_url("appointment/create?patient_id={$id}")];
                }
                if (has_permission('examinations', 'create')) {
                    $actions[] = ['label' => lang('UI.patientStartAssessment'), 'icon' => 'fa-stethoscope', 'url' => base_url("examination/create?patient_id={$id}")];
                }
                if (has_permission('finance', 'create')) {
                    $actions[] = ['label' => lang('UI.patientCreateInvoice'), 'icon' => 'fa-file-invoice-dollar', 'url' => base_url("finance/create?patient_id={$id}")];
                }
            }
            return $actions;
        }

        // Examination → treatment plan / treatment / follow-up
        if (preg_match('#^examination/\d+$#', $currentRoute)) {
            $examId = $context['examination_id'] ?? null;
            $patientId = $context['patient_id'] ?? null;
            if ($examId && $patientId) {
                if (has_permission('treatments', 'create')) {
                    $actions[] = ['label' => lang('Examination.add_treatment_plan'), 'icon' => 'fa-file-signature', 'url' => base_url("contracts/create/{$patientId}?examination_id={$examId}")];
                    $actions[] = ['label' => lang('Examination.add_treatment'), 'icon' => 'fa-plus', 'url' => base_url("treatment/create?examination_id={$examId}")];
                }
                if (has_permission('appointments', 'create')) {
                    $actions[] = ['label' => lang('Examination.schedule_follow_up'), 'icon' => 'fa-calendar-plus', 'url' => base_url("appointment/create?patient_id={$patientId}&appointment_type=follow_up")];
                }
            }
            return $actions;
        }

        // Treatment → completion / printing
        if (preg_match('#^treatment/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Treatment.mark_as_completed'), 'icon' => 'fa-check-circle', 'url' => '#'];
            $actions[] = ['label' => lang('Treatment.print_treatment'), 'icon' => 'fa-print', 'url' => '#'];
            return $actions;
        }

        // Finance → payment / refund / export
        if (preg_match('#^finance/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Finance.recordInvoicePayment'), 'icon' => 'fa-plus-circle', 'url' => '#'];
            $actions[] = ['label' => lang('Finance.exportPdf'), 'icon' => 'fa-file-pdf', 'url' => '#'];
            return $actions;
        }

        // Prescription → print
        if (preg_match('#^prescription/\d+$#', $currentRoute)) {
            $actions[] = ['label' => lang('Prescription.print_prescription'), 'icon' => 'fa-print', 'url' => '#'];
            return $actions;
        }

        return $actions;
    }
}
