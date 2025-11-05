<?php declare(strict_types=1);

namespace Calcine\Services;

interface ContentProviderInterface
{
    public function getTitle(): string;
    public function getDescription(): string;
    public function getPages(): array;
    public function getPosts(): array;
}
