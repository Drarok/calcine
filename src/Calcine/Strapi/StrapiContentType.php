<?php

declare(strict_types=1);

namespace Calcine\Strapi;

enum StrapiContentType: string
{
    case Pages = 'pages';
    case Posts = 'posts';
}
