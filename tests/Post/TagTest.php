<?php declare(strict_types=1);

namespace Calcine\Test\Post;

use PHPUnit\Framework\TestCase;
use Calcine\Post\Tag;

class TagTest extends TestCase
{
    public function testGetters()
    {
        $name = '!!!Awesome Stuff!!!';
        $fakePosts = array('one', 'two', 'three');
        $tag = new Tag($name, $fakePosts);
        $this->assertEquals($name, $tag->getName());
        $this->assertEquals($fakePosts, $tag->getPosts());
        $this->assertEquals('awesome-stuff', $tag->getSlug());
    }
}
