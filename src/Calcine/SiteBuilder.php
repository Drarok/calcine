<?php

namespace Calcine;

use Calcine\Post\Tag;

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
     * Twig template renderer.
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Markdown parser instance.
     *
     * @var ParsedownExtra
     */
    protected $parsedown;

    /**
     * Array of posts, keyed on tag then date.
     *
     * @var array
     */
    protected $tags = array();

    /**
     * Array of posts, keyed on slug.
     *
     * @var array
     */
    protected $posts = array();

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

        if (! is_dir($webPath) && ! mkdir($webPath)) {
            throw new \Exception('Invalid web path: \'' . $webPath . '\'');
        }

        $this->user = $user;
        $this->postsPath = $postsPath;
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;
    }

    /**
     * Build the site, returning how many pages of what type were built.
     *
     * @return array
     */
    public function build()
    {
        $this->posts = array();
        $this->tags = array();

        $loader = new Twig_Loader_Filesystem($this->templatesPath);
        $this->twig = new Twig_Environment($loader);

        $this->parsedown = new ParsedownExtra();

        $this->buildPosts();
        $this->buildIndexes();

        // Count the posts, and tags plus two: one for the site root index, one for the tags index.
        return array(
            'posts'   => count($this->posts),
            'indexes' => count($this->tags) + 2,
        );
    }

    /**
     * Build the site, returning how many pages were built.
     *
     * @return int
     */
    protected function buildPosts()
    {
        $dir = new \DirectoryIterator($this->postsPath);

        foreach ($dir as $fileinfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileinfo->getExtension() != 'markdown') {
                continue;
            }

            echo $fileinfo->getFilename(), PHP_EOL;

            // Parse the post file.
            $post = new Post($fileinfo->getPathname());

            // Write the post to its own file.
            $template = $this->twig->render('post.html.twig', array(
                'user' => $this->user,
                'post' => $post,
                'body' => $this->parsedown->text($post->getBody()),
            ));
            file_put_contents(Path::join($this->webPath, $post->getSlug() . '.html'), $template);

            // Store the post in the posts and tags arrays for buildIndexes.
            $this->posts[$post->getSlug()] = $post;

            foreach ($post->getTags() as $tag) {
                if (! array_key_exists($tag->getName(), $this->tags)) {
                    $this->tags[$tag->getName()] = array();
                }
                $this->tags[$tag->getName()][$post->getSlug()] = $post;
            }
        }

        var_dump(array(
            'posts' => count($this->posts),
            'tags' => count($this->tags),
        ));
    }

    /**
     * Build the index pages (/index.html, /tags/index.html, /tags/<tag>.html, etc).
     *
     * @return void
     */
    protected function buildIndexes()
    {
        $tagsRoot = Path::join($this->webPath, 'tags');

        if (! is_dir($tagsRoot) && ! mkdir($tagsRoot)) {
            throw new \Exception('Cannot create \'' . $tagsRoot . '\'.');
        }

        // Sort by name.
        ksort($this->tags, SORT_NATURAL);

        // Convert the array of name => posts to real Tag objects.
        $tags = array();
        foreach ($this->tags as $name => $posts) {
            // Sort by date, descending.
            krsort($posts);
            $tag = new Tag($name, $posts);
            $tags[$name] = $tag;
        }

        // Build the tags index.
        $template = $this->twig->render('tags.html.twig', array(
            'user' => $this->user,
            'tags' => $tags,
        ));
        file_put_contents(Path::join($tagsRoot, 'index.html'), $template);

        // Now build each individual tag page.
        foreach ($tags as $tag) {
            $template = $this->twig->render('tag.html.twig', array(
                'user'  => $this->user,
                'tag'   => $tag,
            ));

            file_put_contents(Path::join($tagsRoot, $tag->getSlug() . '.html'), $template);
        }

        // Build the site index!
        krsort($this->posts);
        $template = $this->twig->render('index.html.twig', array(
            'user'  => $this->user,
            'posts' => $this->posts,
        ));
        file_put_contents(Path::join($this->webPath, 'index.html'), $template);
    }
}
