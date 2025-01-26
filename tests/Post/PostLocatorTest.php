<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use Calcine\Post;
use Calcine\Post\PostLocator;

// use Calcine\PostsProvider\FilePostsProvider;
// use Calcine\Template\Engine\EngineFactory;
// use Calcine\Template\TemplateRenderer;
// use Calcine\User;

class PostLocatorTest extends TestCase
{
    private ?array $testPosts;
    private ?PostLocator $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->testPosts = [
            $this->makeTestPost(),
        ];
        $this->sut = new PostLocator('/tmp', $this->testPosts);
    }

    public function tearDown(): void
    {
        $this->testPosts = null;
        $this->sut = null;

        parent::tearDown();
    }

    public function testPathForPost(): void
    {
        $path = $this->sut->pathForPost($this->testPosts[0]);
        $this->assertEquals('/tmp/2025/01/27/example-post-title.html', $path);
    }

    public function testPostForPathWithValidPath(): void
    {
        $post = $this->sut->postForPath('/tmp/2025/01/27/example-post-title.html');
        $this->assertNotNull($post);
    }

    public function testPostForPathWithInvalidPath(): void
    {
        $post = $this->sut->postForPath('/tmp/2000/01/01/example-post-title.html');
        $this->assertNull($post);
    }

    private function makeTestPost(): Post
    {
        return new Post(
            title: 'Example Post Title',
            tags: [],
            slug: 'example-post-title',
            date: \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', '2025-01-27 12:15:00'),
            body: 'Example _post_ body',
        );
    }
}
