<?php

namespace Calcine\Tests;

use Calcine\Post;

class PostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the parsing of a post.
     *
     * @return void
     *
     * @dataProvider postDataProvider
     */
    public function testPost($expected, $pathname)
    {
        $post = new Post($pathname);

        $this->assertEquals($expected['title'], $post->getTitle());
        $this->assertEquals($expected['tags'], $post->getTags());
        $this->assertEquals($expected['slug'], $post->getSlug());
        $this->assertEquals($expected['date'], $post->getDate());
        $this->assertEquals($expected['body'], $post->getBody());
    }

    public function postDataProvider()
    {
        $result = array();

        $dir = new \DirectoryIterator(__DIR__ . '/data/xml');

        foreach ($dir as $fileinfo) {
            if ($dir->isDot()) {
                continue;
            }

            $xml = simplexml_load_file($fileinfo->getPathname());

            $expected = array(
                'title' => (string) $xml->title,
                'tags'  => array(),
                'slug'  => (string) $xml->slug,
                'date'  => (string) $xml->date,
                'body'  => (string) $xml->body,
            );

            foreach ($xml->tag as $tag) {
                $expected['tags'][] = (string) $tag;
            }

            $result[] = array(
                $expected,
                __DIR__ . '/data/' . $fileinfo->getBasename('.' . $fileinfo->getExtension()) . '.markdown'
            );
        }

        return $result;
    }
}
