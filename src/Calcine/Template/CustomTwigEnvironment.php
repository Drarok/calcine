<?php declare(strict_types=1);

namespace Calcine\Template;

use Twig\Environment as TwigEnvironment;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Loader\LoaderInterface;
use Twig\Markup;

class CustomTwigEnvironment extends TwigEnvironment
{
    public static function shortenPostBody(string $body): Markup
    {
        // Manually-specified "jump" marker.
        $jumpLocation = stripos($body, '<!-- jump -->');

        // Attempt to find the end of a paragraph.
        if ($jumpLocation === false && ($position = strpos($body, '</p>', 1)) !== false) {
            $jumpLocation = $position + 4;
        }

        // Fall-back behavior is to return the whole string, since we don't want invalid HTML.
        if ($jumpLocation === false) {
            $jumpLocation = strlen($body);
        }

        return new Markup(substr($body, 0, $jumpLocation), 'UTF-8');
    }

    public function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($loader, $options);

        $shortenFilter = new \Twig\TwigFilter('shorten_post_body', [static::class, 'shortenPostBody']);
        $this->addFilter($shortenFilter);

        $this->addExtension(new MarkdownExtension());
        $this->addRuntimeLoader(new MarkdownRuntimeLoader());
    }
}
