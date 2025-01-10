<?php

namespace Calcine;

use Calcine\Post\Tag;
use Calcine\PostsProvider\PostsProviderInterface;
use Calcine\Template\TemplateRenderer;

class SiteBuilder
{
    public function __construct(
        protected TemplateRenderer $templateRenderer,
        protected PostsProviderInterface $postsProvider
    ) {
    }

    /**
     * Build the site, returning how many pages of what type were built.
     *
     * @return array
     */
    public function build(): array
    {
        $posts = $this->postsProvider->getPosts();
        $tags = $this->postsProvider->getTags();
        $archives = $this->postsProvider->getArchives();

        $this->templateRenderer->setGlobal('tags', $tags);
        $this->templateRenderer->setGlobal('archives', $archives);

        $this->templateRenderer->renderTags();
        $this->templateRenderer->renderArchives();

        foreach ($posts as $post) {
            $this->templateRenderer->renderPost($post);
        }

        $this->templateRenderer->renderSiteIndex($posts);

        $this->templateRenderer->copyAssets();

        // Count the posts, and tags plus two: one for the site root index, one for the tags index.
        return [
            'posts'   => count($posts),
            'indexes' => count($tags) + 2,
        ];
    }
}
