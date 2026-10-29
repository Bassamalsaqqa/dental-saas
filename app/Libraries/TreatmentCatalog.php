<?php

namespace App\Libraries;

final class TreatmentCatalog
{
    /** Transport-only edit choice; never a stored clinical category. */
    public const KEEP_UNKNOWN = '__keep_historical_unknown__';
    /**
     * Core procedure list shown to users.
     *
     * @return array<string, string>
     */
    public static function forSelection(): array
    {
        return [
            'comprehensive_oral_evaluation' => 'Comprehensive Oral Evaluation',
            'periodic_oral_evaluation' => 'Periodic Oral Evaluation',
            'emergency_oral_evaluation' => 'Emergency Oral Evaluation',
            'periapical_xray' => 'Periapical Radiograph (PA)',
            'bitewing_xray' => 'Bitewing Radiograph',
            'panoramic_xray' => 'Panoramic Radiograph (OPG)',
            'adult_prophylaxis' => 'Adult Prophylaxis (Scale and Polish)',
            'child_prophylaxis' => 'Child Prophylaxis',
            'fluoride_varnish' => 'Fluoride Varnish',
            'pit_fissure_sealant' => 'Pit and Fissure Sealant',
            'composite_filling_anterior' => 'Composite Restoration (Anterior)',
            'composite_filling_posterior' => 'Composite Restoration (Posterior)',
            'amalgam_filling' => 'Amalgam Restoration',
            'glass_ionomer_filling' => 'Glass Ionomer Restoration',
            'direct_pulp_cap' => 'Direct Pulp Cap',
            'indirect_pulp_cap' => 'Indirect Pulp Cap',
            'root_canal_anterior' => 'Root Canal Therapy (Anterior)',
            'root_canal_premolar' => 'Root Canal Therapy (Premolar)',
            'root_canal_molar' => 'Root Canal Therapy (Molar)',
            'root_canal_retreatment' => 'Root Canal Retreatment',
            'simple_extraction' => 'Simple Extraction',
            'surgical_extraction' => 'Surgical Extraction',
            'wisdom_tooth_extraction' => 'Wisdom Tooth Extraction',
            'scaling_root_planing' => 'Scaling and Root Planing',
            'periodontal_maintenance' => 'Periodontal Maintenance',
            'crown' => 'Crown',
            'inlay_onlay' => 'Inlay/Onlay',
            'bridge' => 'Bridge',
            'veneer' => 'Veneer',
            'partial_denture' => 'Partial Denture',
            'complete_denture' => 'Complete Denture',
            'implant_placement' => 'Implant Placement',
            'implant_abutment' => 'Implant Abutment Placement',
            'implant_crown' => 'Implant Crown',
            'orthodontic_consultation' => 'Orthodontic Consultation',
            'braces' => 'Fixed Orthodontic Appliance (Braces)',
            'clear_aligner' => 'Clear Aligner Therapy',
            'teeth_whitening_inoffice' => 'Teeth Whitening (In-Office)',
            'teeth_whitening_home' => 'Teeth Whitening (Home Kit)',
            'night_guard' => 'Night Guard / Occlusal Splint',
            'incision_drainage' => 'Incision and Drainage',
            'oral_biopsy' => 'Oral Biopsy',
            'other' => 'Other Procedure',
        ];
    }

    /**
     * Legacy keys retained for backward compatibility.
     *
     * @return array<string, string>
     */
    public static function legacy(): array
    {
        return [
            'cleaning' => 'Adult Prophylaxis (Legacy)',
            'extraction' => 'Simple Extraction (Legacy)',
            'filling' => 'Composite Restoration (Legacy)',
            'root_canal' => 'Root Canal Therapy (Legacy)',
            'orthodontic' => 'Orthodontic Treatment (Legacy)',
            'implant' => 'Implant Placement (Legacy)',
            'veneers' => 'Veneer (Legacy)',
            'whitening' => 'Teeth Whitening (Legacy)',
            'gum_treatment' => 'Periodontal Treatment (Legacy)',
            'oral_surgery' => 'Oral Surgery (Legacy)',
        ];
    }

    /**
     * Full set used by validation and mapping.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::forSelection() + self::legacy();
    }

    public static function validationRule(): string
    {
        return 'required|in_list[' . implode(',', array_keys(self::all())) . ']';
    }

    public static function nameFor(string $key): string
    {
        $all = self::all();
        return $all[$key] ?? (function_exists('lang') ? lang('Treatment.historical_unknown_type') : 'Unknown historical treatment (unclassified)');
    }
}

