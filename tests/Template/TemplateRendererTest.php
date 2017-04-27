<?php

namespace Calcine\Tests\Template;

use Calcine\Path;
use Calcine\Post;
use Calcine\Post\Tag;
use Calcine\Template\Engine\Markdown;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class TemplateRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateRenderer
     */
    private $object;

    /**
     * @var string
     */
    private $templatesPath;

    /**
     * @var string
     */
    private $webPath;

    public function setUp()
    {
        parent::setUp();

        $user = new User('Eva Smith', 'esmith@example.org');
        $this->templatesPath = __DIR__ . '/../../app/templates';
        $this->webPath = __DIR__ . '/../../tmp/web';

        $webPath = realpath($this->webPath);
        if ($webPath) {
            shell_exec('rm -rf ' . escapeshellarg($webPath));
            mkdir($webPath, 0755, true);
        }

        $this->object = new TemplateRenderer($user, $this->templatesPath, $this->webPath);
    }

    public function testTheme()
    {
        $theme = 'lumen';

        $this->object->setTheme($theme);
        $this->assertEquals($theme, $this->object->getTheme());
    }

    public function testGlobal()
    {
        $global = 'This is a global var';

        $this->object->setGlobal('global', $global);
        $this->assertEquals($global, $this->object->getGlobal('global'));
    }

    public function testCopyAssetsFailure()
    {
        $this->expectException(\Exception::class, 'Failed to create asset path:');

        $user = new User('Eva Smith', 'esmith@example.org');
        $this->object = new TemplateRenderer($user, $this->templatesPath, '/invalid/path');

        $this->object->copyAssets();
    }

    public function testCopyAssets()
    {
        $this->object->setTheme('lumen');
        $this->object->copyAssets();

        $source = Path::join(realpath($this->templatesPath), 'lumen', 'css', 'bootstrap.min.css');
        $destination = Path::join(realpath($this->webPath), 'css', 'bootstrap.min.css');
        $this->assertFileEquals($source, $destination);

        $source = Path::join(realpath($this->templatesPath), 'default', 'css', 'site.css');
        $destination = Path::join(realpath($this->webPath), 'css', 'site.css');
        $this->assertFileEquals($source, $destination);
    }

    public function testRenderPost()
    {
        $actualOutputPath = $this->webPath . '/2000/01/01/test-blog-post.html';
        if (file_exists($actualOutputPath)) {
            unlink($actualOutputPath);
        }

        $engine = new Markdown();
        $post = new Post($engine, __DIR__ . '/data/test-blog-post.markdown');

        $this->object->renderPost($post);

        $expectedOutputPath = __DIR__ . '/data/test-blog-post.html';
        $this->assertFileEquals($expectedOutputPath, $actualOutputPath);
    }

    public function testRenderTags()
    {
        $engine = new Markdown();
        $posts = array(
            new Post($engine, __DIR__ . '/data/test-blog-post.markdown'),
        );
        $tags = array(
            new Tag('PHP', $posts),
            new Tag('Code', $posts),
        );
        $this->object->setGlobal('tags', $tags);
        $this->object->renderTags();

        $expected = __DIR__ . '/data/tags-index.html';
        $actual = $this->webPath . '/tags/index.html';
        $this->assertFileEquals($expected, $actual);

        $expected = __DIR__ . '/data/tags-php.html';
        $actual = $this->webPath . '/tags/php.html';
        $this->assertFileEquals($expected, $actual);
    }

    public function testRenderArchives()
    {
        $engine = new Markdown();
        $posts = array(
            new Post($engine, __DIR__ . '/data/test-blog-post.markdown'),
        );

        $key = '2000/01';
        $archives = array(
            $key => array(
                'name' => 'January 2000',
                'posts' => $posts,
            ),
        );
        $this->object->setGlobal('archives', $archives);
        $this->object->renderArchives();

        $expected = __DIR__ . '/data/archive.html';
        $actual = $this->webPath . '/2000/01/index.html';

        $this->assertFileEquals($expected, $actual);
    }

    public function testRenderSiteIndexFailure()
    {
        $user = new User('Eva Smith', 'esmith@example.org');
        $this->object = new TemplateRenderer($user, $this->templatesPath, '/invalid/path');

        $this->expectException(\Exception::class, 'Failed to create template destination:');
        $this->testRenderSiteIndex();
    }

    public function testRenderSiteIndex()
    {
        $engine = new Markdown();
        $posts = array(
            new Post($engine, __DIR__ . '/data/test-blog-post.markdown'),
        );

        $this->object->renderSiteIndex($posts);

        $expected = __DIR__ . '/data/site-index.html';
        $actual = $this->webPath . '/index.html';
        $this->assertFileEquals($expected, $actual);
    }
}
