<?php declare(strict_types=1);

namespace Calcine\Tests\Template;

use PHPUnit\Framework\TestCase;
use Calcine\Path;
use Calcine\Post;
use Calcine\Post\Tag;
use Calcine\Template\TemplateRenderer;
use Calcine\User;

class TemplateRendererTest extends TestCase
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

    public function setUp(): void
    {
        parent::setUp();

        $user = new User('Eva Smith', 'esmith@example.org');
        $this->templatesPath = realpath(__DIR__ . '/../../app/templates');
        $this->webPath = $this->makeTemporaryDirectory();

        $this->object = new TemplateRenderer($user, $this->templatesPath, $this->webPath);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (!is_dir($this->webPath)) {
            return;
        }

        shell_exec('rm -rf ' . escapeshellarg($this->webPath));
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
        $this->expectExceptionMessage('Failed to create asset path:');

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
        $this->markTestSkipped('Needs refactor');

        $actualOutputPath = $this->webPath . '/2000/01/01/test-blog-post.html';
        if (file_exists($actualOutputPath)) {
            unlink($actualOutputPath);
        }

        $this->object->renderPost($this->makeTestPost());

        $expectedOutputPath = __DIR__ . '/data/test-blog-post.html';
        $this->assertFileEquals($expectedOutputPath, $actualOutputPath);
    }

    public function testRenderTags()
    {
        $this->markTestSkipped('Needs refactor');

        $posts = [
            $this->makeTestPost(),
        ];
        $tags = [
            new Tag('PHP', $posts),
            new Tag('Code', $posts),
        ];
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
        $this->markTestSkipped('Needs refactor');

        $posts = [
            $this->makeTestPost(),
        ];

        $key = '2000/01';
        $archives = [
            $key => [
                'name' => 'January 2000',
                'posts' => $posts,
            ],
        ];
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

        $this->expectExceptionMessage('Failed to create template destination:');
        $this->testRenderSiteIndex();
    }

    public function testRenderSiteIndex()
    {
        $this->markTestSkipped('Needs refactor');

        $posts = [
            $this->makeTestPost(),
        ];

        $this->object->renderSiteIndex($posts);

        $expected = __DIR__ . '/data/site-index.html';
        $actual = $this->webPath . '/index.html';
        $this->assertFileEquals($expected, $actual);
    }

    private function makeTemporaryDirectory(): string
    {
        $tmpRoot = sys_get_temp_dir();

        do {
            $id = mt_rand(1, 99999);
            $dir = sprintf('%s/calcine-%05d', $tmpRoot, $id);
        } while (is_dir($dir));

        mkdir($dir);

        return $dir;
    }

    private function makeTestPost(): Post
    {
        $tags = [
            new Tag('Tag'),
            new Tag('Test'),
            new Tag('PHP'),
        ];
        return new Post(
            title: 'Test Blog Post',
            tags: $tags,
            slug: 'test-blog-post',
            date: \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', '2000-01-01 00:00:00'),
            body: "This is the first paragraph.\n\nThis is the second paragraph.\n",
        );
    }
}
