<?php

namespace Calcine\Template\Engine;

class PlainText implements EngineInterface
{
    /**
     * @inheritdoc
     */
    public function getExtension()
    {
        return 'txt';
    }

    /**
     * @inheritdoc
     */
    public function render($string)
    {
        return $string;
    }
}
