<?php

namespace Calcine\Tests\Template\Engine;

use Calcine\Template\Engine\PlainText;

class PlainTextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlainText
     */
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new PlainText();
    }

    /**
     * @param string $source Plain text source
     *
     * @dataProvider renderDataProvider
     */
    public function testRender($source)
    {
        $expected = $source;
        $actual = $this->object->render($source);
        $this->assertEquals($expected, $actual);
    }

    public function renderDataProvider()
    {
        return array(
            array(''),
            array('Plain string'),
            array('_Italic_ string'),
            array('*Italic* string'),
            array('__Bold__ string'),
            array('**Bold** string'),
        );
    }
}
