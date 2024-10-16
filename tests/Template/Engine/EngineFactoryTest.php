<?php declare(strict_types=1);

namespace Calcine\Tests\Template\Engine;

use PHPUnit\Framework\TestCase;
use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\Engine\Markdown;
use Calcine\Template\Engine\PlainText;

class EngineFactoryTest extends TestCase
{
    public function testMarkdown()
    {
        $engine = EngineFactory::createInstance('markdown');
        $this->assertInstanceOf(Markdown::class, $engine);
    }

    public function testPlainText()
    {
        $engine = EngineFactory::createInstance('plaintext');
        $this->assertInstanceOf(PlainText::class, $engine);
    }

    public function testInvalid()
    {
        $this->expectException(\Exception::class, "Invalid rendering engine: 'invalid'");
        EngineFactory::createInstance('invalid');
    }
}
