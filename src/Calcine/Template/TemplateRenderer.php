<?php

namespace Calcine\Template;

use Calcine\User;
use Calcine\Path;

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

        // $loader = new Twig_Loader_Filesystem($templatesPath);
        $this->twig = new Twig_Environment(/*$loader*/);
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
        $templatePathname = $this->getTemplateFile($name);
        // if ($templatePathname === false) {
        //     throw new \Exception('');
        // }
        $template = $this->twig->render($pathname, $data);
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
            Path::join($this->templatesPath, $this->theme),
        );

        // If the theme isn't default, look there next.
        if ($this->theme !== 'default') {
            $paths[] = Path::join($this->templatesPath, 'default');
        }

        foreach ($paths as $path) {
            if (file_exists($pathname = Path::join($path, $name))) {
                return $pathname;
            }
        }

        return false;
    }
}
