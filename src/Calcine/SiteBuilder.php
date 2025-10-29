<?php

namespace Calcine;

use Calcine\Post\Tag;
// use Calcine\PagesProvider\PagesProviderInterface;
// use Calcine\PostsProvider\PostsProviderInterface;
use Calcine\Services\ContentProviderInterface;
use Calcine\Template\TemplateRenderer;

class SiteBuilder
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ContentProviderInterface $content,
    ) {
    }

    /**
     * Build the site, returning how many pages of what type were built.
     */
    public function build(): array
    {
        $pages = $this->content->getPages();
        $posts = $this->content->getPosts();

        $tags = $this->collectTags($posts);
        $archives = $this->collectArchives($posts);

        $this->templateRenderer->setGlobal('tags', $tags);
        $this->templateRenderer->setGlobal('archives', $archives);

        $this->templateRenderer->renderTags();
        $this->templateRenderer->renderArchives();

        foreach ($pages as $page) {
            $this->templateRenderer->renderPage($page);
        }

        foreach ($posts as $post) {
            $this->templateRenderer->renderPost($post);
        }

        $this->templateRenderer->renderSiteIndex($posts);

        $this->templateRenderer->copyAssets();

        // Plus two on tabs; one for the site root index, one for the tags index.
        return [
            'pages'   => count($pages),
            'posts'   => count($posts),
            'indexes' => count($tags) + 2,
        ];
    }

    private function collectTags(array $posts): array
    {
        $tags = [];
        $postsByTag = [];

        foreach ($posts as $post) {
            foreach ($post->tags as $tag) {
                $name = $tag->name;

                if (!array_key_exists($name, $tags)) {
                    $tags[$name] = $tag;
                }

                if (!array_key_exists($name, $postsByTag)) {
                    $postsByTag[$name] = [];
                }

                $postsByTag[$name][] = $post;
            }
        }

        $result = [];
        foreach ($postsByTag as $name => $posts) {
            $result[] = new Tag($name, $posts);
        }
        return $result;
    }

    private function collectArchives(array $posts): array
    {
        $archives = [];
        foreach ($posts as $post) {
            $key = $post->date->format('Y/m');

            if (! array_key_exists($key, $archives)) {
                $archives[$key] = [
                    'name'  => $post->date->format('F Y'),
                    'posts' => [],
                ];
            }

            $archives[$key]['posts'][] = $post;
        }

        return $archives;
    }
}
