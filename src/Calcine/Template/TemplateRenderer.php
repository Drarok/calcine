<?php

namespace Calcine\Template;

use Calcine\User;
use Calcine\Path;
use Calcine\Post;
use Calcine\Post\Tag;

use ParsedownExtra;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TemplateRenderer
{
    /**
     * User object.
     *
     * @var User
     */
    protected $user;

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
     * ParsedownExtra instance.
     *
     * @var ParsedownExtra
     */
    protected $parsedown;

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
     * Constructor.
     *
     * @param User   $user          User object.
     * @param string $templatesPath Templates path.
     * @param string $webPath       Web path.
     */
    public function __construct(User $user, $templatesPath, $webPath)
    {
        $this->user = $user;
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;

        $this->parsedown = new ParsedownExtra();

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

                if (! fnmatch('*.' . $type, $fileInfo->getFilename())) {
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
                    echo $source, ' => ', $destination, PHP_EOL;
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
     * @return string
     */
    public function renderPost(Post $post)
    {
        $data = array(
            'user' => $this->user,
            'post' => $post,
            'body' => $this->parsedown->text($post->getBody()),
        );

        $postPathname = Path::join(
            $this->webPath,
            $post->getDate()->format('Y/m/d'),
            $post->getSlug() . '.html'
        );

        $this->render('post.html.twig', $data, $postPathname);

        return $postPathname;
    }

    public function renderTag(Tag $tag)
    {
        $data = array(
            'user'  => $this->user,
            'tag'   => $tag,
        );

        $tagPathname = Path::join($this->webPath, 'tags', $tag->getSlug() . '.html');

        $this->render('tag.html.twig', $data, $tagPathname);
    }

    public function renderTagsIndex(array $tags)
    {
        $data = array(
            'user' => $this->user,
            'tags' => $tags,
        );

        $tagsPathname = Path::join(
            $this->webPath,
            'tags',
            'index.html'
        );

        $this->render('tags.html.twig', $data, $tagsPathname);
    }

    public function renderSiteIndex(array $posts)
    {
        $data = array(
            'user'  => $this->user,
            'posts' => $posts,
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
        $template = $this->twig->render($name, $data);

        if (! is_dir($dirname = dirname($pathname))) {
            if (! mkdir($dirname, 0755, true)) {
                throw new \Exception('Failed to create template destination: ' . $dirname);
            }
        }

        file_put_contents($pathname, $template);
    }

    /**
     * Get the path to a template file, with caching.
     *
     * @param string $name Name of the file.
     *
     * @return string|false
     */
    protected function getTemplateFile($name)
    {
        static $pathCache = array();

        if (! array_key_exists($name, $pathCache)) {
            $pathCache[$name] = $this->findTemplateFile($name);
        }

        return $pathCache[$name];
    }

    /**
     * Find the template file on disk.
     *
     * @param string $name Name of the file.
     *
     * @return string|false
     */
    protected function findTemplateFile($name)
    {
        // Look at the theme first.
        $paths = array(
            $this->theme => Path::join($this->templatesPath, $this->theme),
        );

        // If the theme isn't default, look there next.
        if ($this->theme !== 'default') {
            $paths['default'] = Path::join($this->templatesPath, 'default');
        }

        foreach ($paths as $path => $pathname) {
            if (file_exists(Path::join($pathname, $name))) {
                return Path::join($path, $name);
            }
        }

        return false;
    }
}
