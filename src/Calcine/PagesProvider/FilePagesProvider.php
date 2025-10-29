<?php

declare(strict_types=1);

namespace Calcine\PagesProvider;

use Calcine\Model\Page;

/*
class FilePagesProvider extends AbstractPagesProvider
{
    private FilePostParser $parser;

    public function __construct(private string $rootPath)
    {
        $this->parser = new FilePageParser();
    }

    protected function loadPages(): array
    {
        $pages = [];

        $dir = new \DirectoryIterator($this->rootPath);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            $pages[] = $this->parser->parse($fileInfo->getPathname());
        }

        return $pages;
    }
}


class FilePagesProvider_Old extends AbstractPagesProvider
{
    public function __construct(
        private array $headers,
        private string $rootURL
    ) {
    }

    protected function loadPages(): array
    {
        $iterator = $this->fetchPages();
        return iterator_to_array($iterator);
    }

    private function fetchPages(): \Generator
    {
        $limit = 5;
        $start = 0;

        do {
            $params = [
                'fields' => ['title', 'slug', 'body'],
                'sort' => 'slug:asc',
                'pagination' => [
                    'start' => $start,
                    'limit' => $limit,
                ],
            ];

            $url = $this->makeURL(self::TYPE_PAGES, $params);
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
            'slug',
            'body',
        ];

        $cleanData = [];
        foreach ($keys as $key) {
            $cleanData[$key] = trim($data[$key] ?? '');
        }
        return new Page(...$cleanData);
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
*/
