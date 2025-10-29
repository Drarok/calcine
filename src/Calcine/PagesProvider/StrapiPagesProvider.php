<?php

declare(strict_types=1);

namespace Calcine\PagesProvider;

use Calcine\Strapi\StrapiClientInterface;

class StrapiPagesProvider extends AbstractPagesProvider
{
    public function __construct(private StrapiClientInterface $strapi) {
    }

    protected function loadPages(): array
    {
        $iterator = $this->strapi->fetchPages();
        return iterator_to_array($iterator);
    }
}
