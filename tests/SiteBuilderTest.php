<?php

namespace Calcine\Tests;

use Calcine\SiteBuilder;
use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class SiteBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        parent::setUp();

        $engine = EngineFactory::createInstance('markdown');

        $user = new User('Alice Foobar', 'alice.foobar@example.org');
        $templatesPath = __DIR__ . '/../app/templates';
        $webPath = __DIR__ . '/../tmp/web';
        $renderer = new TemplateRenderer($user, $templatesPath, $webPath);

        $path = __DIR__ . '/../posts';

        $this->builder = new SiteBuilder($engine, $renderer, $path);
    }

    public function testBuild()
    {
        $this->builder->build();
    }
}
