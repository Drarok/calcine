<?php

declare(strict_types=1);

namespace Calcine\Strapi;

final class StrapiClient implements StrapiClientInterface
{
    private const string TYPE_PAGES = 'pages';
    private const string TYPE_POSTS = 'posts';

    public function __construct(
        private BasicHTTPClientInterface $http,
        private string $rootURL
    ) {
    }

    public function fetchPages(): \Generator
    {
        $fields = [
            'title',
            'slug',
            'body',
        ];
        yield from $this->fetch(StrapiContentType::Pages, $fields, 'slug:asc');
    }

    public function fetchPosts(): \Generator
    {
        $fields = [
            'title',
            'slug',
            'date',
            'body',
        ];
        $populate = ['tags' => ['fields' => 'name']];
        $generator = $this->fetch(StrapiContentType::Posts, $fields, 'date:desc', $populate);

        $unwrapTag = fn (array $tag) => $tag['name'];

        foreach ($generator as $post) {
            $post['tags'] = array_map($unwrapTag, $post['tags']);
            yield $post;
        }
    }

    private function unwrapTag(array $tag): string
    {
        return $tag['name'];
    }

    private function fetch(
        StrapiContentType $contentType,
        array $fields,
        string $sort,
        ?array $populate = null
    ): \Generator {
        $limit = 5;
        $start = 0;

        do {
            $params = [
                'fields' => $fields,
                'sort' => $sort,
                'pagination' => [
                    'start' => $start,
                    'limit' => $limit,
                ],
            ];

            if ($populate) {
                $params['populate'] = $populate;
            }

            $url = $this->makeURL($contentType->value, $params);
            $data = $this->makeRequest($url);

            foreach ($data['data'] as $entityData) {
                yield $entityData;
            }

            $pagination = $data['meta']['pagination'];
            $start += $limit;
            $remaining = $pagination['total'] - $start;
        } while ($remaining > 0);
    }

    private function makeURL(string $contentType, array $params): string
    {
        return sprintf("%s/%s%s", $this->rootURL, $contentType, $this->makeParams($params));
    }

    private function makeParams(array $params): string
    {
        return '?' . http_build_query($params);
    }

    private function makeRequest(string $url): array
    {
        $data = $this->http->get($url);
        return json_decode($data, true);
    }
}
