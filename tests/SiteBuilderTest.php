<?php declare(strict_types=1);

namespace Calcine\Tests;

use PHPUnit\Framework\TestCase;
use Calcine\PostsProvider\FilePostsProvider;
use Calcine\SiteBuilder;
use Calcine\Template\Engine\EngineFactory;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class SiteBuilderTest extends TestCase
{
    /**
     * @var SiteBuilder
     */
    private $builder;

    public function setUp(): void
    {
        parent::setUp();

        $user = new User('Alice Foobar', 'alice.foobar@example.org');
        $templatesPath = __DIR__ . '/../app/templates';
        $webPath = __DIR__ . '/../tmp/web';
        $renderer = new TemplateRenderer($user, $templatesPath, $webPath);

        $postsPath = __DIR__ . '/posts';
        $postsProvider = new FilePostsProvider($postsPath);

        $this->builder = new SiteBuilder($renderer, $postsProvider);
    }

    public function testBuild()
    {
        $this->builder->build();

        // TODO: We need some assertions here
    }
}
