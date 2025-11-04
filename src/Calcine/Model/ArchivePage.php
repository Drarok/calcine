<?php

namespace Calcine\Model;

readonly class ArchivePage
{
    public function __construct(
        public string $name,
        public string $slug,
        public array $posts,
    ) {
    }
}
