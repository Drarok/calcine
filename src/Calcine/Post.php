<?php

namespace Calcine;

use Calcine\Post\Tag;

readonly class Post
{
    public static function fromJson(array $json): Post
    {
        $title = trim($json['title']);
        $tags = array_map(fn ($tag) => new Tag(trim($tag)), $json['tags']);
        $slug = trim($json['slug']);
        $date = static::makeDate($json['date']);
        $body = trim($json['body']);

        return new Post(
            $title,
            $tags,
            $slug,
            $date,
            $body,
        );
    }

    private static function makeDate(string $value): \DateTimeInterface
    {
        $dateFormats = [
            'Y-m-d H:i:s',
            'Y-m-d\\TH:i:s',
            \DateTimeInterface::ATOM, // "Y-m-d\\TH:i:sP"
            \DateTimeInterface::RFC3339, // "Y-m-d\\TH:i:sP
            \DateTimeInterface::RFC3339_EXTENDED, // "Y-m-d\\TH:i:s.vP"
        ];

        foreach ($dateFormats as $format) {
            if (($date = \DateTimeImmutable::createFromFormat('!' . $format, $value))) {
                return $date;
            }
        }

        throw new \Exception('Invalid date: ' . $value);
    }

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
