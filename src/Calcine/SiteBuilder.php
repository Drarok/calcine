<?php

namespace Calcine;

use Calcine\Path\PathService;
use Calcine\Post\Tag;
use Calcine\Template\TemplateRenderer;

class SiteBuilder
{
    /**
     * User object.
     *
     * @var User
     */
    protected $user;

    /**
     * Template renderer service.
     *
     * @var TemplateRenderer
     */
    protected $templateRenderer;

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
     * @param User             $user             The user object.
     * @param TemplateRenderer $templateRenderer Template renderer.
     * @param string           $postsPath        Path to the posts.
     */
    public function __construct(User $user, TemplateRenderer $templateRenderer, $postsPath)
    {
        $this->user = $user;
        $this->templateRenderer = $templateRenderer;
        $this->postsPath = $postsPath;
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
            $postPathname = $this->templateRenderer->renderPost($post);

            // Store the post in the posts and tags arrays for buildIndexes.
            $this->posts[$postPathname] = $post;

            foreach ($post->getTags() as $tag) {
                if (! array_key_exists($tag->getName(), $this->tags)) {
                    $this->tags[$tag->getName()] = array();
                }
                $this->tags[$tag->getName()][$postPathname] = $post;
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
        // Custom sort function for reverse date ordering.
        $postsReverseDateSort = function (Post $a, Post $b) {
            $dateA = $a->getDate();
            $dateB = $b->getDate();
            if ($dateA == $dateB) {
                return 0;
            }
            return $dateA < $dateB ? 1 : -1;
        };

        // Sort tags by name.
        ksort($this->tags, SORT_NATURAL);

        // Convert the array of name => posts to real Tag objects.
        $tags = array();
        foreach ($this->tags as $name => $posts) {
            // Sort by date, descending.
            uasort($posts, $postsReverseDateSort);

            $tag = new Tag($name, $posts);
            $tags[$name] = $tag;
        }

        // Build the tags index.
        $this->templateRenderer->renderTagsIndex($tags);

        // Now build each individual tag page.
        foreach ($tags as $tag) {
            $this->templateRenderer->renderTag($tag);
        }

        // Reverse sort so newest posts are at the top.
        uasort($this->posts, $postsReverseDateSort);

        $this->templateRenderer->renderSiteIndex($this->posts);
    }
}
