<?php

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Calcine\CLI\AnsiWrapper;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;
use Calcine\Path;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class BuildCommand extends BaseCommand
{
    public function execute(array $args, Ansi $ansi)
    {
        $ansi = new AnsiWrapper($ansi);

        $theme = $this->config->get('site.theme');
        $webPath = $this->config->get('web.path');

        foreach ($args as $arg) {
            list($key, $value) = explode('=', $arg, 2);
            if ($key === '--theme') {
                $theme = $value;
            } elseif ($key === '--web-path') {
                $webPath = $value;
            }
        }

        $ansi
            ->pushStyle(SGR::COLOR_FG_GREEN)
            ->text('Building with theme ')
            ->text("'$theme'", SGR::STYLE_BOLD)
            ->text(' into ')
            ->text("'$webPath'", SGR::STYLE_BOLD)
            ->lf()
        ;

        $content = $this->config->makeContentProvider();

        $user = new User(
            $this->config->get('user.name'),
            $this->config->get('user.email'),
        );

        $renderer = new TemplateRenderer(
            $content,
            $user,
            Path::join(CALCINE_ROOT, 'app', 'templates'),
            $webPath,
            $theme,
        );

        $startTime = microtime(true);
        $stats = $renderer->build();
        $timeTaken = sprintf('%.3fs', microtime(true) - $startTime);

        $ansi->text('Built ');

        $outputs = [
            $stats->formatPosts(),
            $stats->formatPages(),
            $stats->formatTags(),
        ];
        $lastIdx = count($outputs) - 1;
        foreach ($outputs as $idx => $text) {
            $ansi->text($text, SGR::STYLE_BOLD);
            if ($idx < $lastIdx) {
                $ansi->text(', ');
            }
        }

        $ansi
            ->text(' in ')
            ->text($timeTaken, SGR::COLOR_FG_YELLOW)
            ->lf()
        ;
    }
}
