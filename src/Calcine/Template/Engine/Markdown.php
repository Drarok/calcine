<?php

namespace Calcine\Template\Engine;

use Calcine\Parsedown\CustomParsedownExtra;

class Markdown implements EngineInterface
{
    /**
     * Markdown renderer.
     *
     * @var CustomParsedownExtra
     */
    protected $markdown;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->markdown = new CustomParsedownExtra();
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
