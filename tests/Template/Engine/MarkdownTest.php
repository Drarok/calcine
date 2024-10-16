<?php declare(strict_types=1);

namespace Calcine\Tests\Template\Engine;

use PHPUnit\Framework\TestCase;
use Calcine\Template\Engine\Markdown;

class MarkdownTest extends TestCase
{
    /**
     * @var Markdown
     */
    private $object;

    public function setUp(): void
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

    public static function renderDataProvider()
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
