<?php

declare(strict_types=1);

namespace Calcine\Services;

interface ContentProviderInterface
{
    public function getPages(): array;
    public function getPosts(): array;
}
