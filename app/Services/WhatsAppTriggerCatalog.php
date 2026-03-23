<?php

namespace App\Services;

class WhatsAppTriggerCatalog
{
    public const STRATEGY_TEMPLATE_ONLY = 'template_only';

    private function commonAvailableVars(): array
    {
        return [
            'clinic_name' => 'Clinic display name',
            'clinic_phone' => 'Clinic primary phone number',
            'clinic_address' => 'Clinic address',
        ];
    }

    private function starterBody(string $text): string
    {
        return trim($text);
    }

    public function all(): array
    {
        return [
            'appointment_reminder' => [
                'label' => 'Appointment Reminder',
                'description' => 'Sent before a scheduled appointment based on the clinic reminder lead time.',
                'suggested_template_name' => 'appointment_reminder_ar_v1',
                'reference_body_text' => $this->starterBody("مرحباً {{patient_name}}\nنذكركم بأن لديكم موعداً في {{clinic_name}} بتاريخ {{appointment_date}} الساعة {{appointment_time}}.\nللاستفسار: {{clinic_phone}}"),
                'reference_header_text' => 'تذكير بموعدك',
                'reference_button_text' => '',
                'operator_notes' => 'Use for pre-appointment reminders. Keep the patient name, date, and time in the same placeholder order approved in Meta.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ] + $this->commonAvailableVars(),
            ],
            'appointment_confirmation' => [
                'label' => 'Appointment Confirmation',
                'description' => 'Sent when an appointment is created or materially updated.',
                'suggested_template_name' => 'appointment_confirmation_ar_v1',
                'reference_body_text' => $this->starterBody("مرحباً {{patient_name}}\nتم تأكيد موعدكم في {{clinic_name}} بتاريخ {{appointment_date}} الساعة {{appointment_time}}.\nإذا احتجتم إلى تعديل الموعد يمكنكم التواصل معنا على {{clinic_phone}}."),
                'reference_header_text' => 'تأكيد الموعد',
                'reference_button_text' => '',
                'operator_notes' => 'Use when a new appointment is booked or materially changed. Keep wording simple and operational.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ] + $this->commonAvailableVars(),
            ],
            'follow_up' => [
                'label' => 'Examination Follow-Up',
                'description' => 'Sent after an examination is marked completed.',
                'suggested_template_name' => 'follow_up_ar_v1',
                'reference_body_text' => $this->starterBody("مرحباً {{patient_name}}\nشكراً لزيارتكم {{clinic_name}} بتاريخ {{examination_date}}.\nإذا احتجتم إلى متابعة أو استفسار يمكنكم التواصل معنا على {{clinic_phone}}."),
                'reference_header_text' => 'متابعة بعد الزيارة',
                'reference_button_text' => '',
                'operator_notes' => 'Use for simple post-visit follow-up. Keep it one-way and informational.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'examination_date'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'examination_date' => 'Localized examination date',
                ] + $this->commonAvailableVars(),
            ],
            'no_show_follow_up' => [
                'label' => 'No-Show Follow-Up',
                'description' => 'Sent after an appointment is marked no-show.',
                'suggested_template_name' => 'no_show_follow_up_ar_v1',
                'reference_body_text' => $this->starterBody("مرحباً {{patient_name}}\nلاحظنا عدم حضوركم لموعد {{clinic_name}} بتاريخ {{appointment_date}} الساعة {{appointment_time}}.\nإذا رغبتم بإعادة الحجز يرجى التواصل معنا على {{clinic_phone}}."),
                'reference_header_text' => 'متابعة عدم الحضور',
                'reference_button_text' => '',
                'operator_notes' => 'Use after a missed appointment. Keep the tone neutral and operational.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'appointment_date', 'appointment_time'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'appointment_date' => 'Localized appointment date',
                    'appointment_time' => 'Localized appointment time',
                ] + $this->commonAvailableVars(),
            ],
            'payment_reminder' => [
                'label' => 'Payment Reminder',
                'description' => 'Sent when an invoice still has an outstanding balance.',
                'suggested_template_name' => 'payment_reminder_ar_v1',
                'reference_body_text' => $this->starterBody("مرحباً {{patient_name}}\nيوجد رصيد مستحق بقيمة {{amount}} على فاتورة رقم {{invoice_number}} لدى {{clinic_name}}.\nتاريخ الاستحقاق: {{due_date}}.\nللاستفسار يرجى التواصل معنا على {{clinic_phone}}."),
                'reference_header_text' => 'تذكير دفعة مستحقة',
                'reference_button_text' => '',
                'operator_notes' => 'Use for one-way payment reminders. Keep amount, due date, and invoice number in the approved order.',
                'default_enabled' => 1,
                'strategy' => self::STRATEGY_TEMPLATE_ONLY,
                'body_param_keys' => ['patient_name', 'amount', 'due_date', 'invoice_number'],
                'available_vars' => [
                    'patient_name' => 'Patient full name',
                    'amount' => 'Outstanding balance with currency symbol',
                    'due_date' => 'Localized due date if present',
                    'invoice_number' => 'Invoice/public reference number',
                ] + $this->commonAvailableVars(),
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
                'reference_body_text' => (string) ($definition['reference_body_text'] ?? ''),
                'reference_header_text' => (string) ($definition['reference_header_text'] ?? ''),
                'reference_button_text' => (string) ($definition['reference_button_text'] ?? ''),
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
                'reference_body_text' => trim((string) ($current['reference_body_text'] ?? $defaultConfig['reference_body_text'])),
                'reference_header_text' => trim((string) ($current['reference_header_text'] ?? $defaultConfig['reference_header_text'])),
                'reference_button_text' => trim((string) ($current['reference_button_text'] ?? $defaultConfig['reference_button_text'])),
            ];

