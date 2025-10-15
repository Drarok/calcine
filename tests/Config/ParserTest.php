<?php declare(strict_types=1);

namespace Calcine\Tests\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Calcine\Config\Parser;

class ParserTest extends TestCase
{
    protected Parser $object;

    public function setUp(): void
    {
        parent::setUp();
        $this->object = new Parser(__DIR__ . '/data/parser.json');
    }

    public function testNoSuchFile()
    {
        $this->expectException(\Exception::class, 'Cannot read file \'/tmp/calcine-no-such-file\'');
        new Parser('/tmp/calcine-no-such-file');
    }

    public function testInvalidFile()
    {
        $this->expectException(\Exception::class, 'Syntax error');
        new Parser(__DIR__ . '/data/invalid.json');
    }

    #[DataProvider('getDataProvider')]
    public function testGet($expected, $path)
    {
        $this->assertEquals($expected, $this->object->get($path, 'default'));
    }

    public static function getDataProvider()
    {
        $testData = json_decode(file_get_contents(__DIR__ . '/data/parser.json'), true);

        return [
            [$testData['s01']['s01']['v01'], 's01.s01.v01'],
            [$testData['s01']['s01']['v02'], 's01.s01.v02'],
            [$testData['s01']['s01']['v03'], 's01.s01.v03'],

            [$testData['s01']['s02']['v01'], 's01.s02.v01'],
            [$testData['s01']['s02']['v02'], 's01.s02.v02'],
            [$testData['s01']['s02']['v03'], 's01.s02.v03'],

            [$testData['s02']['s01']['v01'], 's02.s01.v01'],
            [$testData['s02']['s01']['v02'], 's02.s01.v02'],
            [$testData['s02']['s01']['v03'], 's02.s01.v03'],

            [$testData['s02']['s02']['v01'], 's02.s02.v01'],
            [$testData['s02']['s02']['v02'], 's02.s02.v02'],
            [$testData['s02']['s02']['v03'], 's02.s02.v03'],

            [$testData['s01']['s01'], 's01.s01'],
            [$testData['s01']['s02'], 's01.s02'],

            [$testData['s02']['s01'], 's02.s01'],
            [$testData['s02']['s02'], 's02.s02'],

            [$testData['s01'], 's01'],
            [$testData['s02'], 's02'],

            ['default', 's01.s01.v04'],
            ['default', 's01.s03'],
            ['default', 's03'],
        ];
    }
}
