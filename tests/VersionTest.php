<?php

namespace Calcine\Tests;

use Calcine\Version;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $version = Version::getVersion();
        $this->assertRegExp('/^\d+\.\d+\.\d+$/', $version);
    }
}
