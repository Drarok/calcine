<?php

namespace Calcine\Post;

class Tag
{
    /**
     * Tag name.
     *
     * @var string
     */
    protected $name;

    /**
     * Posts.
     *
     * @var array
     */
    protected $posts;

    /**
     * Constructor.
     *
     * @param string $name Tag name.
     */
    public function __construct($name, array $posts = array())
    {
        $this->name = $name;
        $this->posts = $posts;
    }

    /**
     * Gets the tag name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the posts.
     *
     * @return array
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Gets the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($this->getName())), '-');
    }
}
