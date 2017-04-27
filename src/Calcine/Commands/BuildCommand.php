<?php

namespace Calcine\Commands;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;
use Calcine\Path;
use Calcine\SiteBuilder;
use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class BuildCommand extends BaseCommand
{
    public function execute(array $args, Ansi $ansi)
    {
        $theme = $this->config->get('site.theme');

        foreach ($args as $arg) {
            list($key, $value) = explode('=', $arg, 2);
            if ($key === 'theme') {
                $theme = $value;
            }
        }

        $ansi->color([SGR::COLOR_FG_GREEN]);
        $ansi->text('Building with theme \'' . $theme . '\'')->lf();

        $renderer = new TemplateRenderer(
            new User($this->config->get('user.name'), $this->config->get('user.email')),
            Path::join(CALCINE_ROOT, 'app', 'templates'),
            $this->config->get('web.path')
        );
        $renderer->setTheme($theme)
            ->setGlobal('title', $this->config->get('site.title'))
            ->setGlobal('description', $this->config->get('site.description'))
        ;

        $site = new SiteBuilder(
            EngineFactory::createInstance($this->config->get('posts.format')),
            $renderer,
            $this->config->get('posts.path')
        );

        $stats = $site->build();

        $ansi->text(sprintf(
            'Built %d %s.',
            $stats['posts'],
            $stats['posts'] == 1 ? 'post' : 'posts'
        ));
        $ansi->lf();

        $ansi->text(sprintf(
            'Built %d %s.',
            $stats['indexes'],
            $stats['indexes'] == 1 ? 'index' : 'indexes'
        ));
        $ansi->lf();
    }
}
