<?php

namespace Calcine\Template\Engine;

class PlainText implements EngineInterface
{
    /**
     * Render the given template string.
     *
     * @param string $string Template data from a Post file.
     *
     * @return string
     */
    public function render($string)
    {
        return $string;
    }
}
