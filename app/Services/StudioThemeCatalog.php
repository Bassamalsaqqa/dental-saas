<?php
declare(strict_types=1);

namespace App\Services;

/** Immutable chrome palette contract. Never themes diagnostic SVG or signed/print evidence. */
final class StudioThemeCatalog
{
    public const VERSION = 1;
    public const DEFAULT_PRESET = 'studio-forest';
    private const BASE = [
        'ink'=>'#102e27',
        'ink-muted'=>'#445f54',
        'frame'=>'#153f36',
        'frame-deep'=>'#10332c',
        'frame-raised'=>'#255044',
        'frame-hover'=>'#265748',
        'on-frame'=>'#f3f7ef',
        'on-frame-muted'=>'#c7d9cf',
        'action'=>'#23684e',
        'action-hover'=>'#24614c',
        'focus'=>'#39794f',
        'selected'=>'#c4dc87',
        'selected-soft'=>'#d5e6ad',
        'canvas'=>'#eaf0eb',
        'outer'=>'#d9e4dc',
        'surface'=>'#f3f7ef',
        'surface-raised'=>'#edf3e9',
        'surface-tonal'=>'#e1ebd9',
        'surface-heading'=>'#d5e3da',
        'surface-strong'=>'#d4e3d2',
        'surface-hover'=>'#d8e6d0',
        'surface-active'=>'#c6dfb3',
        'control'=>'#e1ebdb',
        'line'=>'#c9d8cf',
        'line-strong'=>'#afbeb6',
        'success'=>'#205c38',
        'success-soft'=>'#cce8d6',
        'warning'=>'#684512',
        'warning-soft'=>'#f0ddb0',
        'danger'=>'#773a2e',
        'danger-soft'=>'#edd0c5',
        'info'=>'#254f43',
        'info-soft'=>'#d9f0e4',
        'avatar-olive'=>'#dde2b4',
        'avatar-olive-ink'=>'#485523',
        'avatar-copper'=>'#ecd4be',
        'avatar-copper-ink'=>'#785035',
        'on-action'=>'#f3f7ef',
        'on-selected'=>'#153f36',
        'link'=>'#23684e',
        'control-border'=>'#5d7063',
        'on-info'=>'#f3f7ef',
    ];

