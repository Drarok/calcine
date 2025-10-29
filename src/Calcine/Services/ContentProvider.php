<?php

declare(strict_types=1);

namespace Calcine\Services;

use Calcine\Model\Page;
use Calcine\Post;
use Calcine\Services\ContentAdaptors\ContentAdaptorInterface;

class ContentProvider implements ContentProviderInterface
{
    private ?array $pages = null;
    private ?array $posts = null;

    public function __construct(
        private ContentAdaptorInterface $pagesAdaptor,
        private ContentAdaptorInterface $postsAdaptor
    ) {
    }

    public function getPages(): array
    {
        if (($pages = $this->pages) !== null) {
            return $pages;
        }

        $content = $this->pagesAdaptor->loadContent();
        return $this->pages = array_map([Page::class, 'fromJson'], $content);
    }

    public function getPosts(): array
    {
        if (($posts = $this->posts) !== null) {
            return $posts;
        }

        $content = $this->postsAdaptor->loadContent();
        return $this->posts = array_map([Post::class, 'fromJson'], $content);
    }
}
