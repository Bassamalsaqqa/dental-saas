<?php
declare(strict_types=1);
namespace App\Services;

/** Structured, application-generated color-role errors; never carries user CSS or database details. */
final class StudioThemeContrastException extends \DomainException
{
    public function __construct(public readonly array $pairs)
    {
        parent::__construct('Theme contrast validation failed.');
    }
}
