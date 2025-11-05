<?php declare(strict_types=1);

namespace Calcine\Model;

use Calcine\Model\Tag;

readonly class Post
{
    public static function fromJson(array $json): Post
    {
        $title = trim($json['title']);
        $tags = array_map('trim', $json['tags']);
        natsort($tags);
        $tags = array_map(fn ($name) => new Tag($name), $tags);
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

    public static function sort(Post $a, Post $b): int
    {
        return $a->date->getTimestamp() - $b->date->getTimeStamp();
    }

    private static function makeDate(string $value): \DateTimeInterface
    {
        $dateFormats = [
            'Y-m-d H:i:s',
            'Y-m-d\\TH:i:s',
            \DateTimeInterface::ATOM,
            \DateTimeInterface::RFC3339,
            \DateTimeInterface::RFC3339_EXTENDED,
        ];

        foreach ($dateFormats as $format) {
            if (($date = \DateTimeImmutable::createFromFormat('!' . $format, $value))) {
                return $date;
            }
        }

        throw new ParseException('Invalid date: ' . $value);
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

        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = "Field 'slug' is invalid: '$slug'";
        }

        if ($errors) {
            throw new ParseException('Invalid data: ' . implode(', ', $errors));
        }
    }
}
