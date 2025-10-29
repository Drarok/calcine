<?php

declare(strict_types=1);

namespace Calcine\PostsProvider;

use Calcine\Post;
use Calcine\Post\Tag;

abstract class AbstractPostsProvider implements PostsProviderInterface
{
    protected ?array $posts = null;

    abstract protected function loadPosts(): array;

    public function getPosts(): array
    {
        if ($this->posts !== null) {
            return $this->posts;
        }

        $posts = $this->loadPosts();
        usort($posts, [$this, 'sortPostsDescending']);
        return $this->posts = $posts;
    }

    public function getTags(): array
    {
        $tags = [];
        foreach ($this->getPosts() as $post) {
            foreach ($post->tags as $tag) {
                $tagName = $tag->name;

                if (! array_key_exists($tagName, $tags)) {
                    $tags[$tagName] = [];
                }

                $tags[$tagName][] = $post;
            }
        }

        // Convert the array of name => posts to real Tag objects.
        foreach ($tags as $name => $posts) {
            $tag = new Tag($name, $posts);
            $tags[$name] = $tag;
        }

        // Sort tags by name, case insensitive.
        uksort($tags, 'strnatcasecmp');

        return $tags;
    }

    public function getArchives(): array
    {
        $archives = [];
        foreach ($this->getPosts() as $post) {
            $key = $post->date->format('Y/m');

            if (! array_key_exists($key, $archives)) {
                $archives[$key] = [
                    'name'  => $post->date->format('F Y'),
                    'posts' => [],
                ];
            }

            $archives[$key]['posts'][] = $post;
        }

        return $archives;
    }

    private function sortPostsDescending(Post $a, Post $b): int
    {
        $dateA = $a->date;
        $dateB = $b->date;

        if ($dateA == $dateB) {
            return 0;
        } else {
            return $dateA < $dateB ? 1 : -1;
        }
    }
}
