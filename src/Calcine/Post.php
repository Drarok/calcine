<?php

namespace Calcine;

class Post
{
    /**
     * Post title.
     *
     * @var string
     */
    protected $title;

    /**
     * Array of tag names.
     *
     * @var array
     */
    protected $tags;

    /**
     * URL slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * Date string (yyyy-mm-dd hh:mm:ss).
     *
     * @var string
     */
    protected $date;

    /**
     * Plain-text post body.
     *
     * @var string
     */
    protected $body;

    /**
     * Constructor.
     *
     * @param string $pathname Pathname to the post file.
     */
    public function __construct($pathname)
    {
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
     * @return array
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
     * Get date string (yyyy-mm-dd hh:mm:ss).
     *
     * @return string
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
     * Parse the given pathname into the current object.
     *
     * @param string $pathname Path to the post file.
     *
     * @return void
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
        foreach ($data as $name => $value) {
            $this->processHeader($name, $value);
        }

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $this->body = trim($body);

        if (! $this->body) {
            throw new Post\ParseException('Body is empty or missing.');
        }
    }

    /**
     * Validate and store a header.
     *
     * @param string $name  Name of the header.
     * @param string $value Value of the header.
     *
     * @return void
     */
    protected function processHeader($name, $value)
    {
        $name = strtolower($name);

        if ($name != 'body' && ! $value) {
            throw new Post\ParseException(sprintf(
                '%s header must have a value.',
                ucfirst($name)
            ));
        } elseif ($name == 'body' && $value) {
            throw new Post\ParseException('Body header must not have a value.');
        }

        switch ($name) {
            case 'title':
                $this->title = $value;
                break;

            case 'tags':
                $this->tags = array_map(
                    function ($tag) {
                        return trim($tag);
                    },
                    explode(',', $value)
                );

                if (! count($this->tags)) {
                    throw new Post\ParseException('Tags header is invalid.');
                }
                break;

            case 'slug':
                $this->slug = $value;
                break;

            case 'date':
                if (\DateTime::createFromFormat('Y-m-d H:i:s', $value) === false) {
                    throw new Post\ParseException(sprintf(
                        'Date header is invalid: \'%s\'',
                        $value
                    ));
                }
                $this->date = $value;
                break;

            case 'body':
                // Nothing to do for this tag, it's just a marker.
                break;

            default:
                throw new Post\ParseException(sprintf(
                    'Unknown tag \'%s\'',
                    $name
                ));
                break;
        }
    }
}
