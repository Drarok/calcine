<?php

declare(strict_types=1);

namespace Calcine\PagesProvider;

interface PagesProviderInterface
{
    public function getPages(): array;
}
