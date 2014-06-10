<?php

namespace Calcine;

use ParsedownExtra;
use Twig_Environment;
use Twig_Loader_Filesystem;

class SiteBuilder
{
    /**
     * User object.
     *
     * @var User
     */
    protected $user;

    /**
     * Path to the posts files.
     *
     * @var string
     */
    protected $postsPath;

    /**
     * Path to the template files.
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
     * Constructor.
     *
     * @param User   $user          The user object.
     * @param string $postsPath     Path to the posts files.
     * @param string $templatesPath Path to the template files.
     * @param string $webPath       Path to the web directory.
     */
    public function __construct(User $user, $postsPath, $templatesPath, $webPath)
    {
        if (! is_dir($postsPath)) {
            throw new \Exception('Invalid posts path: \'' . $postsPath . '\'');
        }

        if (! is_dir($templatesPath)) {
            throw new \Exception('Invalid posts path: \'' . $templatesPath . '\'');
        }

        if (! is_dir($webPath)) {
            throw new \Exception('Invalid web path: \'' . $webPath . '\'');
        }

        $this->user = $user;
        $this->postsPath = $postsPath;
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;
    }

    /**
     * Build the site!
     *
     * @return void
     */
    public function build()
    {
        $loader = new Twig_Loader_Filesystem($this->templatesPath);
        $twig = new Twig_Environment($loader);

        $dir = new \DirectoryIterator($this->postsPath);

        $parsedown = new ParsedownExtra();

        foreach ($dir as $fileinfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileinfo->getExtension() != 'markdown') {
                continue;
            }

            $post = new Post($fileinfo->getPathname());

            $parsedBody =

            $template = $twig->render('post.html.twig', array(
                'post' => $post,
                'body' => $parsedown->text($post->getBody()),
            ));
            file_put_contents(Path::join($this->webPath, $post->getSlug() . '.html'), $template);
        }
    }
}
