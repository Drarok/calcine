<?php declare(strict_types=1);

namespace Calcine\Tests\Template;

use PHPUnit\Framework\TestCase;

use Calcine\Model\BuildStats;
use Calcine\Model\Page;
use Calcine\Model\Post;
use Calcine\Model\Tag;
use Calcine\Model\User;
use Calcine\Path;
use Calcine\Services\ContentProviderInterface;
use Calcine\Template\TemplateRenderer;
use Calcine\Tests\Mocks\MockContentProvider;

class TemplateRendererTest extends TestCase
{
    private string $templatesPath = '';
    private string $webPath = '';

    private ?MockContentProvider $content;

    public function setUp(): void
    {
        parent::setUp();

        $this->templatesPath = realpath(__DIR__ . '/../../app/templates');
        $this->webPath = $this->makeTemporaryDirectory();
        $this->content = new MockContentProvider();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (!is_dir($this->webPath)) {
            return;
        }

        shell_exec('rm -rf ' . escapeshellarg($this->webPath));
    }

    public function testCopyAssetsFailure()
    {
        $this->expectExceptionMessage('Failed to create asset path:');

        $sut = $this->makeSut(null, '/invalid/path');
        $sut->copyAssets();
    }

    public function testCopyAssetsWithDefaultTheme()
    {
        $sut = $this->makeSut();
        $sut->copyAssets();

        $source = Path::join(realpath($this->templatesPath), 'default', 'css', 'site.css');
        $destination = Path::join(realpath($this->webPath), 'css', 'site.css');
        $this->assertFileEquals($source, $destination);
    }

    public function testCopyAssetsWithCustomTheme()
    {
        $sut = $this->makeSut(null, null, 'lumen');
        $sut->copyAssets();

        $source = Path::join(realpath($this->templatesPath), 'lumen', 'css', 'bootstrap.min.css');
        $destination = Path::join(realpath($this->webPath), 'css', 'bootstrap.min.css');
        $this->assertFileEquals($source, $destination);

        $source = Path::join(realpath($this->templatesPath), 'default', 'css', 'site.css');
        $destination = Path::join(realpath($this->webPath), 'css', 'site.css');
        $this->assertFileEquals($source, $destination);
    }

    public function testBuild(): void
    {
        $this->content->pages = [
            $this->makePage(),
        ];
        $this->content->posts = [
            $this->makePost(),
        ];

        $sut = $this->makeSut();
        $stats = $sut->build();

        $expectedWebFiles = [
            'index.html',
            'pages/test-page-slug.html',
            '2025/11/04/test-post-slug.html',
            '2025/11/index.html',
        ];

        foreach ($expectedWebFiles as $name) {
            $pathname = Path::join(
                realpath($this->webPath),
                $name,
            );

            $this->assertFileExists($pathname);
        }
    }

    private function makeSut(
        ?string $templatesPath = null,
        ?string $webPath = null,
        string $theme = 'default'
    ): TemplateRenderer {
        $user = new User('Eva Smith', 'esmith@example.org');

        return new TemplateRenderer(
            $this->content,
            $user,
            $templatesPath ?? $this->templatesPath,
            $webPath ?? $this->webPath,
            $theme,
        );
    }

    // TODO: Refactor this to be reusable.
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

    private function makePage(): Page
    {
        return new Page(
            'Test Page Title',
            'test-page-slug',
            'This is a _test_ page'
        );
    }

    private function makePost(): Post
    {
        $tags = [
            new Tag('Tag1'),
            new Tag('Tag2'),
        ];
        return new Post(
            'Test Post Title',
            $tags,
            'test-post-slug',
            \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', '2025-11-04 20:07:00'),
            'This is a _test_ post'
        );
    }
}
