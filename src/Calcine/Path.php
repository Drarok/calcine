<?php declare(strict_types=1);

namespace Calcine;

abstract class Path
{
    /**
     * Join together all parameters with a directory separator.
     */
    public static function join(string ...$args): string
    {
        if (!$args) {
            return '';
        }

        $first = rtrim(array_shift($args), '\\/');
        $rest = array_map(fn ($s) => trim($s, '\\/'), $args);

        return implode(
            DIRECTORY_SEPARATOR,
            [$first, ...$rest]
        );
    }
}
