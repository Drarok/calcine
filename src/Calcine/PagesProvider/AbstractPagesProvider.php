<?php

declare(strict_types=1);

namespace Calcine\PagesProvider;

abstract class AbstractPagesProvider implements PagesProviderInterface
{
    protected ?array $pages = null;

    abstract protected function loadPages(): array;

    public function getPages(): array
    {
        if ($this->pages !== null) {
            return $this->pages;
        }

        return $this->pages = $this->loadPages();
    }
}
