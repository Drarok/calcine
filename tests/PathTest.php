<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Calcine\Path;

class PathTest extends TestCase
{
    #[DataProvider('joinDataProvider')]
    public function testJoin($expected)
    {
        $params = array_slice(func_get_args(), 1);
        $actual = Path::join(...$params);
        $this->assertEquals($expected, $actual);
    }

    public static function joinDataProvider()
    {
        return array(
            array('/usr/local/bin/php', '/usr', 'local', 'bin/', 'php'),
            array('/usr/local/bin/php', '/usr', 'local', 'bin/', 'php/'),
            array('/usr/local/bin/php', '/usr', '/local/', 'bin/', 'php/'),
        );
    }
}
