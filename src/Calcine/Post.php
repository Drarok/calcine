<?php

namespace Calcine;

use Calcine\Post\Tag;

readonly class Post
{
    public function __construct(
        public string $title,
        public array $tags,
        public string $slug,
        public \DateTimeInterface $date,
        public string $body
    ) {
        $errors = [];
        foreach ($tags as $index => $tag) {
            if (! $tag instanceof Tag) {
                $errors[] = "tag at index $index";
            }
        }
        if ($errors) {
            throw new \Exception('Invalid data: ' . implode(', ', $errors));
        }
    }
}
