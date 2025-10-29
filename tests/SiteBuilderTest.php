<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use Calcine\SiteBuilder;
use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class SiteBuilderTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->markTestSkipped('SiteBuilderTest needs major refactoring');

        parent::setUp();

        $user = new User('Alice Foobar', 'alice.foobar@example.org');
        $templatesPath = __DIR__ . '/../app/templates';
        $webPath = __DIR__ . '/../tmp/web';
        $renderer = new TemplateRenderer($user, $templatesPath, $webPath);

        // TODO: Create SiteBuilder instance
        $this->builder = new SiteBuilder(/* ... */);
    }

    public function testBuild()
    {
        $this->builder->build();

        // TODO: We need some assertions here
    }
}
