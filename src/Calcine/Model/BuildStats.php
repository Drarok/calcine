<?php declare(strict_types=1);

namespace Calcine\Model;

readonly class BuildStats
{
    public function __construct(
        public int $pages,
        public int $posts,
        public int $tags,
    ) {
    }

    public function formatPages(): string
    {
        return $this->formatCount($this->pages, 'page', 'pages');
    }

    public function formatPosts(): string
    {
        return $this->formatCount($this->posts, 'post', 'posts');
    }

    public function formatTags(): string
    {
        return $this->formatCount($this->tags, 'tag', 'tags');
    }

    private function formatCount(int $count, string $singular, string $plural): string
    {
        $noun = $count === 1 ? $singular : $plural;
        return "$count $noun";
    }
}
