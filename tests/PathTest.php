<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * Test the join method.
     *
     * @return void
     *
     * @dataProvider joinDataProvider
     */
    public function testJoin($expected)
    {
        $params = array_slice(func_get_args(), 1);
        $this->assertEquals($expected, call_user_func_array('Calcine\\Path::join', $params));
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