    /** Reading surfaces stay light; Deep Charcoal deliberately darkens chrome, not clinical documents. */
    public static function presets(): array
    {
        return [
            'studio-forest' => [],
            'sandstone-copper' => [
                'frame'=>'#49362d','frame-deep'=>'#30241f','frame-raised'=>'#60473a','frame-hover'=>'#684b3c',
                'ink'=>'#35291f','ink-muted'=>'#625044','action'=>'#75432b','action-hover'=>'#653821',
                'link'=>'#75432b','info'=>'#5b4535','focus'=>'#855332','on-selected'=>'#35291f',
                'canvas'=>'#f1e9dc','outer'=>'#e7dbc8','surface'=>'#fbf5e9','surface-raised'=>'#f5eddf',
                'surface-tonal'=>'#eee2cf','surface-heading'=>'#e9d8be','surface-strong'=>'#eadcc5',
                'surface-hover'=>'#ecdfc9','surface-active'=>'#e6d5b7','control'=>'#f0e5d2',
                'selected'=>'#e7d3a2','selected-soft'=>'#eedfb9','control-border'=>'#89715b',
                'on-frame-muted'=>'#ead8c7','line'=>'#d5c5ae','line-strong'=>'#baa58c',
            ],
            'slate-sage' => [
                'frame'=>'#293e43','frame-deep'=>'#1c2c31','frame-raised'=>'#3a5156','frame-hover'=>'#415b60',
                'ink'=>'#213438','ink-muted'=>'#465e60','action'=>'#345d58','action-hover'=>'#284e4b',
                'link'=>'#345d58','info'=>'#34514e','focus'=>'#416b65','on-selected'=>'#213438',
                'canvas'=>'#e9efec','outer'=>'#dce6e1','surface'=>'#f4f7f3','surface-raised'=>'#eaf1ec',
                'surface-tonal'=>'#e2ece4','surface-heading'=>'#dbe7de','surface-strong'=>'#dce8df',
                'surface-hover'=>'#dce9dd','surface-active'=>'#d2e3d3','control'=>'#e4eee6',
                'selected'=>'#d0dfb0','selected-soft'=>'#deebc7','control-border'=>'#607169',
            ],
            'deep-charcoal' => [
                'frame'=>'#303238','frame-deep'=>'#202126','frame-raised'=>'#42454a','frame-hover'=>'#4b4e52',
                'ink'=>'#272d2b','ink-muted'=>'#505c57','action'=>'#3d5950','action-hover'=>'#304a42',
                'link'=>'#3d5950','info'=>'#3d5148','focus'=>'#506b5b','on-selected'=>'#272d2b',
                'canvas'=>'#eaeeea','outer'=>'#dce2dc','surface'=>'#f6f7f0','surface-raised'=>'#edf1e9',
                'surface-tonal'=>'#e4eadd','surface-heading'=>'#dce5d7','surface-strong'=>'#dde6d7',
                'surface-hover'=>'#dce7d6','surface-active'=>'#d4e3c9','control'=>'#e7eddf',
                'selected'=>'#d2dfac','selected-soft'=>'#e0e9c4','control-border'=>'#62725e',
            ],
            'high-contrast' => [
                'ink'=>'#101b17','ink-muted'=>'#243b2f','frame'=>'#10251c','frame-deep'=>'#09180f',
                'frame-raised'=>'#1b3528','frame-hover'=>'#254131','on-frame'=>'#fffff1','on-frame-muted'=>'#f1f7db',
                'action'=>'#17442b','action-hover'=>'#103320','on-action'=>'#fffff1','link'=>'#17442b',
                'focus'=>'#17442b','on-selected'=>'#101b17','selected'=>'#e3efac','selected-soft'=>'#edf5c7',
                'canvas'=>'#f4f6e9','outer'=>'#e8eedb','surface'=>'#fffff1','surface-raised'=>'#f6f8e9',
                'surface-tonal'=>'#eff4de','surface-heading'=>'#e8efd3','surface-strong'=>'#edf2db',
                'surface-hover'=>'#eaf2d6','surface-active'=>'#e3edc9','control'=>'#f1f5e3',
                'control-border'=>'#445d48','line-strong'=>'#71816b',
            ],
        ];
    }

    public static function roles(): array
    {
        return array_keys(self::BASE);
    }

    public static function palette(string $preset): array
    {
        $presets = self::presets();
        if (!array_key_exists($preset, $presets)) {
            throw new \DomainException('Unknown theme preset.');
        }
        return array_replace(self::BASE, $presets[$preset]);
    }

    /** Canonical complete palette freezes explicit clinic choices against future platform changes. */
    public static function normalize(array $input): array
    {
        if (array_diff(array_keys($input), ['schema','preset','colors','density','text_size'])) {
            throw new \DomainException('Unknown theme property.');
        }
        if (($input['schema'] ?? self::VERSION) !== self::VERSION) {
            throw new \DomainException('Unsupported theme schema.');
        }
        $preset = $input['preset'] ?? self::DEFAULT_PRESET;
        if (!is_string($preset)) throw new \DomainException('Invalid theme preset.');
        $colors = $input['colors'] ?? [];
        if (!is_array($colors) || array_diff(array_keys($colors), self::roles())) {
            throw new \DomainException('Unknown theme color role.');
        }
        foreach ($colors as $role => $value) {
            if (!is_string($value) || !preg_match('/\A#[0-9a-fA-F]{6}\z/D', $value)) {
                throw new \DomainException('Theme colors must be six-digit HEX values.');
            }
            $colors[$role] = strtolower($value);
        }
        $density = $input['density'] ?? 'comfortable';
        $textSize = $input['text_size'] ?? 'standard';
        if (!in_array($density, ['comfortable','compact'], true) || !in_array($textSize, ['standard','large'], true)) {
            throw new \DomainException('Unsupported theme layout option.');
        }
        $palette = array_replace(self::palette($preset), $colors);
        $failures = self::contrastFailures($palette);
        if ($failures) {
            throw new \DomainException('Theme contrast fails: '.implode(', ', array_keys($failures)));
        }
        return ['schema'=>self::VERSION,'preset'=>$preset,'colors'=>$palette,'density'=>$density,'text_size'=>$textSize];
    }