            if ($rawConfig[$triggerKey]['body_param_keys'] === []) {
                $rawConfig[$triggerKey]['body_param_keys'] = $defaultConfig['body_param_keys'];
            }
        }

        return $rawConfig;
    }

    public function validateTriggerTemplateConfig(string $triggerKey, array $config): array
    {
        $catalog = $this->all();
        $meta = $catalog[$triggerKey] ?? null;
        if ($meta === null) {
            return ['Unknown trigger key.'];
        }

        $errors = [];
        $availableVarKeys = array_keys((array) ($meta['available_vars'] ?? []));
        $name = trim((string) ($config['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Approved Meta template name is required.';
        }

        $headerKey = trim((string) ($config['header_param_key'] ?? ''));
        if ($headerKey !== '' && !in_array($headerKey, $availableVarKeys, true)) {
            $errors[] = sprintf('Header variable "%s" is not allowed for this trigger.', $headerKey);
        }

        $bodyKeys = $config['body_param_keys'] ?? [];
        if (!is_array($bodyKeys) || $bodyKeys === []) {
            $errors[] = 'At least one body variable is required.';
        } else {
            foreach ($bodyKeys as $bodyKey) {
                $cleanKey = trim((string) $bodyKey);
                if ($cleanKey === '' || !in_array($cleanKey, $availableVarKeys, true)) {
                    $errors[] = sprintf('Body variable "%s" is not allowed for this trigger.', $cleanKey);
                }
            }
        }

        $buttonType = trim((string) ($config['button_type'] ?? ''));
        $buttonParamKey = trim((string) ($config['button_param_key'] ?? ''));
        if ($buttonType !== '' && $buttonParamKey === '') {
            $errors[] = 'Button variable is required when button type is enabled.';
        }
        if ($buttonParamKey !== '' && !in_array($buttonParamKey, $availableVarKeys, true)) {
            $errors[] = sprintf('Button variable "%s" is not allowed for this trigger.', $buttonParamKey);
        }

        return array_values(array_unique($errors));
    }
}
