<?php

declare(strict_types=1);

namespace Calcine\Strapi;

interface BasicHTTPClientInterface
{
    public function get(string $url): string;
}
