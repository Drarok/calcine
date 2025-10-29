<?php

declare(strict_types=1);

namespace Calcine\PostsProvider;

use Calcine\Post;
use Calcine\Post\Tag;
use Calcine\Post\FilePostParser;

class FilePostsProvider extends AbstractPostsProvider
{
    private FilePostParser $parser;

    public function __construct(private string $rootPath)
    {
        $this->parser = new FilePostParser();
    }

    protected function loadPosts(): array
    {
        $posts = [];

        $dir = new \DirectoryIterator($this->rootPath);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            $posts[] = $this->parser->parse($fileInfo->getPathname());
        }

        return $posts;
    }
}
