<?php
namespace App\Services;

/** The same Meta component mapping is used by producers and claim validation. */
final class WorkflowTemplatePayload
{
    public static function components(array $triggerConfig, array $templateVars): array
    {
        $components = [];

        $headerParamKey = trim((string) ($triggerConfig['header_param_key'] ?? ''));
        if ($headerParamKey !== '' && array_key_exists($headerParamKey, $templateVars)) {
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'text',
                    'text' => (string) $templateVars[$headerParamKey],
                ]],
            ];
        }

        $bodyParamKeys = $triggerConfig['body_param_keys'] ?? [];
        if (is_array($bodyParamKeys) && $bodyParamKeys !== []) {
            $parameters = [];
            foreach ($bodyParamKeys as $key) {
                $cleanKey = trim((string) $key);
                if ($cleanKey === '' || !array_key_exists($cleanKey, $templateVars)) {
                    continue;
                }

                $parameters[] = [
                    'type' => 'text',
                    'text' => (string) $templateVars[$cleanKey],
                ];
            }

            if ($parameters !== []) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $parameters,
                ];
            }
        }

        $buttonParamKey = trim((string) ($triggerConfig['button_param_key'] ?? ''));
        $buttonType = trim((string) ($triggerConfig['button_type'] ?? ''));
        if ($buttonParamKey !== '' && $buttonType !== '' && array_key_exists($buttonParamKey, $templateVars)) {
            $parameterType = $buttonType === 'url' ? 'text' : 'payload';
            $parameter = [
                'type' => $parameterType,
            ];
            $parameter[$parameterType === 'text' ? 'text' : 'payload'] = (string) $templateVars[$buttonParamKey];

            $components[] = [
                'type' => 'button',
                'sub_type' => $buttonType,
                'index' => ctype_digit((string) ($triggerConfig['button_index'] ?? '0')) ? (string) $triggerConfig['button_index'] : '0',
                'parameters' => [$parameter],
            ];
        }

        return $components;
    }

    public static function matches(array $payload, array $config, array $vars): bool
    {
        return ($payload['template_source'] ?? null) === 'workflow_trigger'
            && ($payload['template_name'] ?? null) === trim((string) ($config['name'] ?? ''))
            && trim((string) ($config['name'] ?? '')) !== ''
            && ($payload['template_language'] ?? null) === (trim((string) ($config['language'] ?? 'ar')) ?: 'ar')
            && self::varsMatch($payload['template_vars'] ?? null, $vars)
            && ($payload['template_components'] ?? null) === self::components($config, $vars);
    }
    private static function varsMatch(mixed $actual, array $expected): bool
    {
        if (!is_array($actual) || count($actual) !== count($expected) || array_diff_key($actual, $expected) !== []) { return false; }
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $actual) || $actual[$key] !== $value) { return false; }
        }
        return true;
    }

}
