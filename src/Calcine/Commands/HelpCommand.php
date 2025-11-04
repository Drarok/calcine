<?php

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;
use Calcine\Path;
use Calcine\SiteBuilder;
use Calcine\Template\TemplateRenderer;

class HelpCommand extends BaseCommand
{
    public function execute(array $args, Ansi $ansi)
    {
        $ansi->text('HelpCommand: TODO');
        $ansi->lf();
        $ansi->lf();

        $ansi->text('Commands: build');
        $ansi->lf();
    }
}
