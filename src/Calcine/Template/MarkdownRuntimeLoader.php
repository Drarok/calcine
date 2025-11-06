<?php declare(strict_types=1);

namespace Calcine\Template;

use Twig\Extra\Markdown\LeagueMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

final class MarkdownRuntimeLoader implements RuntimeLoaderInterface
{
    public function load($class): mixed
    {
        if (MarkdownRuntime::class === $class) {
            return $this->makeMarkdownRuntime();
        }
    }

    private function makeMarkdownRuntime(): MarkdownRuntime
    {
        $converter = new CustomCommonMarkConverter();
        $markdown = new LeagueMarkdown($converter);
        return new MarkdownRuntime($markdown);
    }
}
