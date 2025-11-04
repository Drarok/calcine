<?php declare(strict_types=1);

namespace Calcine\Tests\Mocks;

use Calcine\Services\ContentProviderInterface;

final class MockContentProvider implements ContentProviderInterface
{
    public string $title = 'title';

    public string $description = 'description';

    public array $pages = [];

    public array $posts = [];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getPosts(): array
    {
        return $this->posts;
    }
}
