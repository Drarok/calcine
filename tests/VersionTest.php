<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use Calcine\Version;

class VersionTest extends TestCase
{
    public function testGetters()
    {
        $version = Version::getVersion();
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
    }
}
