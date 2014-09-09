<?php

namespace Calcine\Template;

use Calcine\User;
use Calcine\Path;
use Calcine\Post;
use Calcine\Post\Tag;
use Calcine\Template\Engine\EngineInterface;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TemplateRenderer
{
    /**
     * Path to the templates.
     *
     * @var string
     */
    protected $templatesPath;

    /**
     * Path to the web directory.
     *
     * @var string
     */
    protected $webPath;

    /**
     * Template rendering engine.
     *
     * @var EngineInterface
     */
    protected $engine;

    /**
     * Twig environment.
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Theme name.
     *
     * @var string
     */
    protected $theme = 'default';

    /**
     * Global data passed to all templates.
     *
     * @var array
     */
    protected $globalData = array(
        'user'        => null,
        'title'       => '',
        'description' => '',
        'tags'        => array(),
        'archives'    => array(),
    );

    /**
     * Constructor.
     *
     * @param User   $user          User object.
     * @param string $templatesPath Templates path.
     * @param string $webPath       Web path.
     */
    public function __construct(User $user, $templatesPath, $webPath)
    {
        $this->setGlobal('user', $user);
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;

        $loader = new Twig_Loader_Filesystem();
        $this->twig = new Twig_Environment($loader);

        $this->setTheme('default');
    }

    /**
     * Setter the theme.
     *
     * @param string $theme Theme name.
     *
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        $paths = array(
            Path::join($this->templatesPath, $theme),
        );

        if ($theme != 'default') {
            $paths[] = Path::join($this->templatesPath, 'default');
        }

        $this->twig->getLoader()->setPaths($paths);

        return $this;
    }

    /**
     * Getter for theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Sets a global.
     *
     * @param string $key   Global name.
     * @param mixed  $value Global value.
     *
     * @return $this
     */
    public function setGlobal($key, $value)
    {
        $this->globalData[$key] = $value;
        return $this;
    }

    /**
     * Gets a global.
     *
     * @return mixed
     */
    public function getGlobal($key)
    {
        return array_key_exists($key, $this->globalData) ? $this->globalData[$key] : null;
    }

    /**
     * Copy template assets to the web directory.
     *
     * @return void
     */
    public function copyAssets()
    {
        $assetsRootPaths = array(
            array(Path::join($this->templatesPath, $this->getTheme(), 'css'), 'css'),
            array(Path::join($this->templatesPath, $this->getTheme(), 'js'), 'js'),
        );

        if ($this->getTheme() != 'default') {
            $assetsRootPaths[] = array(Path::join($this->templatesPath, 'default', 'css'), 'css');
            $assetsRootPaths[] = array(Path::join($this->templatesPath, 'default', 'js'), 'js');
        }

        foreach ($assetsRootPaths as $pathInfo) {
            list($path, $type) = $pathInfo;

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if ($fileInfo->getExtension() != $type) {
                    continue;
                }

                $source = $fileInfo->getPathname();
                $destination = Path::join($this->webPath, $type, $fileInfo->getFilename());

                $assetPath = Path::join($this->webPath, $type);
                if (! is_dir($assetPath)) {
                    if (! mkdir($assetPath, 0755, true)) {
                        throw new \Exception('Failed to create asset path: ' . $assetPath);
                    }
                }

                if (! file_exists($destination)) {
                    copy($source, $destination);
                }
            }
        }
    }

    /**
     * Render a post to the web directory, returning its pathname.
     *
     * @param Post $post Post object.
     *
     * @return void
     */
    public function renderPost(Post $post)
    {
        $data = array(
            'post' => $post,
            'body' => $this->engine->render($post->getBody()),
        );

        $postPathname = Path::join(
            $this->webPath,
            $post->getDate()->format('Y/m/d'),
            $post->getSlug() . '.html'
        );

        $this->render('post_page.html.twig', $data, $postPathname);
    }

    /**
     * Render the tags index and tag pages.
     *
     * @return void
     */
    public function renderTags()
    {
        $data = array(
            'route' => 'tags',
        );

        $tagsPathname = Path::join(
            $this->webPath,
            'tags',
            'index.html'
        );

        $this->render('tags.html.twig', $data, $tagsPathname);

        foreach ($this->getGlobal('tags') as $tag) {
            $data = array(
                'route' => 'tag',
                'tag'   => $tag,
            );

            $tagPathname = Path::join($this->webPath, 'tags', $tag->getSlug() . '.html');

            $this->render('tag.html.twig', $data, $tagPathname);
        }
    }

    /**
     * Render the archive pages.
     *
     * @return void
     */
    public function renderArchives()
    {
        foreach ($this->getGlobal('archives') as $path => $archive) {
            $data = array(
                'route'   => 'archive',
                'archive' => $archive,
            );

            $archivePathname = Path::join($this->webPath, $path, 'index.html');

            $this->render('archive.html.twig', $data, $archivePathname);
        }
    }

    /**
     * Render the site index.
     *
     * @param array $posts Array of Post objects.
     *
     * @return void
     */
    public function renderSiteIndex(array $posts)
    {
        $data = array(
            'posts' => $posts,
            'route' => 'index',
        );

        $indexPathname = Path::join($this->webPath, 'index.html');

        $this->render('index.html.twig', $data, $indexPathname);
    }

    /**
     * Render a Twig template to a file.
     *
     * @param string $name     Name of the template.
     * @param array  $data     View data.
     * @param string $pathname Full pathname to write the file to.
     *
     * @return void
     */
    protected function render($name, $data, $pathname)
    {
        $template = $this->twig->render($name, array_merge($this->globalData, $data));

        if (! is_dir($dirname = dirname($pathname))) {
            if (! mkdir($dirname, 0755, true)) {
                throw new \Exception('Failed to create template destination: ' . $dirname);
            }
        }

        file_put_contents($pathname, $template);
    }
}
