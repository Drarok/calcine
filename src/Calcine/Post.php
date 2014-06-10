<?php

namespace Calcine;

class Post
{
    protected $state = 'header';

    protected $title;

    protected $tags;

    protected $slug;

    protected $date;

    protected $body;

    public function __construct($pathname)
    {
        $this->parse($pathname);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getBody()
    {
        return $this->body;
    }

    protected function parse($pathname)
    {
        if (! ($file = fopen($pathname, 'r'))) {
            throw new \Exception('Cannot open ' . $pathname);
        }

        $lineNumber = 0;
        do {
            ++$lineNumber;
            $line = trim(fgets($file));

            // Skip blank or comment lines.
            if (! $line || $line[0] == '#') {
                continue;
            }

            if (! preg_match('/^([a-zA-Z]+): *(.*)$/', $line, $matches)) {
                throw new Post\ParseException(sprintf(
                    'Invalid header \'%s\' in %s:%d',
                    $line,
                    basename($pathname),
                    $lineNumber
                ));
            }

            try {
                $this->processTag($matches[1], $matches[2]);
            } catch (Post\ParseException $e) {
                throw new Post\ParseException(sprintf(
                    '%s in %s:%d',
                    $e->getMessage(),
                    basename($pathname),
                    $lineNumber
                ));
            }
        } while ($this->state == 'header');

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $this->body = trim($body);
    }

    protected function processTag($name, $value)
    {
        switch (strtolower($name)) {
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
                break;

            case 'slug':
                $this->slug = $value;
                break;

            case 'date':
                if (\DateTime::createFromFormat('Y-m-d', $value) === false) {
                    throw new Post\ParseException(sprintf(
                        'Invalid date: \'%s\'',
                        $value
                    ));
                }
                $this->date = $value;
                break;

            case 'body':
                $this->state = 'body';
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
