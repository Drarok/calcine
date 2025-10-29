<?php declare(strict_types=1);

namespace Calcine\Tests\Post;

use PHPUnit\Framework\TestCase;
use Calcine\Post;
use Calcine\Post\FilePostParser;

class FilePostParserTest extends TestCase
{
    private ?FilePostParser $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new FilePostParser();
    }

    public function tearDown(): void
    {
        $this->sut = null;
        parent::tearDown();
    }

    public function testValidPosts()
    {
        $testFiles = [
            'test-blog-post.markdown',
            'test-blog-post-2.markdown',
            'test-blog-post-3.markdown',
        ];

        foreach ($testFiles as $filename) {
            $path = $this->makePath($filename);
            $post = $this->sut->parse($path);
            $slug = basename($filename, '.markdown');
            $this->assertEquals($slug, $post->slug);
        }
    }

    public function testInvalidBody()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage('Body is missing or empty');
        $path = $this->makePath('invalid/invalid-body.markdown');
        $this->sut->parse($path);
    }

    public function testInvalidDate()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'date' is invalid: '2014-99-99'");
        $path = $this->makePath('invalid/invalid-date.markdown');
        $this->sut->parse($path);
    }

    public function testInvalidSlug()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'slug' is invalid: 'Invalid Slug'");
        $path = $this->makePath('invalid/invalid-slug.markdown');
        $this->sut->parse($path);
    }

    public function testMissingBody()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage('Body is missing or empty');
        $path = $this->makePath('invalid/missing-body.markdown');
        $this->sut->parse($path);
    }

    public function testMissingDate()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'date' is missing or empty");
        $path = $this->makePath('invalid/missing-date.markdown');
        $this->sut->parse($path);
    }

    public function testMissingSlug()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'slug' is missing or empty");
        $path = $this->makePath('invalid/missing-slug.markdown');
        $this->sut->parse($path);
    }

    public function testMissingTags()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'tags' is missing or empty");
        $path = $this->makePath('invalid/missing-tags.markdown');
        $this->sut->parse($path);
    }

    public function testMissingTitle()
    {
        $this->expectException(\Calcine\Post\PostParserException::class);
        $this->expectExceptionMessage("Header 'title' is missing or empty");
        $path = $this->makePath('invalid/missing-title.markdown');
        $this->sut->parse($path);
    }

    private function makePath(string $name): string
    {
        return realpath(__DIR__ . '/../posts') . '/' . $name;
    }
}
