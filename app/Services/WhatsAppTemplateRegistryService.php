<?php

namespace App\Services;

use App\Models\SettingsModel;

class WhatsAppTemplateRegistryService
{
    public const SETTINGS_KEY_TRIGGER_TEMPLATES = 'whatsapp_global_trigger_templates';
    public const SETTINGS_KEY_FALLBACK_TEMPLATE = 'whatsapp_global_fallback_template';
    public const SETTINGS_KEY_TRIGGER_TOGGLES = 'whatsapp_global_trigger_toggles';
    public const FORCED_LANGUAGE = 'ar';
    public const SUGGESTED_FALLBACK_TEMPLATE = 'general_reengagement_ar_v1';
    public const SUGGESTED_FALLBACK_BODY = "مرحباً {{patient_name}}\nيسعدنا تواصلكم مع {{clinic_name}}.\nللحجز أو الاستفسار يرجى التواصل معنا على {{clinic_phone}}.";

    protected SettingsModel $settingsModel;
    protected WhatsAppTriggerCatalog $triggerCatalog;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->triggerCatalog = new WhatsAppTriggerCatalog();
    }

    public function getGlobalConfig(): array
    {
        return [
            'trigger_templates' => $this->getGlobalTriggerTemplates(),
            'fallback_template' => $this->getGlobalFallbackTemplate(),
            'trigger_toggles' => $this->getGlobalTriggerToggles(),
        ];
    }

    public function getTriggerReadiness(?array $config = null): array
    {
        $config = $config ?? $this->getGlobalConfig();
        $triggerTemplates = is_array($config['trigger_templates'] ?? null) ? $config['trigger_templates'] : [];
        $triggerToggles = is_array($config['trigger_toggles'] ?? null) ? $config['trigger_toggles'] : [];

        $readiness = [];
        foreach ($this->triggerCatalog->all() as $triggerKey => $meta) {
            $triggerConfig = is_array($triggerTemplates[$triggerKey] ?? null) ? $triggerTemplates[$triggerKey] : [];
            $errors = $this->triggerCatalog->validateTriggerTemplateConfig($triggerKey, $triggerConfig);
            $readiness[$triggerKey] = [
                'enabled' => (int) ($triggerToggles[$triggerKey] ?? ($meta['default_enabled'] ?? 1)) === 1,
                'configured' => trim((string) ($triggerConfig['name'] ?? '')) !== '',
                'valid' => $errors === [],
                'ready' => trim((string) ($triggerConfig['name'] ?? '')) !== '' && $errors === [],
                'errors' => $errors,
            ];
        }

        return $readiness;
    }

    public function validateTriggerTemplates(array $triggerTemplates): array
    {
        $normalized = $this->triggerCatalog->normalizeTriggerTemplateConfig($triggerTemplates);
        $errors = [];

        foreach ($this->triggerCatalog->all() as $triggerKey => $meta) {
            $validationErrors = $this->triggerCatalog->validateTriggerTemplateConfig(
                $triggerKey,
                is_array($normalized[$triggerKey] ?? null) ? $normalized[$triggerKey] : []
            );

            if ($validationErrors === []) {
                continue;
            }

            foreach ($validationErrors as $message) {
                $errors[] = sprintf('%s: %s', $meta['label'] ?? $triggerKey, $message);
            }
        }

        return $errors;
    }

    public function getGlobalTriggerToggles(): array
    {
        $stored = $this->settingsModel->getGlobalSetting(self::SETTINGS_KEY_TRIGGER_TOGGLES, []);
        $defaults = $this->triggerCatalog->defaultTriggerToggles();
        $stored = is_array($stored) ? $stored : [];

        $toggles = [];
        foreach ($defaults as $triggerKey => $default) {
            $toggles[$triggerKey] = (int) ($stored[$triggerKey] ?? $default) === 1 ? 1 : 0;
        }

        return $toggles;
    }

    public function isGlobalTriggerEnabled(string $triggerKey): bool
    {
        $toggles = $this->getGlobalTriggerToggles();
        if (!array_key_exists($triggerKey, $toggles)) {
            return true;
        }

        return (int) $toggles[$triggerKey] === 1;
    }

    public function getGlobalTriggerTemplates(): array
    {
        $stored = $this->settingsModel->getGlobalSetting(self::SETTINGS_KEY_TRIGGER_TEMPLATES, []);
        $normalized = $this->triggerCatalog->normalizeTriggerTemplateConfig(is_array($stored) ? $stored : []);

        foreach ($normalized as $triggerKey => $config) {
            $normalized[$triggerKey]['language'] = self::FORCED_LANGUAGE;
        }

        return $normalized;
    }

    public function getGlobalFallbackTemplate(): array
    {
        $stored = $this->settingsModel->getGlobalSetting(self::SETTINGS_KEY_FALLBACK_TEMPLATE, []);
        $stored = is_array($stored) ? $stored : [];

        return [
            'name' => trim((string) ($stored['name'] ?? '')),
            'language' => self::FORCED_LANGUAGE,
            'components' => is_array($stored['components'] ?? null) ? $stored['components'] : [],
            'suggested_name' => self::SUGGESTED_FALLBACK_TEMPLATE,
            'reference_body_text' => trim((string) ($stored['reference_body_text'] ?? self::SUGGESTED_FALLBACK_BODY)),
        ];
    }

    public function saveGlobalConfig(array $triggerTemplates, array $fallbackTemplate, array $triggerToggles = []): bool
    {
        $normalizedTriggers = $this->triggerCatalog->normalizeTriggerTemplateConfig($triggerTemplates);
        foreach ($normalizedTriggers as $triggerKey => $config) {
            $normalizedTriggers[$triggerKey]['language'] = self::FORCED_LANGUAGE;
        }

        $normalizedToggles = [];
        foreach ($this->triggerCatalog->defaultTriggerToggles() as $triggerKey => $default) {
            $normalizedToggles[$triggerKey] = (int) ($triggerToggles[$triggerKey] ?? $default) === 1 ? 1 : 0;
        }

        $normalizedFallback = [
            'name' => trim((string) ($fallbackTemplate['name'] ?? '')),
            'language' => self::FORCED_LANGUAGE,
            'components' => is_array($fallbackTemplate['components'] ?? null) ? $fallbackTemplate['components'] : [],
            'reference_body_text' => trim((string) ($fallbackTemplate['reference_body_text'] ?? self::SUGGESTED_FALLBACK_BODY)),
        ];

        $savedTriggers = $this->settingsModel->setGlobalSetting(
            self::SETTINGS_KEY_TRIGGER_TEMPLATES,
            $normalizedTriggers,
            'json',
            'Global WhatsApp trigger templates managed by control plane.'
        );

        $savedFallback = $this->settingsModel->setGlobalSetting(
            self::SETTINGS_KEY_FALLBACK_TEMPLATE,
            $normalizedFallback,
            'json',
            'Global WhatsApp fallback template managed by control plane.'
        );

        $savedToggles = $this->settingsModel->setGlobalSetting(
            self::SETTINGS_KEY_TRIGGER_TOGGLES,
            $normalizedToggles,
            'json',
            'Global WhatsApp trigger master switches managed by control plane.'
        );

        return $savedTriggers && $savedFallback && $savedToggles;
    }
}
