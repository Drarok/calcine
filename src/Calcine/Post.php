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

    public function getShortBody(): string
    {
        $body = trim($this->body);

        // Manually-specified "jump" marker.
        $jumpLocation = stripos($body, '<!-- jump -->');

        // Attempt to find the end of a paragraph.
        if ($jumpLocation === false) {
            $jumpLocation = strpos($body, "\n\n");
        }

        if ($jumpLocation) {
            return substr($body, 0, $jumpLocation);
        } else {
            // Fall-back behavior is to return a limited number of words.
            $words = preg_split('/\\b/', $body);
            $words = array_slice($words, 0, 150);
            return implode('', $words);
        }
    }
}
