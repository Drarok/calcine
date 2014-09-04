<?php

namespace Calcine\Template;

use Calcine\User;
use Calcine\Path;

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
     * Theme name.
     *
     * @var string
     */
    protected $theme = 'default';

    public function __construct(User $user, $templatesPath, $webPath)
    {
        $this->user = $user;
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;
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
     * Render a post to the web directory.
     *
     * @param Post $post Post object.
     *
     * @return void
     */
    public function renderPost(Post $post)
    {
        // Write the post to its own file.

        $postPathname = Path::join(
            $this->webPath,
            $post->getDate()->format('Y/m/d'),
            $post->getSlug() . '.html'
        );

        $template = $this->twig->render('post.html.twig', array(
            'user' => $this->user,
            'post' => $post,
            'body' => $this->parsedown->text($post->getBody()),
        ));

        file_put_contents($postPathname, $template);
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
        $hierarchy = array(
            Path::join($this->templatesPath, $this->theme),
        );

        // If the theme isn't default, look there next.
        if ($this->theme !== 'default') {
            $hierarchy[] = Path::join($this->templatesPath, 'default');
        }
    }
}