    /** Explicit consumer pairs: decorative separators are not represented as essential control borders. */
    public static function contrastPairs(): array
    {
        $pairs = [];
        $surfaces = ['canvas','outer','surface','surface-raised','surface-tonal','surface-heading','surface-strong','surface-hover','surface-active','control','selected','selected-soft'];
        foreach (['ink','ink-muted','frame'] as $foreground) {
            foreach ($surfaces as $background) $pairs[$foreground.'/'.$background] = [$foreground,$background,4.5];
        }
        foreach (['on-frame','on-frame-muted'] as $foreground) {
            foreach (['frame','frame-deep','frame-raised','frame-hover'] as $background) $pairs[$foreground.'/'.$background] = [$foreground,$background,4.5];
        }
        foreach (['action','action-hover','frame'] as $background) $pairs['on-action/'.$background] = ['on-action',$background,4.5];
        foreach (['selected','selected-soft','surface-active'] as $background) $pairs['on-selected/'.$background] = ['on-selected',$background,4.5];
        foreach (['surface','canvas','control','outer','surface-raised','selected-soft'] as $background) $pairs['link/'.$background] = ['link',$background,4.5];
        $pairs['on-info/info'] = ['on-info','info',4.5];
        foreach (['success','warning','danger','info'] as $state) $pairs[$state.'/'.$state.'-soft'] = [$state,$state.'-soft',4.5];
        foreach (['olive','copper'] as $avatar) $pairs['avatar-'.$avatar] = ['avatar-'.$avatar.'-ink','avatar-'.$avatar,4.5];
        foreach (['surface','control','canvas','surface-active','selected'] as $background) {
            $pairs['focus/'.$background] = ['focus',$background,3.0];
            $pairs['control-border/'.$background] = ['control-border',$background,3.0];
        }
        return $pairs;
    }

    public static function contrastFailures(array $colors): array
    {
        $failures = [];
        foreach (self::contrastPairs() as $name => [$foreground,$background,$minimum]) {
            if (!isset($colors[$foreground], $colors[$background]) || self::contrast($colors[$foreground], $colors[$background]) + 0.000001 < $minimum) {
                $failures[$name] = $minimum;
            }
        }
        return $failures;
    }

    public static function contrast(string $a, string $b): float
    {
        $values = [];
        foreach ([$a,$b] as $hex) {
            if (!preg_match('/\A#[0-9a-fA-F]{6}\z/D', $hex)) throw new \DomainException('Invalid contrast color.');
            $channels = [];
            foreach ([1,3,5] as $offset) {
                $channel = hexdec(substr($hex, $offset, 2)) / 255;
                $channels[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
            }
            $values[] = $channels[0]*0.2126 + $channels[1]*0.7152 + $channels[2]*0.0722;
        }
        return (max($values)+0.05)/(min($values)+0.05);
    }

    /** Safe first-paint declaration map; only normalized keys and hexadecimal values can reach CSS. */
    public static function css(array $input): string
    {
        $theme = self::normalize($input);
        $rules = [];
        foreach ($theme['colors'] as $role => $color) $rules[] = '--color-'.$role.':'.$color;
        $rules[] = '--grid-row-height:'.($theme['density']==='compact'?'80px':'96px');
        $rules[] = '--text-body:'.($theme['text_size']==='large'?'1.25rem':'1.125rem');
        return ':root{'.implode(';', $rules).'}';
    }
}
