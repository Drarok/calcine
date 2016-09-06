<?php

namespace Calcine\Template\Engine;



abstract class EngineFactory
{
    /**
     * Create an Engine instance.
     *
     * @param string $name Short name of the engine.
     *
     * @return EngineInterface
     */
    public static function createInstance($name)
    {
        switch ($name) {
            case 'markdown':
                return new Markdown();

            case 'plaintext':
                return new PlainText();

            default:
                throw new \Exception('Invalid rendering engine: \'' . $name . '\'');
        }
    }
}
