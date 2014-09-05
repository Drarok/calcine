<?php

namespace Calcine;

use Calcine\Path\PathService;
use Calcine\Post\Tag;
use Calcine\Template\TemplateRenderer;

class SiteBuilder
{
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
     * Array of archive information.
     *
     * @var array
     */
    protected $archives = array();

    /**
     * Array of posts.
     *
     * @var array
     */
    protected $posts = array();

    /**
     * Constructor.
     *
     * @param TemplateRenderer $templateRenderer Template renderer.
     * @param string           $postsPath        Path to the posts.
     */
    public function __construct(TemplateRenderer $templateRenderer, $postsPath)
    {
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

        $this->loadPosts();
        $this->buildSite();
        $this->templateRenderer->copyAssets();

        // Count the posts, and tags plus two: one for the site root index, one for the tags index.
        return array(
            'posts'   => count($this->posts),
            'indexes' => count($this->tags) + 2,
        );
    }

    /**
     * Load the post files into objects.
     *
     * @return void
     */
    protected function loadPosts()
    {
        $dir = new \DirectoryIterator($this->postsPath);

        $tags = array();
        foreach ($dir as $fileInfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileInfo->getExtension() != 'markdown') {
                continue;
            }

            // Parse the post file and store in the posts array.
            $post = new Post($fileInfo->getPathname());

            foreach ($post->getTags() as $tag) {
                if (! array_key_exists($tag->getName(), $tags)) {
                    $tags[$tag->getName()] = array();
                }
                $tags[$tag->getName()][] = $post;
            }

            $this->posts[] = $post;
        }

        // Custom sort function for reverse date ordering.
        $postsReverseDateSort = function (Post $a, Post $b) {
            $dateA = $a->getDate();
            $dateB = $b->getDate();
            if ($dateA == $dateB) {
                return 0;
            }
            return $dateA < $dateB ? 1 : -1;
        };

        // Ensure posts are in order.
        usort($this->posts, $postsReverseDateSort);

        // Convert the array of name => posts to real Tag objects.
        foreach ($tags as $name => $posts) {
            // Sort by date, descending.
            usort($posts, $postsReverseDateSort);

            $tag = new Tag($name, $posts);
            $this->tags[$name] = $tag;
        }

        // Sort tags by name.
        ksort($this->tags, SORT_NATURAL);

        // Build the archives.
        $previousYear = $previousMonth = false;
        foreach ($this->posts as $post) {
            $key = $post->getDate()->format('Y/m');

            if (! array_key_exists($key, $this->archives)) {
                $this->archives[$key] = array(
                    'name'  => $post->getDate()->format('F Y'),
                    'posts' => array(),
                );
            }

            $this->archives[$key]['posts'][] = $post;
        }
    }

    /**
     * Build all post pages and the index pages (/index.html, /tags/index.html, /tags/<tag>.html, etc).
     *
     * @return void
     */
    protected function buildSite()
    {
        // Pass the tags and archives to the template renderer.
        $this->templateRenderer->setGlobal('tags', $this->tags);
        $this->templateRenderer->setGlobal('archives', $this->archives);

        // Build the tags index and individual pages.
        $this->templateRenderer->renderTags();

        // Build the archives.
        $this->templateRenderer->renderArchives();

        // Build each post page.
        foreach ($this->posts as $post) {
            $this->templateRenderer->renderPost($post);
        }

        $this->templateRenderer->renderSiteIndex($this->posts);
    }
}
