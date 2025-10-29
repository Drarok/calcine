<?php

namespace Calcine\Post;

use Calcine\Post;

readonly class Tag
{
    public static function fromJson(array $json): Tag
    {
        return new Tag($json['name'], $json['posts'] ?? []);
    }

    public function __construct(public string $name, public array $posts = [])
    {
    }

    /**
     * Gets the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($this->name)), '-');
    }
}
