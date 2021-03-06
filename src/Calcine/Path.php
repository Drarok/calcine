<?php

namespace Calcine;

abstract class Path
{
    /**
     * Join together all parameters with a directory separator.
     *
     * @return string
     */
    public static function join()
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_map(
                function ($k, $v) {
                    if ($k === 0) {
                        return rtrim($v, '/\\');
                    } else {
                        return trim($v, '/\\');
                    }
                },
                array_keys(func_get_args()),
                func_get_args()
            )
        );
    }
}
