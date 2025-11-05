<?php declare(strict_types=1);

namespace Calcine\Template;

use Calcine\Model\ArchivePage;
use Calcine\Model\BuildStats;
use Calcine\Model\Page;
use Calcine\Path;
use Calcine\Model\Post;
use Calcine\Model\Tag;
use Calcine\Model\User;
use Calcine\Services\ContentProviderInterface;

use Twig\Loader\FilesystemLoader as TwigFileLoader;

class TemplateRenderer implements TemplateRendererInterface
{
    private CustomTwigEnvironment $twig;

    private string $theme;

    private array $globals = [
        'user'        => null,
        'tags'        => [], // Array<Tag>
        'archives'    => [], // Array<ArchivePage>
        'pages'       => [], // Array<Page>
    ];

    public function __construct(
        private ContentProviderInterface $content,
        User $user,
        private string $templatesPath,
        private string $webPath,
        string $theme = 'default',
    ) {
        $this->setGlobal('user', $user);

        $twigLoader = new TwigFileLoader();
        $this->twig = new CustomTwigEnvironment($twigLoader);

        $this->setTheme($theme);
    }

    /**
     * Build the site, returning how many pages of what type were built.
     */
    public function build(): BuildStats
    {
        $pages = $this->content->getPages();
        $this->setGlobal('pages', $pages);

        $posts = $this->content->getPosts();

        $tags = $this->collectTags($posts);
        $this->setGlobal('tags', $tags);

        $archives = $this->collectArchives($posts);
        $this->setGlobal('archives', $archives);

        $this->renderTags($tags);
        $this->renderArchives($archives);

        foreach ($pages as $page) {
            $this->renderPage($page);
        }

        foreach ($posts as $post) {
            $this->renderPost($post);
        }

        $this->renderSiteIndex($posts);

        $this->copyAssets();

        return new BuildStats(
            count($pages),
            count($posts),
            count($tags),
        );
    }

    private function setTheme(string $theme): self
    {
        $this->theme = $theme;

        $paths = [
            Path::join($this->templatesPath, $theme),
        ];

        if ($theme !== 'default') {
            $paths[] = Path::join($this->templatesPath, 'default');
        }

        $this->twig->getLoader()->setPaths($paths);

        return $this;
    }

    /**
     * Sets a global.
     *
     * @param string $key   Global name.
     * @param mixed  $value Global value.
     *
     * @return $this
     */
    private function setGlobal($key, $value): self
    {
        $this->globals[$key] = $value;
        return $this;
    }

    /**
     * Collect all the tags, with their posts attached.
     */
    private function collectTags(array $posts): array
    {
        $tags = [];
        $postsByTag = [];

        foreach ($posts as $post) {
            foreach ($post->tags as $tag) {
                $name = $tag->name;

                if (!array_key_exists($name, $tags)) {
                    $tags[$name] = $tag;
                }

                if (!array_key_exists($name, $postsByTag)) {
                    $postsByTag[$name] = [];
                }

                $postsByTag[$name][] = $post;
            }
        }

        $result = [];
        foreach ($postsByTag as $name => $posts) {
            $result[] = new Tag($name, $posts);
        }
        return $result;
    }

    /**
     * Collect all the posts, keyed on `date('Y/m')`, and in date order.
     */
    private function collectArchives(array $posts): array
    {
        $archives = [];
        foreach ($posts as $post) {
            $key = $post->date->format('Y/m');

            if (! array_key_exists($key, $archives)) {
                $archives[$key] = [
                    'name'  => $post->date->format('F Y'),
                    'posts' => [],
                ];
            }

            $archives[$key]['posts'][] = $post;
        }

        // Ensure archives are in date order.
        ksort($archives);

        // Ensure posts within each archive are ordered.
        $result = [];
        foreach ($archives as $slug => $data) {
            $name = $data['name'];
            $posts = $data['posts'];
            usort($posts, [Post::class, 'sort']);
            $result[] = new ArchivePage($name, $slug, $posts);
        }

        return $result;
    }

