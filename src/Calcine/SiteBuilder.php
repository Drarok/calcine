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

        foreach ($dir as $fileInfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileInfo->getExtension() != 'markdown') {
                continue;
            }

            echo $fileInfo->getFilename(), PHP_EOL;

            // Parse the post file and store in the posts array.
            $post = new Post($fileInfo->getPathname());

            foreach ($post->getTags() as $tag) {
                if (! array_key_exists($tag->getName(), $this->tags)) {
                    $this->tags[$tag->getName()] = array();
                }
                $this->tags[$tag->getName()][] = $post;
            }

            $this->posts[] = $post;
        }

        var_dump(array(
            'posts' => count($this->posts),
            'tags' => count($this->tags),
        ));
    }

    /**
     * Build all post pages and the index pages (/index.html, /tags/index.html, /tags/<tag>.html, etc).
     *
     * @return void
     */
    protected function buildSite()
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

        // Ensure posts are in order.
        usort($this->posts, $postsReverseDateSort);

        // Sort tags by name.
        ksort($this->tags, SORT_NATURAL);

        // Convert the array of name => posts to real Tag objects.
        $tags = array();
        foreach ($this->tags as $name => $posts) {
            // Sort by date, descending.
            usort($posts, $postsReverseDateSort);

            $tag = new Tag($name, $posts);
            $tags[$name] = $tag;
        }

        // Pass the tags to the template renderer.
        $this->templateRenderer->setGlobal('tags', $tags);

        // Build the archives.
        $archives = array();
        $previousYear = $previousMonth = false;
        foreach ($this->posts as $post) {
            $key = $post->getDate()->format('Y/m');

            if (! array_key_exists($key, $archives)) {
                $archives[$key] = array(
                    'name'  => $post->getDate()->format('F Y'),
                    'posts' => array(),
                );
            }

            $archives[$key]['posts'] = $post;
        }

        // Pass the archives to the template renderer.
        $this->templateRenderer->setGlobal('archives', $archives);

        // Build each post page.
        foreach ($this->posts as $post) {
            $this->templateRenderer->renderPost($post);
        }

        // Build the tags index.
        $this->templateRenderer->renderTagsIndex($tags);

        // Now build each individual tag page.
        foreach ($tags as $tag) {
            $this->templateRenderer->renderTag($tag);
        }

        $this->templateRenderer->renderSiteIndex($this->posts);
    }
}
