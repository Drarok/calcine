<?php declare(strict_types=1);

namespace Calcine\Template;

use Twig\Environment as TwigEnvironment;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class CustomTwigEnvironment extends TwigEnvironment
{
    public function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($loader, $options);

        $this->addExtension(new MarkdownExtension());
        $this->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class)
            {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }
            }
        });
    }
}
