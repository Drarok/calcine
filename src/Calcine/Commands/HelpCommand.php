<?php declare(strict_types=1);

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;
use Calcine\Path;
use Calcine\SiteBuilder;
use Calcine\Template\TemplateRenderer;

class HelpCommand extends BaseCommand
{
    public function execute(array $args, Ansi $ansi): void
    {
        $ansi->text('HelpCommand: TODO');
        $ansi->lf();
        $ansi->lf();

        $ansi->text('Commands: build');
        $ansi->lf();
    }
}
