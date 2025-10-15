<?php declare(strict_types=1);

namespace Calcine\Post;

abstract class BasePostLocator implements PostLocatorInterface
{
    public function isValidPath(string $path): bool
    {
        return (($post = $this->postForPath()) !== null);
    }
}
