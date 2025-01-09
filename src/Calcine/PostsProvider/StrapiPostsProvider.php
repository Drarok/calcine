<?php

declare(strict_types=1);

namespace Calcine\PostsProvider;

use Calcine\Post;
use Calcine\Post\Tag;

class StrapiPostsProvider extends AbstractPostsProvider {
    private const string TYPE_POSTS = 'posts';
    private const string TYPE_TAGS = 'tags';

    public function __construct(
        private array $headers,
        private string $rootURL
    ) {
    }

    protected function loadPosts(): array
    {
        $iterator = $this->fetchPosts();
        return iterator_to_array($iterator);
    }

    private function fetchPosts(): \Generator
    {
        $limit = 5;
        $start = 0;

        do {
            $params = [
                'fields' => ['title', 'slug', 'date', 'body'],
                'sort' => 'date:desc',
                'populate' => ['tags' => ['fields' => 'name']],
                'pagination' => [
                    'start' => $start,
                    'limit' => $limit,
                ],
            ];

            $url = $this->makeURL(self::TYPE_POSTS, $params);
            $data = $this->makeRequest($url);

            foreach ($data['data'] as $postData) {
                yield $this->makePost($postData);
            }

            $pagination = $data['meta']['pagination'];
            $start += $limit;
            $remaining = $pagination['total'] - $start;
        } while ($remaining > 0);
    }

    private function makePost(array $data): Post
    {
        $keys = [
            'title',
            'tags',
            'slug',
            'date',
            'body',
        ];

        $cleanData = [];
        foreach ($keys as $key) {
            $cleanData[$key] = $this->parse($key, $data[$key] ?? '');
        }
        return new Post(...$cleanData);
    }

    private function parse(string $key, mixed $value): mixed
    {
        switch ($key) {
            case 'title':
            case 'slug':
            case 'body':
                return trim($value);

            case 'tags':
                return array_map(
                    fn ($tag) => new Tag(trim($tag['name'])),
                    $value
                );

            case 'date':
                // 2025-01-09T18:08:00.000Z
                $format = '!' . \DateTimeInterface::RFC3339_EXTENDED;
                return \DateTimeImmutable::createFromFormat($format, $value);
        }
    }

    private function makeURL(string $contentType, array $params = []): string
    {
        return sprintf("%s/%s%s", $this->rootURL, $contentType, $this->makeParams($params));
    }

    private function makeParams(array $params): string
    {
        if (!$params) {
            return '';
        }

        return '?' . http_build_query($params);
    }

    private function makeRequest(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $data = curl_exec($ch);

        if (($errno = curl_errno($ch))) {
            $error = curl_error($ch);
            throw new \Exception("$errno: $error");
        }

        return json_decode($data, true);
    }
}
