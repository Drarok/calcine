<?php

namespace Calcine\Tests;

use Calcine\Template\Engine\Markdown;
use Calcine\Post;
use Calcine\Post\Tag;

class PostTest extends \PHPUnit_Framework_TestCase
{
    private $engine;
    private $errorLevel;

    public function setUp()
    {
        parent::setUp();
        $this->engine = new Markdown();

        // This is required so we can test that fopen failures are handled.
        $this->errorLevel = error_reporting(0);
    }

    protected function tearDown()
    {
        parent::tearDown();
        error_reporting($this->errorLevel);
    }

    public function testFailureWhenNoFile()
    {
        $this->setExpectedException('Exception', 'Cannot open /path/to/nowhere');
        new Post($this->engine, '/path/to/nowhere');
    }

    /**
     * Test the parsing of a post.
     *
     * @param array  $expected Expected data, keyed on property name.
     * @param string $pathname Pathname to the post file.
     *
     * @return void
     *
     * @dataProvider validPostsDataProvider
     */
    public function testValidPosts($expected, $pathname)
    {
        $post = new Post($this->engine, $pathname);

        $this->assertEquals($expected['title'], $post->getTitle());
        $this->assertEquals($expected['tags'], $post->getTags());
        $this->assertEquals($expected['slug'], $post->getSlug());
        $this->assertEquals($expected['date'], $post->getDate());
        $this->assertEquals($expected['body'], $post->getBody());
        $this->assertEquals($expected['renderedBody'], $post->getRenderedBody());
    }

    /**
     * Provider for testValidPosts.
     *
     * @return array
     */
    public function validPostsDataProvider()
    {
        return $this->getDataProviderForDirectory(__DIR__ . '/posts/valid');
    }

    /**
     * Test invalid post files.
     *
     * @param string $expectedException Expected exception class.
     * @param string $expectedMessage   Expected exception message.
     * @param string $pathname
     *
     * @return void
     *
     * @dataProvider invalidPostsDataProvider
     */
    public function testInvalidPosts($expectedException, $expectedMessage, $pathname)
    {
        $this->setExpectedException($expectedException, $expectedMessage);
        $post = new Post($this->engine, $pathname);
    }

    /**
     * Provider for testInvalidPosts.
     *
     * @return array
     */
    public function invalidPostsDataProvider()
    {
        return $this->getDataProviderForDirectory(__DIR__ . '/posts/invalid');
    }

    protected function getDataProviderForDirectory($path)
    {
        $result = [];
        $dir = new \DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileinfo->getExtension() != 'xml') {
                continue;
            }

            $postPathname = $path . '/' . $fileinfo->getBasename('.' . $fileinfo->getExtension()) . '.markdown';

            $xml = simplexml_load_file($fileinfo->getPathname());

            if ($xml->exception) {
                $result[] = array(
                    (string) $xml->exception->class,
                    (string) $xml->exception->message,
                    $postPathname,
                );

                continue;
            }

            $post = $xml->post;

            $expected = array(
                'title'        => (string) $post->title,
                'tags'         => array(),
                'slug'         => (string) $post->slug,
                'date'         => \DateTime::createFromFormat('Y-m-d H:i:s', (string) $post->date),
                'body'         => (string) $post->body,
                'renderedBody' => (string) $post->renderedBody,
            );

            foreach ($post->tag as $tag) {
                $expected['tags'][] = new Tag((string) $tag);
            }

            $result[] = array(
                $expected,
                $postPathname,
            );
        }

        return $result;
    }
}
