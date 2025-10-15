<?php declare(strict_types=1);

namespace Calcine\Post;

use Calcine\Path;
use Calcine\Post;

final class PostLocator extends BasePostLocator
{
    public function __construct(private string $rootPath, private array $posts)
    {
    }

    public function pathForPost(Post $post): string
    {
        return Path::join(
            $this->rootPath,
            $post->date->format('Y/m/d'),
            $post->slug . '.html'
        );
    }

    public function postForPath(string $path): ?Post
    {
        foreach ($this->posts as $post) {
            if ($this->pathForPost($post) === $path) {
                return $post;
            }
        }

        return null;
    }
}
