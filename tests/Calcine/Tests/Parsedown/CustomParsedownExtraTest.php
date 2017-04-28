<?php

namespace Calcine\Tests\Parsedown;

use Calcine\Parsedown\CustomParsedownExtra;

class CustomParsedownExtraTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new CustomParsedownExtra();
    }

    /**
     * @param string $input
     * @param bool $isCode
     * @param int $attrCount
     *
     * @dataProvider dataProviderFencedCodeBlocks
     */
    public function testFencedCodeBlocks($input, $isCode, $attrCount)
    {
        $r = new \ReflectionObject($this->object);
        $method = $r->getMethod('blockFencedCode');
        $method->setAccessible(true);

        $line = [
            'text' => $input,
        ];
        $actual = $method->invoke($this->object, $line);
        $actualAttr = !empty($actual['element']['text']['attributes']) ? $actual['element']['text']['attributes'] : [];

        $this->assertEquals($isCode, (bool)$actual);
        $this->assertEquals($attrCount, count($actualAttr));
    }

    public function dataProviderFencedCodeBlocks()
    {
        return [
            ['`', false, 0],
            ['```', true, 0],
            ['```js', true, 1],
            ['``` js', true, 1],
            ['``` js ', true, 1],
            ['```{#id}', true, 1],
            ['```{#id .class}', true, 2],
            ['``` {#id}', true, 1],
            ['``` {#id .class}', true, 2],
            ['``` js {#id}', true, 2],
            ['``` js {#id .class}', true, 2],
            ['``` js {#id .class lang=fr}', true, 3],
            ['``` js{#id .class lang=fr}', true, 3],
            ['```js{#id .class lang=fr}', true, 3],
        ];
    }
}
