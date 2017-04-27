<?php

namespace Calcine\Tests\Template\Engine;

use Calcine\Template\Engine\Markdown;

class MarkdownTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Markdown
     */
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Markdown();
    }

    /**
     * @param string $source Markdown source
     * @param string $expected Expected output
     *
     * @dataProvider renderDataProvider
     */
    public function testRender($source, $expected)
    {
        $actual = $this->object->render($source);
        $this->assertEquals($expected, $actual);
    }

    public function renderDataProvider()
    {
        return array(
            array('', ''),
            array('Plain string', '<p>Plain string</p>'),
            array('_Italic_ string', '<p><em>Italic</em> string</p>'),
            array('*Italic* string', '<p><em>Italic</em> string</p>'),
            array('__Bold__ string', '<p><strong>Bold</strong> string</p>'),
            array('**Bold** string', '<p><strong>Bold</strong> string</p>'),
        );
    }
}
