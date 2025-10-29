<?php declare(strict_types=1);

namespace Calcine\Tests\PostsProvider;

use PHPUnit\Framework\TestCase;
use Calcine\Post;
use Calcine\PostsProvider\FilePostsProvider;

class FilePostsProviderTest extends TestCase
{
    private ?FilePostsProvider $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new FilePostsProvider(__DIR__ . '/../posts');
    }

    public function tearDown(): void
    {
        $this->sut = null;
        parent::tearDown();
    }

    public function testThat_GivenValidPosts_WhenGetPostsCalled_ThenPostsAreReturned(): void
    {
        $posts = $this->sut->getPosts();
        $this->assertEquals(3, count($posts));

        foreach ($posts as $post) {
            $expectedSlug = $this->makeSlug($post);
            $this->assertEquals($expectedSlug, $post->slug);
        }
    }

    private function makeSlug(Post $post): string
    {
        return strtolower(str_replace(' ', '-', $post->title));
    }
}
