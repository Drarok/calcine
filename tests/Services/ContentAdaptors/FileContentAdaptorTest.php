<?php declare(strict_types=1);

namespace Calcine\Tests\Services\ContentAdaptors;

use PHPUnit\Framework\TestCase;

use Calcine\Services\ContentAdaptors\ContentAdaptorException;
use Calcine\Services\ContentAdaptors\FileContentAdaptor;

final class FileContentAdaptorTest extends TestCase
{
    public function testThatGivenValidSetupWhenLoadContentCalledThenContentsAreCorrect(): void
    {
        $rootDir = realpath(__DIR__ . '/../../posts');
        $sut = new FileContentAdaptor($rootDir);
        $content = $sut->loadContent();
        $this->assertEquals(3, count($content));

        $expectedSlugs = ['test-blog-post', 'test-blog-post-2', 'test-blog-post-3'];
        $actualSlugs = array_map(fn (array $data) => $data['slug'], $content);
        $this->assertEquals($expectedSlugs, $actualSlugs);
    }

    public function testThatGivenInvalidPathWhenLoadContentCalledThenExceptionThrown(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $sut = new FileContentAdaptor('/invalid/path');
        $sut->loadContent();
    }

    public function testThatGivenInvalidFilesWhenLoadContentCalledThenExceptionIsThrown(): void
    {
        $this->expectException(ContentAdaptorException::class);

        $rootDir = realpath(__DIR__ . '/../../posts/invalid');
        $sut = new FileContentAdaptor($rootDir);
        $sut->loadContent();
    }
}
