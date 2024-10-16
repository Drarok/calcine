<?php

namespace Calcine\Template\Engine;

use League\CommonMark\ConverterInterface;

class Markdown implements EngineInterface
{
    protected ConverterInterface $converter;

    /**
     * Constructor.
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
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
        return $this->converter->convert($string)->getContent();
    }
}
