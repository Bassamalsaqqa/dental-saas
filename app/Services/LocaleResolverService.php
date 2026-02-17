<?php

namespace App\Services;

use CodeIgniter\HTTP\IncomingRequest;

class LocaleResolverService
{
    /**
     * @param array<string,mixed> $options
     */
    public function resolve(IncomingRequest $request, array $options = []): string
    {
        $supported = $this->normalizeSupported($options['supported'] ?? ['en']);
        $default = $this->normalizeLocale((string) ($options['default'] ?? 'en'));
        if (!in_array($default, $supported, true)) {
            $default = $supported[0] ?? 'en';
        }

        $sessionKey = (string) ($options['session_key'] ?? 'ui_locale');
        $settingsLocale = isset($options['settings_locale']) ? $this->normalizeLocale((string) $options['settings_locale']) : '';

        $queryLocale = $this->normalizeLocale((string) $request->getGet('lang'));
        if ($queryLocale !== '' && in_array($queryLocale, $supported, true)) {
            session()->set($sessionKey, $queryLocale);
            return $queryLocale;
        }

        $sessionLocale = $this->normalizeLocale((string) (session()->get($sessionKey) ?? ''));
        if ($sessionLocale !== '' && in_array($sessionLocale, $supported, true)) {
            return $sessionLocale;
        }

        if ($settingsLocale !== '' && in_array($settingsLocale, $supported, true)) {
            return $settingsLocale;
        }

        return $default;
    }

    public function apply(IncomingRequest $request, string $locale): void
    {
        $request->setLocale($locale);
        service('language')->setLocale($locale);
    }

    public function isRtl(string $locale): bool
    {
        return in_array($this->normalizeLocale($locale), ['ar', 'fa', 'he', 'ur'], true);
    }

    /**
     * @param mixed $supported
     * @return array<int,string>
     */
    private function normalizeSupported($supported): array
    {
        if (!is_array($supported) || empty($supported)) {
            return ['en'];
        }

        $result = [];
        foreach ($supported as $item) {
            $locale = $this->normalizeLocale((string) $item);
            if ($locale !== '' && !in_array($locale, $result, true)) {
                $result[] = $locale;
            }
        }

        return $result ?: ['en'];
    }

    private function normalizeLocale(string $locale): string
    {
        return strtolower(trim($locale));
    }
}