    /**
     * Copy template assets to the web directory.
     *
     * @throws \Exception when asset path cannot be created
     */
    public function copyAssets(): void
    {
        $assetTypes = ['css', 'fonts', 'img', 'js'];

        $assetsRootPaths = [];
        foreach ($assetTypes as $type) {
            $assetsRootPaths[] = [
                Path::join(realpath($this->templatesPath), $this->theme, $type),
                $type,
            ];

            if ($this->theme !== 'default') {
                $assetsRootPaths[] = [
                    Path::join(realpath($this->templatesPath), 'default', $type),
                    $type
                ];
            }
        }

        foreach ($assetsRootPaths as $pathInfo) {
            list($path, $type) = $pathInfo;

            if (!is_dir($path)) {
                continue;
            }

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $fileInfo) {
                if ($fileInfo->isDot() || $fileInfo->isDir()) {
                    continue;
                }

                $assetPath = Path::join($this->webPath, $type);
                if (!is_dir($assetPath)) {
                    $level = error_reporting(0);

                    try {
                        if (!mkdir($assetPath, 0755, true)) {
                            throw new \Exception('Failed to create asset path: ' . $assetPath);
                        }
                    } finally {
                        error_reporting($level);
                    }
                }

                $source = $fileInfo->getPathname();
                $destination = Path::join(realpath($this->webPath), $type, $fileInfo->getFilename());

                if (!file_exists($destination) || filemtime($destination) < $fileInfo->getMTime()) {
                    copy($source, $destination);
                }
            }
        }
    }

    /**
     * Render a post to its web destination.
     */
    private function renderPost(Post $post): void
    {
        $data = [
            'post' => $post,
        ];

        $postPathname = Path::join(
            $this->webPath,
            $post->date->format('Y/m/d'),
            $post->slug . '.html'
        );

        $this->render('post_page.html.twig', $data, $postPathname);
    }

    /**
     * Render a page to its web destination.
     */
    private function renderPage(Page $page): void
    {
        $data = [
            'page' => $page,
        ];

        $pagePathname = Path::join($this->webPath, 'pages', $page->slug . '.html');

        $this->render('page.html.twig', $data, $pagePathname);
    }

    /**
     * Render the tags index and pages.
     */
    private function renderTags(array $tags): void
    {
        $data = [
            'route' => 'tags',
        ];

        $tagsPathname = Path::join(
            $this->webPath,
            'tags',
            'index.html'
        );

        $this->render('tags.html.twig', $data, $tagsPathname);

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $data = [
                'route' => 'tag',
                'tag'   => $tag,
            ];

            $tagPathname = Path::join($this->webPath, 'tags', $tag->getSlug() . '.html');

            $this->render('tag.html.twig', $data, $tagPathname);
        }
    }

    /**
     * Render the archive pages.
     */
    private function renderArchives(array $archives): void
    {
        foreach ($archives as $archive) {
            $data = [
                'route'   => 'archive',
                'archive' => $archive,
            ];

            $archivePathname = Path::join($this->webPath, $archive->slug, 'index.html');

            $this->render('archive.html.twig', $data, $archivePathname);
        }
    }

    /**
     * Render the site index, displaying the first 30 posts for now.
     */
    private function renderSiteIndex(array $posts): void
    {
        $data = [
            'posts' => array_slice($posts, 0, 30),
            'route' => 'index',
        ];

        $indexPathname = Path::join($this->webPath, 'index.html');

        $this->render('index.html.twig', $data, $indexPathname);
    }

    /**
     * Render a Twig template to a file.
     *
     * @throws \Exception when template destination cannot be created
     */
    private function render(string $name, array $data, string $pathname): void
    {
        $data = array_merge($this->globals, $data);
        $template = $this->twig->render($name, $data);

        if (! is_dir($dirname = dirname($pathname))) {
            $level = error_reporting(0);

            try {
                if (! mkdir($dirname, 0755, true)) {
                    throw new \Exception('Failed to create template destination: ' . $dirname);
                }
            } finally {
                error_reporting($level);
            }
        }

        file_put_contents($pathname, $template);
    }
}
