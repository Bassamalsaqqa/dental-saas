<?php

/** Stable request-local metadata versioning for public assets, never private storage. */
function asset_version(string $relative): string
{
    static $versions = [];
    if (isset($versions[$relative])) { return $versions[$relative]; }
    if ($relative === '' || preg_match('~[^a-zA-Z0-9_./-]~', $relative)
        || str_starts_with($relative, '/') || in_array('..', explode('/', $relative), true)) {
        return 'missing';
    }
    $root = realpath(FCPATH);
    $path = $root === false ? false : realpath($root . DIRECTORY_SEPARATOR . $relative);
    if ($path === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)
        || !is_file($path) || !is_readable($path)) {
        return $versions[$relative] = 'missing';
    }
    $mtime = filemtime($path);
    $size = filesize($path);
    return $versions[$relative] = ($mtime === false || $size === false) ? 'missing' : $mtime . '-' . $size;
}
