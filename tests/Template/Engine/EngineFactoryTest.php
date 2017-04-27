<?php

namespace Calcine\Tests\Template\Engine;

use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\Engine\Markdown;
use Calcine\Template\Engine\PlainText;

class EngineFactoryTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(\Exception::class, "Invalid rendering engine: 'invalid'");
        EngineFactory::createInstance('invalid');
    }
}
