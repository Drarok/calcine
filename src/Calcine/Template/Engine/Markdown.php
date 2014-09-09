<?php

namespace Calcine\Template\Engine;

use Parsedown;

class Markdown implements EngineInterface
{
    /**
     * Markdown renderer.
     *
     * @var Parsedown
     */
    protected $markdown;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->markdown = new Parsedown();
    }

    /**
     * Render the given template string.
     *
     * @param string $string Template data from a Post file.
     *
     * @return string
     */
    public function render($string)
    {
        return $this->markdown->text($string);
    }
}
