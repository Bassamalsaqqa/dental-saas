<?php

namespace App\Services;

class OdontogramSvgSnapshotService
{
    private const MAX_STORED_SNAPSHOT_BYTES = 250000;

    public function encode(?string $svg): ?string
    {
        $svg = trim((string) $svg);
        if ($svg === '') {
            return null;
        }

        if (strlen($svg) > self::MAX_STORED_SNAPSHOT_BYTES) {
            return null;
        }

        // Reduce obvious whitespace without changing semantic content.
        $svg = preg_replace('/>\s+</', '><', $svg) ?? $svg;

        $compressed = gzencode($svg, 6);
        if ($compressed === false) {
            return null;
        }

        if (strlen($compressed) > self::MAX_STORED_SNAPSHOT_BYTES) {
            return null;
        }

        return 'gz64:' . base64_encode($compressed);
    }

    public function decode(?string $payload): ?string
    {
        $payload = (string) $payload;
        if ($payload === '') {
            return null;
        }

        if (str_starts_with($payload, 'gz64:')) {
            $encoded = substr($payload, 5);
            $binary = base64_decode($encoded, true);
            if ($binary === false) {
                return null;
            }

            $decoded = gzdecode($binary);
            return $decoded === false ? null : $decoded;
        }

        return $payload;
    }
}
