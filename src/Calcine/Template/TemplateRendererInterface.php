<?php declare(strict_types=1);

namespace Calcine\Template;

use Calcine\Model\BuildStats;

interface TemplateRendererInterface
{
    public function build(): BuildStats;
}
