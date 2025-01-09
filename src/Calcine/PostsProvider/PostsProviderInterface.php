<?php

declare(strict_types=1);

namespace Calcine\PostsProvider;

use Calcine\Post;
use Calcine\Post\Tag;

interface PostsProviderInterface
{
    /**
     * @return [Post]
     */
    public function getPosts(): array;

    /**
     * @return [Tag]
     */
    public function getTags(): array;

    /**
     * TODO
     */
    public function getArchives(): array;
}
