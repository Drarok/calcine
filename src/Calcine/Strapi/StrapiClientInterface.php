<?php

declare(strict_types=1);

namespace Calcine\Strapi;

interface StrapiClientInterface
{
    public function fetchPages(): \Generator;
    public function fetchPosts(): \Generator;
}
