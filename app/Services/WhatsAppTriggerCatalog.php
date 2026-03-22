<?php

namespace App\Services;

class WhatsAppTriggerCatalog
{
    public const STRATEGY_TEMPLATE_ONLY = 'template_only';

    public function all(): array
    {
        return [
            'appointment_reminder' => [
                'label' => 'Appointment Reminder',
                'description' => 'Sent before a scheduled appointment based on the clinic reminder lead time.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ],
            ],
            'appointment_confirmation' => [
                'label' => 'Appointment Confirmation',
                'description' => 'Sent when an appointment is created or materially updated.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ],
            ],
            'follow_up' => [
                'label' => 'Examination Follow-Up',
                'description' => 'Sent after an examination is marked completed.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'examination_date'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'examination_date' => 'Localized examination date',
                ],
            ],
            'no_show_follow_up' => [
                'label' => 'No-Show Follow-Up',
                'description' => 'Sent after an appointment is marked no-show.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ],
            ],
            'payment_reminder' => [
                'label' => 'Payment Reminder',
                'description' => 'Sent when an invoice still has an outstanding balance.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'amount', 'due_date', 'invoice_number'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'amount' => 'Outstanding balance with currency symbol',
                    'due_date' => 'Localized due date if present',
                    'invoice_number' => 'Invoice/public reference number',
                ],
            ],
        ];
    }

    public function defaultTriggerToggles(): array
    {
        $toggles = [];
        foreach ($this->all() as $key => $definition) {
            $toggles[$key] = (int) ($definition['default_enabled'] ?? 1);
        }

        return $toggles;
    }

    public function defaultTriggerTemplateConfig(): array
    {
        $templates = [];

        foreach ($this->all() as $key => $definition) {
            $templates[$key] = [
                'name' => '',
                'language' => 'ar',
                'strategy' => (string) ($definition['strategy'] ?? self::STRATEGY_TEMPLATE_ONLY),
                'header_param_key' => '',
                'body_param_keys' => array_values($definition['body_param_keys'] ?? []),
                'button_type' => '',
                'button_index' => '0',
                'button_param_key' => '',
            ];
        }

        return $templates;
    }

    public function normalizeTriggerTemplateConfig($rawConfig): array
    {
        $defaults = $this->defaultTriggerTemplateConfig();
        $rawConfig = is_array($rawConfig) ? $rawConfig : [];

        foreach ($defaults as $triggerKey => $defaultConfig) {
            $current = is_array($rawConfig[$triggerKey] ?? null) ? $rawConfig[$triggerKey] : [];
            $bodyParamKeys = $current['body_param_keys'] ?? $defaultConfig['body_param_keys'];
            if (!is_array($bodyParamKeys)) {
                $bodyParamKeys = $defaultConfig['body_param_keys'];
            }

            $rawConfig[$triggerKey] = [
                'name' => trim((string) ($current['name'] ?? $defaultConfig['name'])),
                'language' => trim((string) ($current['language'] ?? $defaultConfig['language'])) ?: 'ar',
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'header_param_key' => trim((string) ($current['header_param_key'] ?? $defaultConfig['header_param_key'])),
                'body_param_keys' => array_values(array_filter(array_map(
                    static fn($value): string => trim((string) $value),
                    $bodyParamKeys
                ), static fn(string $value): bool => $value !== '')),
                'button_type' => in_array(($current['button_type'] ?? ''), ['quick_reply', 'url'], true)
                    ? (string) $current['button_type']
                    : '',
                'button_index' => ctype_digit((string) ($current['button_index'] ?? ''))
                    ? (string) ($current['button_index'] ?? '0')
                    : '0',
                'button_param_key' => trim((string) ($current['button_param_key'] ?? $defaultConfig['button_param_key'])),
            ];

            if ($rawConfig[$triggerKey]['body_param_keys'] === []) {
                $rawConfig[$triggerKey]['body_param_keys'] = $defaultConfig['body_param_keys'];
            }
        }

        return $rawConfig;
    }
}
