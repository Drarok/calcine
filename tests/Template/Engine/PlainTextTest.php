<?php declare(strict_types=1);

namespace Calcine\Tests\Template\Engine;

use PHPUnit\Framework\TestCase;
use Calcine\Template\Engine\PlainText;

class PlainTextTest extends TestCase
{
    /**
     * @var PlainText
     */
    private $object;

    public function setUp(): void
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

    public static function renderDataProvider()
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
