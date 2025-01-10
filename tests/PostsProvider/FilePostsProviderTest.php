<?php declare(strict_types=1);

namespace Calcine\Tests\PostsProvider;

use PHPUnit\Framework\TestCase;
use Calcine\PostsProvider\FilePostsProvider;

class FilePostsProviderTest extends TestCase
{
    private ?FilePostsProvider $postsProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->postsProvider = new FilePostsProvider(__DIR__ . '/../posts');
    }

    public function tearDown(): void
    {
        $this->postsProvider = null;
        parent::tearDown();
    }

    public function testValidPosts()
    {
        $posts = $this->postsProvider->getPosts();
        $this->assertEquals(count($posts), 3);
    }

    public function testInvalidBody()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage('Body is empty or missing.');
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/invalid-body.markdown');
    }

    public function testInvalidDate()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Date header is invalid: '2014-99-99'");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/invalid-date.markdown');
    }

    public function testInvalidHeader()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Unknown header in invalid-header.markdown: 'invalidheader'.");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/invalid-header.markdown');
    }

    public function testInvalidLine()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage('Invalid header line');
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/invalid-line.markdown');
    }

    public function testInvalidSlug()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Header 'slug' is invalid: 'Invalid Slug'");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/invalid-slug.markdown');
    }

    public function testMissingBody()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage('Body is empty or missing.');
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/missing-body.markdown');
    }

    public function testMissingDate()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Header 'date' is empty or missing.");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/missing-date.markdown');
    }

    public function testMissingSlug()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Header 'slug' is empty or missing.");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/missing-slug.markdown');
    }

    public function testMissingTags()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Header 'tags' is empty or missing.");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/missing-tags.markdown');
    }

    public function testMissingTitle()
    {
        $this->expectException(\Calcine\PostsProvider\ParseException::class);
        $this->expectExceptionMessage("Header 'title' is empty or missing.");
        $this->postsProvider->loadPost(__DIR__ . '/../posts/invalid/missing-title.markdown');
    }
}
