<?php

namespace Calcine;

use Calcine\Post\Tag;
use Calcine\Template\Engine\EngineInterface;

class Post
{
    /**
     * Rendering engine.
     *
     * @var EngineInterface
     */
    protected $engine;

    /**
     * Post title.
     *
     * @var string
     */
    protected $title;

    /**
     * Array of Tag objects.
     *
     * @var Tag[]
     */
    protected $tags;

    /**
     * URL slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * Date of the post.
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * Plain-text post body.
     *
     * @var string
     */
    protected $body;

    /**
     * Cached version of the rendered body.
     *
     * @var string
     */
    protected $renderedBody;

    /**
     * Constructor.
     *
     * @param EngineInterface $engine   Rendering engine.
     * @param string          $pathname Pathname to the post file.
     */
    public function __construct(EngineInterface $engine, $pathname)
    {
        $this->engine = $engine;
        $this->parse($pathname);
    }

    /**
     * Get the post title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the tags as an array of tag name strings.
     *
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get URL slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get the post date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get plain text body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get rendered body.
     *
     * @return string
     */
    public function getRenderedBody()
    {
        if ($this->renderedBody === null) {
            $this->renderedBody = $this->engine->render($this->body);
        }

        return $this->renderedBody;
    }

    /**
     * Parse the given pathname into the current object.
     *
     * @param string $pathname Path to the post file.
     *
     * @return void
     *
     * @throws \Exception when file cannot be opened
     * @throws Post\ParseException when file is invalid
     */
    protected function parse($pathname)
    {
        if (! ($file = fopen($pathname, 'r'))) {
            throw new \Exception('Cannot open ' . $pathname);
        }

        $data = array(
            'title' => false,
            'tags'  => false,
            'slug'  => false,
            'date'  => false,
            'body'  => false,
        );

        while (! feof($file)) {
            $line = trim(fgets($file));

            // Skip blank or comment lines.
            if (! $line || $line[0] == ';') {
                continue;
            }

            if (! preg_match('/^([a-zA-Z]+): *(.*)$/', $line, $matches)) {
                throw new Post\ParseException(sprintf(
                    'Invalid header line in %s: \'%s\'.',
                    basename($pathname),
                    $line
                ));
            }

            $name = strtolower($matches[1]);
            $value = trim($matches[2]);

            if (! array_key_exists($name, $data)) {
                throw new Post\ParseException(sprintf(
                    'Unknown header in %s: \'%s\'.',
                    basename($pathname),
                    $name
                ));
            }

            $data[$name] = $value;

            if ($name == 'body') {
                break;
            }
        }

        // Validate and store the headers.
        $errors = [];
        foreach ($data as $name => $value) {
            try {
                $this->processHeader($name, $value);
            } catch (Post\ParseException $e) {
                $errors[] = $e->getMessage();
            }
        }

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $this->body = trim($body);

        if (! $this->body) {
            $errors[] = 'Body is empty or missing.';
        }

        if ($errors) {
            throw new Post\ParseException(sprintf(
                'Failed to parse %s: %s',
                $pathname,
                implode(', ', $errors)
            ));
        }
    }

    /**
     * Validate and store a header.
     *
     * @param string $name  Name of the header.
     * @param string $value Value of the header.
     *
     * @return void
     *
     * @throws Post\ParseException when header is invalid
     */
    protected function processHeader($name, $value)
    {
        $name = strtolower($name);

        if ($name != 'body' && ! $value) {
            throw new Post\ParseException(sprintf(
                '%s header must have a value',
                ucfirst($name)
            ));
        } elseif ($name == 'body' && $value) {
            throw new Post\ParseException('Body header must not have a value');
        }

        switch ($name) {
            case 'title':
                $this->title = $value;
                break;

            case 'tags':
                $this->tags = array_map(
                    function ($tag) {
                        return new Post\Tag(trim($tag));
                    },
                    explode(',', $value)
                );
                break;

            case 'slug':
                if (! preg_match('/^[a-z0-9-]+$/', $value)) {
                    throw new Post\ParseException(sprintf(
                        'Slug header is invalid: \'%s\'',
                        $value
                    ));
                }
                $this->slug = $value;
                break;

            case 'date':
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                if ($date === false) {
                    throw new Post\ParseException(sprintf(
                        'Date header is invalid: \'%s\'',
                        $value
                    ));
                }
                $this->date = $date;
                break;

            case 'body':
                // Nothing to do for this tag, it's just a marker.
                break;
        }
    }
}
