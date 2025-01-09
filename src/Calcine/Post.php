<?php

namespace Calcine;

readonly class Post
{
    public function __construct(
        public string $title,
        public array $tags,
        public string $slug,
        public \DateTimeInterface $date,
        public string $body
    ) {
    }
}
