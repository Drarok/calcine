<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Calcine\Path;

class PathTest extends TestCase
{
    #[DataProvider('joinDataProvider')]
    public function testJoin($expected, ...$args)
    {
        $actual = Path::join(...$args);
        $this->assertEquals($expected, $actual);
    }

    public static function joinDataProvider()
    {
        return [
            [''],
            ['/usr/local/bin/php', '/usr', 'local', 'bin/', 'php'],
            ['/usr/local/bin/php', '/usr', 'local', 'bin/', 'php/'],
            ['/usr/local/bin/php', '/usr', '/local/', 'bin/', 'php/'],
        ];
    }
}
