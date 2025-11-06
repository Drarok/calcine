<?php declare(strict_types=1);

namespace Calcine\Template;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\MarkdownConverter;

final class CustomCommonMarkConverter extends MarkdownConverter
{
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new FootnoteExtension());

        parent::__construct($environment);
    }
}
