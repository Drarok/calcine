<?php

declare(strict_types=1);

namespace Calcine\Services\ContentAdaptors;

use Calcine\Strapi\StrapiClientInterface;
use Calcine\Strapi\StrapiContentType;

class StrapiContentAdaptor implements ContentAdaptorInterface
{
    public function __construct(
        private StrapiClientInterface $strapi,
        private StrapiContentType $contentType
    ) {
    }

    public function loadContent(): array
    {
        $generator = match ($this->contentType) {
            StrapiContentType::Pages => $this->strapi->fetchPages(),
            StrapiContentType::Posts => $this->strapi->fetchPosts(),
        };

        return iterator_to_array($generator);
    }
}
