<?php

declare(strict_types=1);

namespace Calcine\Services\ContentAdaptors;

interface ContentAdaptorInterface
{
    public function loadContent(): array;
}
