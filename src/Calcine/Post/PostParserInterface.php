<?php declare(strict_types=1);

namespace Calcine\Post;

use Calcine\Post;

interface PostParserInterface
{
    function parse(string $path): Post;
}

