<?php

namespace Calcine\Path;

use Calcine\Path;
use Calcine\Post;

/**
 * Generic path generation service.
 */
class PathService
{
    /**
     * Posts path.
     *
     * @var string
     */
    protected $postsPath;

    /**
     * Templates path.
     *
     * @var string
     */
    protected $templatesPath;

    /**
     * Web path.
     *
     * @var string
     */
    protected $webPath;

    /**
     * Constructor.
     *
     * @param string $postsPath     Posts path.
     * @param string $templatesPath Templates path.
     * @param string $webPath       Web path.
     */
    public function __construct($postsPath, $templatesPath, $webPath)
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

        $this->postsPath = $postsPath;
        $this->templatesPath = $templatesPath;
        $this->webPath = $webPath;
    }

    /**
     * Get posts path.
     *
     * @return string
     */
    public function getPostsPath()
    {
        return $this->postsPath;
    }

    /**
     * Get templates path.
     *
     * @return string
     */
    public function getTemplatesPath()
    {
        return $this->templatesPath;
    }

    /**
     * Get web path.
     *
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * Get the path for a given (supported) object type.
     *
     * @param mixed $object Supported object.
     *
     * @return string
     */
    public function getPath($object)
    {
        if ($object instanceof Post) {
            return $this->getPathForPost($object);
        } else {
            throw new \InvalidArgumentException('Invalid class: ' . get_class($object));
        }
    }

    /**
     * Get the file path for a given Post.
     *
     * @param Post $post The post.
     *
     * @return string
     */
    protected function getPathForPost(Post $post)
    {
        return Path::join(
            $this->webPath,
            $post->getDate()->format('Y/m/d'),
            $post->getSlug() . '.html'
        );
    }
}
