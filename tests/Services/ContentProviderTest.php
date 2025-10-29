<?php declare(strict_types=1);

namespace Calcine\Tests\Services;

use PHPUnit\Framework\TestCase;
use Calcine\Services\ContentAdaptors\ContentAdaptorInterface;
use Calcine\Services\ContentProvider;
use Calcine\Services\ContentProviderInterface;
use Calcine\Tests\Mocks\MockContentAdaptor;

require __DIR__ . '/../Mocks/MockContentAdaptor.php';

final class ContentProviderTest extends TestCase
{
    private ?MockContentAdaptor $pagesAdaptor = null;
    private ?MockContentAdaptor $postsAdaptor = null;

    private ?ContentProviderInterface $sut = null;

    public function setUp(): void
    {
        parent::setUp();

        $pagesAdaptor = $this->pagesAdaptor = new MockContentAdaptor();
        $postsAdaptor = $this->postsAdaptor = new MockContentAdaptor();

        $this->sut = new ContentProvider(
            $pagesAdaptor,
            $postsAdaptor,
        );
    }

    public function testThat_GivenPageAdaptor_WhenGetPagesCalled_ThenContentIsCorrect()
    {
        $expectedContent = [
            $this->makePage(1),
            $this->makePage(2),
        ];

        $this->pagesAdaptor->onLoadContent = function () use ($expectedContent) {
            return $expectedContent;
        };

        $actualContent = $this->sut->getPages();
        $this->assertEquals(2, count($actualContent));

        foreach ($actualContent as $idx => $page) {
            $this->assertEquals(sprintf('Title %d', $idx + 1), $page->title);
        }
    }

    private function makePage(int $index): array
    {
        return [
            'title' => "Title $index",
            'slug' => "slug-$index",
            'body' => "Body $index",
        ];
    }

    public function testThat_GivenPostAdaptor_WhenGetPostsCalled_ThenContentIsCorrect()
    {
        $expectedContent = [
            $this->makePost(1),
            $this->makePost(2),
        ];

        $this->postsAdaptor->onLoadContent = function () use ($expectedContent) {
            return $expectedContent;
        };

        $actualContent = $this->sut->getPosts();
        $this->assertEquals(2, count($actualContent));

        foreach ($actualContent as $idx => $post) {
            $this->assertEquals(sprintf('Title %d', $idx + 1), $post->title);
        }
    }

    private function makePost(int $index): array
    {
        $title = "Title $index";
        $tags = ['Tag1', 'Tag2'];
        $slug = "title-$index";
        $date = '2025-10-29 04:23:00';
        $body = "## Example Body $index";
        return [
            'title' => $title,
            'tags' => $tags,
            'slug' => $slug,
            'date' => $date,
            'body' => $body,
        ];
        // return new Post($title, $tags, $slug, $date, $body);
    }

    public function testThat_GivenWarmedCache_WhenGetMethodsCalled_ThenAdaptorsAreNotCalled()
    {
        // Given
        $expectedPages = [
            $this->makePage(1),
            $this->makePage(2),
        ];
        $this->pagesAdaptor->onLoadContent = function () use ($expectedPages) {
            return $expectedPages;
        };

        $expectedPosts = [
            $this->makePost(1),
            $this->makePost(2),
        ];
        $this->postsAdaptor->onLoadContent = function () use ($expectedPosts) {
            return $expectedPosts;
        };

        $this->sut->getPages();
        $this->sut->getPosts();

        // When
        $throw = function () {
            throw new \Exception('Should not be called');
        };
        $this->pagesAdaptor->onLoadContent = $throw;
        $this->postsAdaptor->onLoadContent = $throw;

        // Then
        $actualPages = $this->sut->getPages();
        $actualPosts = $this->sut->getPosts();
        $this->assertEquals(2, count($actualPages));
        $this->assertEquals(2, count($actualPosts));
    }
}
