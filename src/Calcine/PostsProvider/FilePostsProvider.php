<?php

declare(strict_types=1);

namespace Calcine\PostsProvider;

use Calcine\Post;
use Calcine\Post\Tag;

class FilePostsProvider extends AbstractPostsProvider
{
    public function __construct(private string $rootPath)
    {
    }

    protected function loadPosts(): array
    {
        $posts = [];

        $dir = new \DirectoryIterator($this->rootPath);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            // Parse the post file and store in the posts array.
            $posts[] = $this->loadPost($fileInfo->getPathname());
        }

        // Ensure posts are in order.
        usort($posts, [$this, 'sortPostsDescending']);

        return $posts;
    }

    private function loadPost($path): Post
    {
        if (! ($file = fopen($path, 'r'))) {
            throw new ParseException("Cannot open $path");
        }

        $basepath = basename($path);

        $data = [
            'title' => false,
            'tags'  => false,
            'slug'  => false,
            'date'  => false,
        ];
        $errors = [];

        while (! feof($file)) {
            $line = trim(fgets($file));

            // Skip blank or comment lines.
            if (! $line || $line[0] == ';') {
                continue;
            }

            // Parse/validate this header line.
            if (! preg_match('/^([a-zA-Z]+): *(.*)$/', $line, $matches)) {
                throw new ParseException("Invalid header line in $basepath: '$line'.");
            }

            $name = strtolower($matches[1]);
            $value = trim($matches[2]);

            if ($name === 'body') {
                break;
            }

            if (! array_key_exists($name, $data)) {
                throw new ParseException("Unknown header in $basepath: \'$name\'.");
            }

            try {
                $data[$name] = $this->processHeader($name, $value);
            } catch (ParseException $e) {
                $errors[] = $e->getMessage();
            }
        }

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $body = trim($body);

        if (! $body) {
            $errors[] = 'Body is empty or missing.';
        }

        if ($errors) {
            $errors = implode(', ', $errors);
            throw new ParseException("Failed to parse $basepath: $errors");
        } else {
            $data['body'] = $body;
            return new Post(...$data);
        }
    }

    /**
     * Validate a header, returning its native type.
     *
     * @param string $name  Name of the header.
     * @param string $value Value of the header.
     *
     * @return mixed
     *
     * @throws ParseException when header is invalid
     */
    protected function processHeader($name, $value): mixed
    {
        $name = strtolower($name);

        if (! $value) {
            throw new ParseException("Header '$name' must have a value");
        }

        switch ($name) {
            case 'title':
                return $value;

            case 'tags':
                $tags = array_map('trim', explode(',', $value));
                natsort($tags);
                return array_map(
                    fn ($name) => new Tag($name),
                    $tags
                );

            case 'slug':
                if (! preg_match('/^[a-z0-9-]+$/', $value)) {
                    throw new ParseException("Header 'slug' is invalid: '$value'");
                }
                return $value;

            case 'date':
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
                if ($date === false) {
                    throw new ParseException("Date header is invalid: '$value'");
                }
                return $date;

            default:
                throw new ParseException("Header '$name' is unknown");
        }
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
