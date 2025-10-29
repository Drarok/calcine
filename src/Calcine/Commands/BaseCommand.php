<?php declare(strict_types=1);

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

use Calcine\Config\ConfigParser;

abstract class BaseCommand
{
    abstract public function execute(array $args, Ansi $ansi);

    protected ConfigParser $config;

    public static function factory(ConfigParser $config, string $name): BaseCommand
    {
        $replacer = function ($matches) {
            return strtoupper($matches[1]);
        };
        $name = preg_replace_callback('/(?:^|-)([a-z])/i', $replacer, $name);
        $name = __NAMESPACE__ . '\\' . $name . 'Command';
        if (!class_exists($name)) {
            throw new \Exception("Class '$name' does not exist");
        }
        return new $name($config);
    }

    public function __construct(ConfigParser $config)
    {
        $this->config = $config;
    }
}
