<?php

namespace Calcine\Tests\Config;

use Calcine\Config\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser
     */
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Parser(__DIR__ . '/data/parser.json');
    }

    public function testNoSuchFile()
    {
        $this->setExpectedException(\Exception::class, 'Cannot read file \'/tmp/calcine-no-such-file\'');
        new Parser('/tmp/calcine-no-such-file');
    }

    public function testInvalidFile()
    {
        $this->setExpectedException(\Exception::class, 'Syntax error');
        new Parser(__DIR__ . '/data/invalid.json');
    }

    /**
     * Test the get method.
     *
     * @param mixed $expected
     * @param string $path
     *
     * @return void
     *
     * @dataProvider getDataProvider
     */
    public function testGet($expected, $path)
    {
        $this->assertEquals($expected, $this->object->get($path, 'default'));
    }

    public function getDataProvider()
    {
        $testData = json_decode(file_get_contents(__DIR__ . '/data/parser.json'), true);

        return array(
            array($testData['s01']['s01']['v01'], 's01.s01.v01'),
            array($testData['s01']['s01']['v02'], 's01.s01.v02'),
            array($testData['s01']['s01']['v03'], 's01.s01.v03'),

            array($testData['s01']['s02']['v01'], 's01.s02.v01'),
            array($testData['s01']['s02']['v02'], 's01.s02.v02'),
            array($testData['s01']['s02']['v03'], 's01.s02.v03'),

            array($testData['s02']['s01']['v01'], 's02.s01.v01'),
            array($testData['s02']['s01']['v02'], 's02.s01.v02'),
            array($testData['s02']['s01']['v03'], 's02.s01.v03'),

            array($testData['s02']['s02']['v01'], 's02.s02.v01'),
            array($testData['s02']['s02']['v02'], 's02.s02.v02'),
            array($testData['s02']['s02']['v03'], 's02.s02.v03'),

            array($testData['s01']['s01'], 's01.s01'),
            array($testData['s01']['s02'], 's01.s02'),

            array($testData['s02']['s01'], 's02.s01'),
            array($testData['s02']['s02'], 's02.s02'),

            array($testData['s01'], 's01'),
            array($testData['s02'], 's02'),

            array('default', 's01.s01.v04'),
            array('default', 's01.s03'),
            array('default', 's03'),
        );
    }
}
