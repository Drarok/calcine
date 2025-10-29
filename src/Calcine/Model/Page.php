<?php

namespace Calcine\Model;

readonly class Page
{
    public static function fromJson(array $json): Page
    {
        return new Page($json['title'], $json['slug'], $json['body']);
    }

    public function __construct(
        public string $title,
        public string $slug,
        public string $body
    ) {
    }
}
