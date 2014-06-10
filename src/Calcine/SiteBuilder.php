<?php

namespace Calcine;

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
     * Path to the web directory.
     *
     * @var string
     */
    protected $webPath;

    /**
     * Constructor.
     *
     * @param User   $user      The user object.
     * @param string $postsPath Path to the posts files.
     * @param string $webPath   Path to the web directory.
     */
    public function __construct(User $user, $postsPath, $webPath)
    {
        if (! is_dir($postsPath)) {
            throw new \Exception('Invalid posts path: \'' . $postsPath . '\'');
        }

        if (! is_dir($webPath)) {
            throw new \Exception('Invalid web path: \'' . $webPath . '\'');
        }

        $this->user = $user;
        $this->postsPath = $postsPath;
        $this->webPath = $webPath;
    }

    public function build()
    {
        $dir = new \DirectoryIterator($this->postsPath);

        foreach ($dir as $fileinfo) {
            if ($dir->isDot()) {
                continue;
            }

            if ($fileinfo->getExtension() != 'markdown') {
                continue;
            }

            $this->processPost($fileinfo);
        }
    }

    protected function processPost(\SplFileInfo $fileinfo)
    {
        $post = new Post($fileinfo->getPathname());
    }
}
