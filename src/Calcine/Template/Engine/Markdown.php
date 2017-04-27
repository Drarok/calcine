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
     * @inheritdoc
     */
    public function getExtension()
    {
        return 'markdown';
    }

    /**
     * @inheritdoc
     */
    public function render($string)
    {
        return $this->markdown->text($string);
    }
}
