<?php

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

use Calcine\Config\Parser;

abstract class BaseCommand
{
    /**
     * @var Parser
     */
    protected $config;

    /**
     * @param Parser $config
     * @param string $name Name of the command
     *
     * @return BaseCommand
     */
    public static function factory(Parser $config, $name)
    {
        $replacer = function ($matches) {
            return strtoupper($matches[1]);
        };
        $name = preg_replace_callback('/(?:^|-)([a-z])/i', $replacer, $name);
        $name = __NAMESPACE__ . '\\' . $name . 'Command';
        return new $name($config);
    }

    public function __construct(Parser $config)
    {
        $this->config = $config;
    }

    abstract public function execute(array $args, Ansi $ansi);
}
