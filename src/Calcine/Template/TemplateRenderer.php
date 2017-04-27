<?php

namespace Calcine\Template;

use Calcine\User;
use Calcine\Path;
use Calcine\Post;
use Calcine\Post\Tag;

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
     * @param string $key Global name.
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
     *
     * @throws \Exception when asset path cannot be created
     */
    public function copyAssets()
    {
        $theme = $this->getTheme();

        $assetTypes = array('css', 'fonts', 'img', 'js');

        $assetsRootPaths = array();
        foreach ($assetTypes as $type) {
            $assetsRootPaths[] = array(
                Path::join(realpath($this->templatesPath), $theme, $type),
                $type,
            );

            if ($theme !== 'default') {
                $assetsRootPaths[] = array(
                    Path::join(realpath($this->templatesPath), 'default', $type),
                    $type
                );
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

        // TODO: Using a global 'tags' is pretty hacky, this should be formalised.
        /** @var Tag $tag */
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
        // TODO: Using the global for this feels hacky.
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
     * This will display the 1st 30 posts for now.
     *
     * @param array $posts Array of Post objects.
     *
     * @return void
     */
    public function renderSiteIndex(array $posts)
    {
        $data = array(
            'posts' => array_slice($posts, 0, 30),
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
     *
     * @throws \Exception when template destination cannot be created
     */
    protected function render($name, $data, $pathname)
    {
        $template = $this->twig->render($name, array_merge($this->globalData, $data));

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
