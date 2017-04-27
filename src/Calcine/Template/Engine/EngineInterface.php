<?php

namespace Calcine\Template\Engine;

interface EngineInterface
{
    /**
     * Get the extension used for files in this format.
     *
     * @return string
     */
    public function getExtension();

    /**
     * Render the given template string.
     *
     * @param string $string Template data from a Post file.
     *
     * @return string
     */
    public function render($string);
}
