<?php declare(strict_types=1);

namespace Calcine\Post;

use Calcine\Post;

interface PostLocatorInterface
{
    public function pathForPost(Post $post): string;
    public function postForPath(string $path): ?Post;
    public function isValidPath(string $path): bool;
}
