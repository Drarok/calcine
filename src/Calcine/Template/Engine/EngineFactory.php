<?php

namespace Calcine\Template\Engine;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\GithubFlavoredMarkdownConverter;

abstract class EngineFactory
{
    /**
     * Create an Engine instance.
     *
     * @param string $name    Short name of the engine.
     * @param ?array $options Options to pass to creation of the engine.
     *
     * @return EngineInterface
     *
     * @throws \Exception when invalid engine is requested.
     */
    public static function createInstance($name, ?array $options = null)
    {
        switch ($name) {
            case 'markdown':
                $converter = new CommonMarkConverter();
                return new Markdown($converter);

            case 'plaintext':
                return new PlainText();

            default:
                throw new \Exception('Invalid rendering engine: \'' . $name . '\'');
        }
    }
}
